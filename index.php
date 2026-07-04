<?php
/**
 * Homepage.
 * GA4 events fired here:
 *   - view_promotion  (hero banner, on load)
 *   - view_item_list  (featured products, on load)
 *   - select_promotion / select_item (on click, via main.js)
 */
require_once __DIR__ . '/includes/functions.php';

$PAGE_TITLE = 'Home';

// Featured = first 8 products across all categories.
$featured = array_slice(array_values($PRODUCTS), 0, 8);

$listId   = 'featured';
$listName = 'Featured Products';

// Build the item list payload for view_item_list.
$viewItems = [];
$i = 1;
foreach ($featured as $p) {
    $viewItems[] = ga_item($p, $i, $listId, $listName);
    $i++;
}

$promo = $PROMOTIONS[0];

// Page-load dataLayer events handed to header.php.
$PAGE_DATALAYER = [
    [
        'event'      => 'view_promotion',
        'ecommerce'  => [
            'items' => [[
                'promotion_id'   => $promo['promotion_id'],
                'promotion_name' => $promo['promotion_name'],
                'creative_name'  => $promo['creative_name'],
                'creative_slot'  => $promo['creative_slot'],
            ]],
        ],
    ],
    [
        'event'     => 'view_item_list',
        'ecommerce' => [
            'item_list_id'   => $listId,
            'item_list_name' => $listName,
            'items'          => $viewItems,
        ],
    ],
];

require __DIR__ . '/includes/header.php';

$promoJson = attr_json([
    'promotion_id'   => $promo['promotion_id'],
    'promotion_name' => $promo['promotion_name'],
    'creative_name'  => $promo['creative_name'],
    'creative_slot'  => $promo['creative_slot'],
]);
?>

<section class="hero">
  <h1>Dress the season, effortlessly</h1>
  <p>Linen, leather and everyday staples — considered pieces that work harder than their price tag.</p>
  <a class="btn" href="/category.php?id=womens-apparel"
     data-select-promotion data-promotion="<?= $promoJson ?>">Shop the Summer Edit</a>
  <span class="hero__emoji">👗</span>
</section>

<div class="notice">
  <strong>Training site:</strong> This is a fictional shop for learning GA4 &amp; GTM.
  Open your browser console and inspect <code>window.dataLayer</code> as you click around —
  every action pushes a standard ecommerce event.
</div>

<section>
  <div class="section-head"><h2>Shop by category</h2></div>
  <div class="cat-grid">
    <?php foreach ($CATEGORIES as $cat): ?>
      <a class="cat-tile" href="/category.php?id=<?= urlencode($cat['id']) ?>">
        <div class="cat-tile__icon"><?= $cat['icon'] ?></div>
        <h3><?= htmlspecialchars($cat['name']) ?></h3>
        <p><?= htmlspecialchars($cat['blurb']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section style="margin-top:44px;">
  <div class="section-head">
    <h2>Featured products</h2>
    <a href="/category.php?id=womens-apparel">Browse all →</a>
  </div>
  <div class="product-grid">
    <?php
    $idx = 1;
    foreach ($featured as $p) {
        echo render_product_card($p, $idx, $listId, $listName);
        $idx++;
    }
    ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
