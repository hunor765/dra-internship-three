<?php
/**
 * Single store page.
 *
 * dataLayer events:
 *   - view_location   (on load, queued in the <head>)
 *   - get_directions  (directions button, from content.js)
 *   - click_to_call   (phone link, from content.js)
 */
require_once __DIR__ . '/includes/functions.php';

$sid = isset($_GET['id']) ? (string) $_GET['id'] : '';
$loc = $LOCATIONS[$sid] ?? null;

if (!$loc) {
    header('Location: /locations.php');
    exit;
}

$PAGE_TITLE = $loc['name'];

$PAGE_DATALAYER = [[
    'event'         => 'view_location',
    'store_id'      => $loc['id'],
    'store_name'    => $loc['name'],
    'store_city'    => $loc['city'],
    'store_country' => $loc['country'],
    'is_flagship'   => !empty($loc['flagship']),
]];

require __DIR__ . '/includes/header.php';

$mapsUrl = 'https://www.google.com/maps/search/?api=1&query='
         . urlencode($loc['address'] . ', ' . $loc['city'] . ', ' . $loc['country']);
$telUrl  = 'tel:' . preg_replace('/\s+/', '', $loc['phone']);
?>

<nav class="breadcrumb">
  <a href="/index.php">Home</a> /
  <a href="/locations.php">Locations</a> /
  <?= htmlspecialchars($loc['name']) ?>
</nav>

<div class="location-detail">
  <div>
    <div class="location-detail__hero" style="background:linear-gradient(135deg,<?= $loc['color'] ?>22,<?= $loc['color'] ?>55);">
      <span class="location-detail__icon"><?= $loc['icon'] ?></span>
    </div>

    <h2 style="margin-top:28px;">About this store</h2>
    <p><?= htmlspecialchars($loc['blurb']) ?></p>

    <h2>What you'll find here</h2>
    <ul class="tick-list">
      <?php foreach ($loc['services'] as $svc): ?>
        <li><?= htmlspecialchars($svc) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <aside class="summary">
    <?php if (!empty($loc['flagship'])): ?>
      <span class="location-card__badge" style="position:static;display:inline-block;margin-bottom:10px;">Flagship</span>
    <?php endif; ?>

    <h3 style="margin-top:0;"><?= htmlspecialchars($loc['name']) ?></h3>

    <p class="muted" style="margin:0 0 4px;"><?= htmlspecialchars($loc['address']) ?></p>
    <p class="muted" style="margin:0 0 16px;">
      <?= htmlspecialchars($loc['city']) ?>, <?= htmlspecialchars($loc['country']) ?>
    </p>

    <div class="summary__row"><span>Phone</span>
      <span>
        <a href="<?= htmlspecialchars($telUrl) ?>"
           data-click-to-call
           data-store-id="<?= htmlspecialchars($loc['id']) ?>"
           data-store-name="<?= htmlspecialchars($loc['name']) ?>"><?= htmlspecialchars($loc['phone']) ?></a>
      </span>
    </div>

    <h4 style="margin:18px 0 8px;">Opening hours</h4>
    <?php foreach ($loc['hours'] as $day => $time): ?>
      <div class="summary__row">
        <span><?= htmlspecialchars($day) ?></span>
        <span<?= $time === 'Closed' ? ' class="muted"' : '' ?>><?= htmlspecialchars($time) ?></span>
      </div>
    <?php endforeach; ?>

    <a class="btn btn--block" style="margin-top:18px;"
       href="<?= htmlspecialchars($mapsUrl) ?>"
       target="_blank" rel="noopener"
       data-get-directions
       data-store-id="<?= htmlspecialchars($loc['id']) ?>"
       data-store-name="<?= htmlspecialchars($loc['name']) ?>"
       data-store-city="<?= htmlspecialchars($loc['city']) ?>">Get directions</a>

    <a class="btn btn--ghost btn--block" style="margin-top:10px;" href="/contact.php">Contact this store</a>
  </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
