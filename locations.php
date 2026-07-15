<?php
/**
 * Store locator — index of all physical locations.
 *
 * dataLayer events:
 *   - view_location_list (on load, queued in the <head>)
 *   - select_location    (on store card click, from content.js)
 */
require_once __DIR__ . '/includes/functions.php';

$PAGE_TITLE = 'Store locations';

$stores = [];
$i = 1;
foreach ($LOCATIONS as $loc) {
    $stores[] = [
        'store_id'      => $loc['id'],
        'store_name'    => $loc['name'],
        'store_city'    => $loc['city'],
        'store_country' => $loc['country'],
        'index'         => $i,
    ];
    $i++;
}

$PAGE_DATALAYER = [[
    'event'       => 'view_location_list',
    'list_name'   => 'Store Locator',
    'store_count' => count($stores),
    'stores'      => $stores,
]];

require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb">
  <a href="/index.php">Home</a> / Locations
</nav>

<h1>Find a store</h1>
<p class="muted" style="max-width:62ch;">
  <?= count($LOCATIONS) ?> (entirely fictional) shops where you can see everything
  in person. Every one has free returns and staff who genuinely enjoy talking shop.
</p>

<div class="location-grid">
  <?php foreach ($LOCATIONS as $loc): ?>
    <article class="location-card">
      <div class="location-card__head" style="background:linear-gradient(135deg,<?= $loc['color'] ?>22,<?= $loc['color'] ?>55);">
        <span class="location-card__icon"><?= $loc['icon'] ?></span>
        <?php if (!empty($loc['flagship'])): ?>
          <span class="location-card__badge">Flagship</span>
        <?php endif; ?>
      </div>

      <div class="location-card__body">
        <h3>
          <a href="/location.php?id=<?= urlencode($loc['id']) ?>"
             data-select-location
             data-store-id="<?= htmlspecialchars($loc['id']) ?>"
             data-store-name="<?= htmlspecialchars($loc['name']) ?>"
             data-store-city="<?= htmlspecialchars($loc['city']) ?>"><?= htmlspecialchars($loc['name']) ?></a>
        </h3>
        <p class="muted"><?= htmlspecialchars($loc['address']) ?></p>
        <p class="muted"><?= htmlspecialchars($loc['city']) ?>, <?= htmlspecialchars($loc['country']) ?></p>

        <div class="location-card__meta">
          <span class="location-card__hours"><?= htmlspecialchars($loc['hours_short']) ?></span>
        </div>

        <a class="btn btn--ghost btn--block" style="margin-top:14px;"
           href="/location.php?id=<?= urlencode($loc['id']) ?>"
           data-select-location
           data-store-id="<?= htmlspecialchars($loc['id']) ?>"
           data-store-name="<?= htmlspecialchars($loc['name']) ?>"
           data-store-city="<?= htmlspecialchars($loc['city']) ?>">Store details</a>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
