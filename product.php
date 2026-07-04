<?php
/**
 * Product detail page.
 * GA4 events: view_item (on load), add_to_cart / add_to_wishlist (clicks).
 * Variant selection is captured in main.js and sent as item_variant.
 */
require_once __DIR__ . '/includes/functions.php';

$pid     = isset($_GET['id']) ? (string) $_GET['id'] : '';
$product = get_product($pid);

if (!$product) {
    header('Location: /index.php');
    exit;
}

$PAGE_TITLE = $product['name'];
$cat        = get_category($product['category']);

$item = ga_item($product);

// For view_item, include the default (first) variant so item_variant is
// present whenever the product actually has variants. The button payload
// below stays variant-free — main.js fills in the variant the user selects.
$viewItem = $item;
if (!empty($product['variants'])) {
    $viewItem['item_variant'] = $product['variants'][0]['name'];
}

$PAGE_DATALAYER = [[
    'event'     => 'view_item',
    'ecommerce' => [
        'currency' => $STORE['currency'],
        'value'    => round((float) $product['price'], 2),
        'items'    => [$viewItem],
    ],
]];

require __DIR__ . '/includes/header.php';

$itemJson = attr_json($item);

// Related products from the same category (excluding this one).
$related = array_filter(products_in_category($product['category']), fn($p) => $p['id'] !== $product['id']);
$related = array_slice(array_values($related), 0, 4);
?>

<nav class="breadcrumb">
  <a href="/index.php">Home</a> /
  <a href="/category.php?id=<?= urlencode($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></a> /
  <?= htmlspecialchars($product['name']) ?>
</nav>

<div class="product-detail" data-product-scope>
  <div>
    <?= product_image($product, 380) ?>
  </div>

  <div class="product-detail__info">
    <span class="product-card__cat"><?= htmlspecialchars($cat['name']) ?> · <?= htmlspecialchars($product['brand']) ?></span>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="rating">★ <?= number_format($product['rating'], 1) ?> / 5</div>
    <div class="product-detail__price"><?= money($product['price']) ?></div>
    <p><?= htmlspecialchars($product['desc']) ?></p>

    <?php if (!empty($product['variants'])): ?>
      <div class="field">
        <label for="variant">Option</label>
        <select id="variant" data-variant-select>
          <?php foreach ($product['variants'] as $v): ?>
            <option value="<?= htmlspecialchars($v['id']) ?>"><?= htmlspecialchars($v['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <div class="qty-row">
      <div class="field">
        <label for="qty">Quantity</label>
        <input id="qty" type="number" min="1" value="1" data-qty-input>
      </div>
    </div>

    <div class="detail-actions">
      <button class="btn" data-add-to-cart data-item="<?= $itemJson ?>">Add to cart</button>
      <button class="wishlist-btn" data-toggle-wishlist data-item="<?= $itemJson ?>">♥ Save to wishlist</button>
    </div>
  </div>
</div>

<?php if ($related): ?>
<section style="margin-top:56px;">
  <div class="section-head"><h2>You might also like</h2></div>
  <div class="product-grid">
    <?php
    $idx = 1;
    foreach ($related as $p) {
        echo render_product_card($p, $idx, 'related_' . $cat['id'], 'Related: ' . $cat['name']);
        $idx++;
    }
    ?>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
