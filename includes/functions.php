<?php
/**
 * Shared helper functions.
 */

require_once __DIR__ . '/data.php';

/** Fetch a product by SKU, or null. */
function get_product(string $id): ?array
{
    global $PRODUCTS;
    return $PRODUCTS[$id] ?? null;
}

/** Fetch a category by slug, or null. */
function get_category(string $id): ?array
{
    global $CATEGORIES;
    return $CATEGORIES[$id] ?? null;
}

/** All products belonging to a category slug. */
function products_in_category(string $categoryId): array
{
    global $PRODUCTS;
    return array_values(array_filter(
        $PRODUCTS,
        fn($p) => $p['category'] === $categoryId
    ));
}

/** Human readable category name from slug. */
function category_name(string $id): string
{
    $c = get_category($id);
    return $c ? $c['name'] : $id;
}

/** Format a price with the store currency symbol. */
function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

/**
 * Build the canonical GA4 "item" object for a product.
 *
 * This is the exact shape pushed to the dataLayer, so the PHP side and the
 * JS side always agree. $index is the position within a list (1-based).
 */
function ga_item(array $product, int $index = 0, ?string $listId = null, ?string $listName = null): array
{
    $item = [
        'item_id'        => $product['id'],
        'item_name'      => $product['name'],
        'item_brand'     => $product['brand'],
        'item_category'  => category_name($product['category']),
        'price'          => round((float) $product['price'], 2),
        'quantity'       => 1,
    ];
    if ($index > 0) {
        $item['index'] = $index;
    }
    if ($listId !== null) {
        $item['item_list_id'] = $listId;
    }
    if ($listName !== null) {
        $item['item_list_name'] = $listName;
    }
    return $item;
}

/**
 * Render a coloured SVG "photo" placeholder for a product so the site has
 * no binary image dependencies (keeps the Vercel deploy trivial).
 */
function product_image(array $product, int $height = 220): string
{
    $cat   = get_category($product['category']);
    $color = $cat['color'] ?? '#7d5260';
    $icon  = $cat['icon'] ?? '👗';
    $label = htmlspecialchars($product['name'], ENT_QUOTES);

    return '<div class="product-photo" style="height:' . $height . 'px;'
        . 'background:linear-gradient(135deg,' . $color . '22,' . $color . '55);'
        . 'border-color:' . $color . '33;" role="img" aria-label="' . $label . '">'
        . '<span class="product-photo__icon">' . $icon . '</span>'
        . '</div>';
}

/** JSON-encode for safe embedding inside an HTML attribute. */
function attr_json(array $data): string
{
    return htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Render a product card for use inside a list/grid.
 *
 * The card carries the GA4 item payload in data attributes so the JS layer
 * can fire select_item (on the link) and add_to_cart / add_to_wishlist
 * (on the buttons) without another server round-trip.
 */
function render_product_card(array $product, int $index, string $listId, string $listName): string
{
    $item = ga_item($product, $index, $listId, $listName);
    $json = attr_json($item);
    $url  = '/product.php?id=' . urlencode($product['id']);

    ob_start(); ?>
    <article class="product-card">
      <a class="prod-link" href="<?= $url ?>"
         data-select-item
         data-item="<?= $json ?>"
         data-list-id="<?= htmlspecialchars($listId) ?>"
         data-list-name="<?= htmlspecialchars($listName) ?>">
        <?= product_image($product, 180) ?>
      </a>
      <div class="product-card__body">
        <span class="product-card__cat"><?= htmlspecialchars(category_name($product['category'])) ?></span>
        <h3 class="product-card__name">
          <a href="<?= $url ?>"
             data-select-item
             data-item="<?= $json ?>"
             data-list-id="<?= htmlspecialchars($listId) ?>"
             data-list-name="<?= htmlspecialchars($listName) ?>"><?= htmlspecialchars($product['name']) ?></a>
        </h3>
        <div class="product-card__meta">
          <span class="price"><?= money($product['price']) ?></span>
          <span class="rating">★ <?= number_format($product['rating'], 1) ?></span>
        </div>
      </div>
      <div class="product-card__actions" data-product-scope>
        <button class="btn" data-add-to-cart data-item="<?= $json ?>">Add to cart</button>
        <button class="wishlist-btn" data-toggle-wishlist data-item="<?= $json ?>" aria-label="Add to wishlist">♥</button>
      </div>
    </article>
    <?php
    return ob_get_clean();
}
