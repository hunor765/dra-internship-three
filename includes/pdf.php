<?php
/**
 * Minimal dependency-free PDF writer.
 *
 * Just enough of the PDF spec to render a multi-page text document with a
 * heading font and a body font. It exists so download.php can hand visitors a
 * genuine .pdf without pulling in a library or committing binaries — the point
 * of the feature is the file_download event, but the file itself should still
 * open properly in a real PDF reader.
 *
 * Everything is laid out in points on A4 (595 x 842).
 */

const PDF_PAGE_W   = 595.0;
const PDF_PAGE_H   = 842.0;
const PDF_MARGIN   = 56.0;
const PDF_LEADING  = 15.0;
const PDF_BODY_PT  = 10.5;
const PDF_H1_PT    = 19.0;
const PDF_H2_PT    = 12.5;

/**
 * Escape a string for use inside a PDF literal string, and drop anything
 * outside printable ASCII — the base-14 fonts are single-byte, so a stray emoji
 * would render as mojibake.
 */
function pdf_text(string $s): string
{
    $s = str_replace(['&amp;', '&nbsp;', '&mdash;', '&hellip;'], ['&', ' ', '-', '...'], $s);
    $s = strtr($s, [
        '—' => '-', '–' => '-', '’' => "'", '‘' => "'",
        '“' => '"', '”' => '"', '…' => '...', '×' => 'x',
        '·' => '-', '½' => '1/2', '°' => ' deg', '€' => 'EUR', '£' => 'GBP',
    ]);
    $s = preg_replace('/[^\x20-\x7E]/', '', $s) ?? '';
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
}

/** Rough width of a Helvetica string at a given size, for wrapping. */
function pdf_width(string $s, float $pt): float
{
    return strlen($s) * $pt * 0.5;   // ~0.5em average for Helvetica
}

/** Greedy word wrap to a column width in points. */
function pdf_wrap(string $s, float $pt, float $maxW): array
{
    $words = preg_split('/\s+/', trim($s)) ?: [];
    $lines = [];
    $line  = '';
    foreach ($words as $w) {
        $try = $line === '' ? $w : $line . ' ' . $w;
        if (pdf_width($try, $pt) > $maxW && $line !== '') {
            $lines[] = $line;
            $line = $w;
        } else {
            $line = $try;
        }
    }
    if ($line !== '') {
        $lines[] = $line;
    }
    return $lines ?: [''];
}

/**
 * Build a PDF.
 *
 * @param string $title    Document title (drawn as the H1 and set in /Info).
 * @param string $subtitle Small line under the title.
 * @param array  $sections [ ['heading' => string, 'body' => string[]], … ]
 *                         Each body entry is a paragraph; a leading "- " marks
 *                         a bullet and is kept as-is.
 * @param string $footer   Small line drawn at the foot of every page.
 */
function pdf_build(string $title, string $subtitle, array $sections, string $footer = ''): string
{
    $colW = PDF_PAGE_W - (2 * PDF_MARGIN);

    /* ---- 1. Flow the content into a list of draw ops per page ---------- */
    $pages   = [];
    $ops     = [];
    $y       = PDF_PAGE_H - PDF_MARGIN;
    $newPage = function () use (&$pages, &$ops, &$y) {
        if ($ops) {
            $pages[] = $ops;
        }
        $ops = [];
        $y   = PDF_PAGE_H - PDF_MARGIN;
    };
    $room = function (float $need) use (&$y, $newPage) {
        if ($y - $need < PDF_MARGIN + 28) {   // 28pt reserved for the footer
            $newPage();
        }
    };

    // Title block.
    foreach (pdf_wrap($title, PDF_H1_PT, $colW) as $line) {
        $ops[] = ['F2', PDF_H1_PT, PDF_MARGIN, $y, $line];
        $y -= PDF_H1_PT + 6;
    }
    if ($subtitle !== '') {
        $y -= 2;
        foreach (pdf_wrap($subtitle, PDF_BODY_PT, $colW) as $line) {
            $ops[] = ['F1', PDF_BODY_PT, PDF_MARGIN, $y, $line];
            $y -= PDF_LEADING;
        }
    }
    $y -= 10;

    foreach ($sections as $section) {
        $heading = (string) ($section['heading'] ?? '');
        if ($heading !== '') {
            $room(PDF_H2_PT + PDF_LEADING);
            $y -= 6;
            $ops[] = ['F2', PDF_H2_PT, PDF_MARGIN, $y, $heading];
            $y -= PDF_H2_PT + 5;
        }
        foreach ((array) ($section['body'] ?? []) as $para) {
            $para   = (string) $para;
            $bullet = str_starts_with($para, '- ');
            $indent = $bullet ? 12.0 : 0.0;
            $lines  = pdf_wrap($para, PDF_BODY_PT, $colW - $indent);
            foreach ($lines as $i => $line) {
                $room(PDF_LEADING);
                $x = PDF_MARGIN + ($i === 0 ? 0.0 : $indent);
                $ops[] = ['F1', PDF_BODY_PT, $x, $y, $line];
                $y -= PDF_LEADING;
            }
            $y -= 4;
        }
    }
    $pages[] = $ops;

    /* ---- 2. Turn each page's ops into a content stream ------------------ */
    $streams = [];
    foreach ($pages as $pi => $pageOps) {
        $buf = "BT\n";
        foreach ($pageOps as [$font, $pt, $x, $yy, $text]) {
            $buf .= sprintf("/%s %.1f Tf\n1 0 0 1 %.1f %.1f Tm\n(%s) Tj\n",
                $font, $pt, $x, $yy, pdf_text($text));
        }
        if ($footer !== '') {
            $buf .= sprintf("/F1 8.0 Tf\n1 0 0 1 %.1f %.1f Tm\n(%s) Tj\n",
                PDF_MARGIN, PDF_MARGIN - 12, pdf_text($footer . '   |   page ' . ($pi + 1) . ' of ' . count($pages)));
        }
        $buf .= "ET";
        $streams[] = $buf;
    }

    /* ---- 3. Assemble objects, tracking byte offsets for the xref ------- */
    // Objects 1-2 are the catalog and page tree; each page then takes two ids
    // (the page and its content stream), so the fonts start right after them.
    $n         = count($streams);
    $fontA     = 3 + (2 * $n);       // /F1 Helvetica
    $fontB     = $fontA + 1;         // /F2 Helvetica-Bold
    $objects   = [];                 // 1-indexed: $objects[$id] = body

    $kids = [];
    for ($i = 0; $i < $n; $i++) {
        $kids[] = (3 + (2 * $i)) . ' 0 R';
    }

    $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count {$n} >>";

    for ($i = 0; $i < $n; $i++) {
        $pageId    = 3 + (2 * $i);
        $contentId = $pageId + 1;
        $objects[$pageId] = "<< /Type /Page /Parent 2 0 R "
            . sprintf("/MediaBox [0 0 %.0f %.0f] ", PDF_PAGE_W, PDF_PAGE_H)
            . "/Resources << /Font << /F1 {$fontA} 0 R /F2 {$fontB} 0 R >> >> "
            . "/Contents {$contentId} 0 R >>";
        $stream = $streams[$i];
        $objects[$contentId] = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
    }

    $objects[$fontA] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
    $objects[$fontB] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

    $infoId = $fontB + 1;
    $objects[$infoId] = "<< /Title (" . pdf_text($title) . ") /Producer (GA4 training store) >>";

    $out     = "%PDF-1.4\n";
    $offsets = [];
    for ($id = 1; $id <= $infoId; $id++) {
        $offsets[$id] = strlen($out);
        $out .= "{$id} 0 obj\n" . $objects[$id] . "\nendobj\n";
    }

    $xrefPos = strlen($out);
    $total   = $infoId + 1;
    $out .= "xref\n0 {$total}\n0000000000 65535 f \n";
    for ($id = 1; $id <= $infoId; $id++) {
        $out .= sprintf("%010d 00000 n \n", $offsets[$id]);
    }
    $out .= "trailer\n<< /Size {$total} /Root 1 0 R /Info {$infoId} 0 R >>\n";
    $out .= "startxref\n{$xrefPos}\n%%EOF\n";

    return $out;
}
