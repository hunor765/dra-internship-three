<?php
/**
 * Resource centre — every downloadable document in one place.
 *
 * GA4 events:
 *   - view_document_list (on load, queued in <main> via $PAGE_DATALAYER) —
 *     a flat, non-ecommerce event carrying how many documents are on offer.
 *   - file_download (per link, fired from main.js — see wireDownloads()).
 */
require_once __DIR__ . '/includes/functions.php';

$PAGE_TITLE = 'Downloads & resources';

$siteDocs = site_documents();

// Product documents, grouped so each one can be shown against its product.
$productDocs = [];
foreach ($DOCUMENTS as $doc) {
    if ($doc['product'] === null) {
        continue;
    }
    $p = get_product($doc['product']);
    if ($p) {
        $productDocs[] = ['doc' => $doc, 'product' => $p];
    }
}

$PAGE_DATALAYER = [[
    'event'          => 'view_document_list',
    'document_count' => count($siteDocs) + count($productDocs),
    'document_types' => array_values(array_unique(array_map(
        fn($d) => $d['type'],
        $DOCUMENTS
    ))),
]];

require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb"><a href="/index.php">Home</a> / Downloads</nav>

<h1>Downloads &amp; resources</h1>
<p class="muted" style="max-width:62ch;">
  Guides, specifications and reference material. Every file is a PDF, generated
  on request — each download pushes a <code>file_download</code> event to the
  dataLayer carrying the file name, extension and document type.
</p>

<?php if ($siteDocs): ?>
<section class="doc-section" style="margin-top:36px;">
  <div class="section-head"><h2>Store guides</h2></div>
  <?= render_document_links($siteDocs) ?>
</section>
<?php endif; ?>

<?php if ($productDocs): ?>
<section class="doc-section" style="margin-top:44px;">
  <div class="section-head"><h2>Product documents</h2></div>
  <?php foreach ($productDocs as $entry): ?>
    <div class="doc-group">
      <h3 class="doc-group__title">
        <a href="/product.php?id=<?= urlencode($entry['product']['id']) ?>">
          <?= htmlspecialchars($entry['product']['name']) ?>
        </a>
        <span class="muted"><?= htmlspecialchars($entry['product']['brand']) ?></span>
      </h3>
      <?= render_document_links([$entry['doc']], $entry['product']) ?>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
