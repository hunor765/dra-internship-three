<?php
/**
 * Document download endpoint.
 *
 * Renders the requested document to a real PDF on the fly (includes/pdf.php)
 * and sends it as an attachment, so the repo carries no binary files.
 *
 * There is deliberately no tracking here. file_download is fired client-side
 * from main.js on the link click — a server-side push would be invisible to
 * GTM, since the browser never navigates to this response.
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/pdf.php';

$id  = isset($_GET['doc']) ? (string) $_GET['doc'] : '';
$doc = get_document($id);

if (!$doc) {
    http_response_code(404);
    $PAGE_TITLE = 'Document not found';
    require __DIR__ . '/includes/header.php';
    echo '<nav class="breadcrumb"><a href="/index.php">Home</a> / Download</nav>';
    echo '<div class="empty-state"><p>That document does not exist.</p>'
       . '<a class="btn" href="/downloads.php">Browse the resource centre</a></div>';
    require __DIR__ . '/includes/footer.php';
    return;
}

$sections = $doc['sections'];

/* The catalogue is generated from live product data rather than written out
   by hand, so it never drifts from what the shop is actually selling. */
if ($doc['id'] === 'catalogue-current') {
    global $PRODUCTS, $CATEGORIES;
    $sections = [];
    foreach ($CATEGORIES as $cat) {
        $lines = [];
        foreach (products_in_category($cat['id']) as $p) {
            $lines[] = '- ' . $p['name'] . ' (' . $p['brand'] . ') — ' . money($p['price'])
                     . ' — ' . $p['id'];
        }
        if ($lines) {
            $sections[] = ['heading' => $cat['name'], 'body' => $lines];
        }
    }
}

$product  = $doc['product'] ? get_product($doc['product']) : null;
$subtitle = $STORE['name'] . ($product ? ' · ' . $product['name'] : '')
          . ' · Document ' . strtoupper($doc['id']);
$footer   = $STORE['name'] . ' — reference document. All details are fictional training data.';

$pdf = pdf_build($doc['title'], $subtitle, $sections, $footer);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $doc['file'] . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: public, max-age=3600');
header('X-Content-Type-Options: nosniff');

echo $pdf;
