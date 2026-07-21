<?php
/**
 * Shared page header. Every page includes this, so the GTM snippet and the
 * dataLayer bootstrap live in exactly one place.
 *
 * Pages may set $PAGE_TITLE and $PAGE_DATALAYER (an array of dataLayer events
 * to push on load, e.g. view_item_list / view_item) before including this.
 */

require_once __DIR__ . '/functions.php';

$PAGE_TITLE      = $PAGE_TITLE      ?? 'Thread & Stitch';
$PAGE_DATALAYER  = $PAGE_DATALAYER  ?? [];

// GTM container for the Thread & Stitch training store.
$GTM_ID = 'GTM-KQNM4DRL';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($PAGE_TITLE) ?> · Thread &amp; Stitch</title>

<!-- ============================================================
     dataLayer bootstrap — MUST come before the GTM snippet.
     Page-load ecommerce events are NOT pushed directly here. They
     are queued in SNS_PENDING_EVENTS and flushed by main.js ONLY
     after the GTM container has finished loading, so no ecommerce
     event fires before GTM is ready to receive it.

     NOTE: the per-page events themselves are emitted INSIDE <main>
     (see the bottom of this file), not here in the <head>. The SPA
     router only swaps <main> on navigation, so a <head> script would
     never run again after the first load — emitting the queue inside
     <main> is what lets view_* events re-fire on every route change.
     ============================================================ -->
<script>
  window.dataLayer = window.dataLayer || [];
  window.GTM_CONTAINER_ID = '<?= $GTM_ID ?>';
  window.SNS_PENDING_EVENTS = window.SNS_PENDING_EVENTS || [];

  /* ------------------------------------------------------------------
     SPA page-ready hook.

     The site is now a single-page app: after the first load, navigation
     is handled by router.js, which swaps <main> and re-executes the
     scripts inside it. DOMContentLoaded only ever fires ONCE, so page
     scripts register through SNS_READY instead — it defers on the first
     load and runs immediately on every client-side route change.
     ------------------------------------------------------------------ */
  window.SNS_READY = function (fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  };
</script>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?= $GTM_ID ?>');</script>
<!-- End Google Tag Manager -->

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/cookie-banner-accordion.css">
<script>
  // Expose the store currency + coupon table to the client-side cart logic.
  window.STORE_CURRENCY = <?= json_encode($STORE['currency']) ?>;
  window.STORE_ICON = <?= json_encode($STORE['icon'] ?? '🛍️', JSON_UNESCAPED_UNICODE) ?>;
  window.SNS_COUPONS = <?= json_encode($COUPONS ?? [], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="/assets/js/cookie-banner-accordion.js"></script>
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= $GTM_ID ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<header class="site-header">
  <div class="wrap site-header__inner">
    <a class="brand" href="/index.php">
      <span class="brand__mark">🧵</span>
      <span class="brand__name">Thread&nbsp;&amp;&nbsp;Stitch</span>
    </a>

    <nav class="main-nav" aria-label="Categories">
      <?php global $CATEGORIES; foreach ($CATEGORIES as $cat): ?>
        <a href="/category.php?id=<?= urlencode($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
      <?php endforeach; ?>
      <a href="/quiz.php"><?= htmlspecialchars($QUIZ['nav_label'] ?? 'Find your match') ?></a>
      <a href="/blog.php">Blog</a>
      <a href="/downloads.php">Downloads</a>
      <a href="/locations.php">Stores</a>
      <a href="/contact.php">Contact</a>
    </nav>

    <form class="header-search" role="search" data-search-form action="/search.php" method="get">
      <input type="search" name="q" class="header-search__input"
             placeholder="Search products…" aria-label="Search products" autocomplete="off"
             value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
      <button type="submit" class="header-search__btn" aria-label="Search">🔍</button>
    </form>

    <div class="header-actions">
      <a class="header-actions__link header-actions__account" href="/account.php"
         data-account-link aria-label="Account">
        <span aria-hidden="true">👤</span> <span class="header-actions__account-label" data-account-label>Sign in</span>
      </a>
      <a class="header-actions__link" href="/wishlist.php" aria-label="Wishlist">
        ♥ <span class="badge" data-wishlist-count>0</span>
      </a>
      <a class="header-actions__link" href="/cart.php" aria-label="Cart">
        🛒 <span class="badge" data-cart-count>0</span>
      </a>
    </div>
  </div>
</header>

<main class="wrap page">
<?php if (!empty($PAGE_DATALAYER)): ?>
<script>
  /* Page-load view_* events, emitted INSIDE <main> on purpose.
     The SPA router (router.js) swaps only <main> on navigation, so this
     script re-executes on every client-side route change — repopulating
     SNS_PENDING_EVENTS, which SNS.initPage() then flushes once GTM is ready.
     (A <head> script would run only once, which is the whole SPA tracking trap.) */
  window.SNS_PENDING_EVENTS = window.SNS_PENDING_EVENTS || [];
  <?php foreach ($PAGE_DATALAYER as $event): ?>
  window.SNS_PENDING_EVENTS.push(<?= json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
  <?php endforeach; ?>
</script>
<?php endif; ?>
