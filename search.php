<?php
/**
 * Search results page.
 *
 * GA4 events:
 *   - view_search_results (on load, queued in <main> via $PAGE_DATALAYER) —
 *     carries the search_term, a results_count, and the matched products as a
 *     standard ecommerce item list so it can double as a view_item_list.
 *   - select_item / add_to_cart / add_to_wishlist (result cards, via main.js)
 *
 * There is no backend: matching is a simple case-insensitive substring search
 * across product name, brand, category and description.
 */
require_once __DIR__ . '/includes/functions.php';

$q = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$PAGE_TITLE = $q !== '' ? ('Search · ' . $q) : 'Search';

$listId   = 'search_results';
$listName = 'Search Results';

/** Case-insensitive substring match across the searchable fields. */
function product_matches(array $p, string $needle): bool
{
    if ($needle === '') {
        return false;
    }
    $hay = strtolower(implode(' ', [
        $p['name'] ?? '',
        $p['brand'] ?? '',
        category_name($p['category'] ?? ''),
        $p['desc'] ?? '',
    ]));
    foreach (array_filter(explode(' ', strtolower($needle))) as $term) {
        if (strpos($hay, $term) === false) {
            return false;
        }
    }
    return true;
}

$results = [];
if ($q !== '') {
    global $PRODUCTS;
    $results = array_values(array_filter($PRODUCTS, fn($p) => product_matches($p, $q)));
}

$viewItems = [];
$i = 1;
foreach ($results as $p) {
    $viewItems[] = ga_item($p, $i, $listId, $listName);
    $i++;
}

// Only fire view_search_results once the visitor has actually searched.
if ($q !== '') {
    $PAGE_DATALAYER = [[
        'event'         => 'view_search_results',
        'search_term'   => $q,
        'results_count' => count($results),
        'ecommerce'     => [
            'item_list_id'   => $listId,
            'item_list_name' => $listName,
            'items'          => $viewItems,
        ],
    ]];
}

require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb"><a href="/index.php">Home</a> / Search</nav>

<h1>Search</h1>

<form class="search-page-form" role="search" data-search-form action="/search.php" method="get">
  <input type="search" name="q" class="search-page-form__input"
         placeholder="Search products…" aria-label="Search products"
         value="<?= htmlspecialchars($q, ENT_QUOTES) ?>" autofocus>
  <button type="submit" class="btn">Search</button>
</form>

<?php if ($q === ''): ?>
  <div class="empty-state">
    <p>Type a product, brand or category above to search the shop.</p>
  </div>
<?php elseif (!$results): ?>
  <div class="empty-state">
    <p>No results for <strong>&ldquo;<?= htmlspecialchars($q) ?>&rdquo;</strong>.</p>
    <p class="muted">A <code>view_search_results</code> event still fired — with <code>results_count: 0</code>.</p>
    <a class="btn" href="/index.php">Back to shop</a>
  </div>
<?php else: ?>
  <div class="section-head">
    <h2><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for &ldquo;<?= htmlspecialchars($q) ?>&rdquo;</h2>
  </div>
  <div class="product-grid">
    <?php
    $idx = 1;
    foreach ($results as $p) {
        echo render_product_card($p, $idx, $listId, $listName);
        $idx++;
    }
    ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
