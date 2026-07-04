<?php
/**
 * Category listing page.
 * GA4 events: view_item_list (on load), select_item / add_to_cart / add_to_wishlist (clicks).
 */
require_once __DIR__ . '/includes/functions.php';

$catId = isset($_GET['id']) ? (string) $_GET['id'] : '';
$cat   = get_category($catId);

if (!$cat) {
    // Unknown category — send them home.
    header('Location: /index.php');
    exit;
}

$products = products_in_category($catId);
$PAGE_TITLE = $cat['name'];

$listId   = $cat['id'];
$listName = $cat['name'];

$viewItems = [];
$i = 1;
foreach ($products as $p) {
    $viewItems[] = ga_item($p, $i, $listId, $listName);
    $i++;
}

$PAGE_DATALAYER = [[
    'event'     => 'view_item_list',
    'ecommerce' => [
        'item_list_id'   => $listId,
        'item_list_name' => $listName,
        'items'          => $viewItems,
    ],
]];

require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb"><a href="/index.php">Home</a> / <?= htmlspecialchars($cat['name']) ?></nav>

<section class="hero" style="background:linear-gradient(135deg,<?= $cat['color'] ?>,<?= $cat['color'] ?>cc);padding:40px;">
  <h1><?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?></h1>
  <p><?= htmlspecialchars($cat['blurb']) ?></p>
</section>

<div class="section-head"><h2><?= count($products) ?> products</h2></div>

<div class="product-grid">
  <?php
  $idx = 1;
  foreach ($products as $p) {
      echo render_product_card($p, $idx, $listId, $listName);
      $idx++;
  }
  ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
