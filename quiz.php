<?php
/**
 * Product personalization quiz.
 *
 * Four questions; on submit the client picks a random set of products from the
 * catalog and shows them as recommendations.
 *
 * dataLayer events (all flat, via SNS.pushEvent / SNS.pushEcommerce):
 *   - quiz_start        (first interaction with the quiz)
 *   - quiz_complete     (submit — carries the answers + result item ids)
 *   - view_item_list    (the recommended products, list "Quiz Recommendations")
 *   - select_item / add_to_cart / add_to_wishlist (result cards, via main.js)
 */
require_once __DIR__ . '/includes/functions.php';

$PAGE_TITLE = $QUIZ['title'];

// Catalog handed to the client for the random pick. ga_item keeps the GA4
// payload clean; icon/color are carried alongside just for the card artwork.
$catalog = [];
foreach ($PRODUCTS as $p) {
    $c = get_category($p['category']);
    $catalog[] = [
        'item'  => ga_item($p),
        'icon'  => $c['icon'] ?? '🛍️',
        'color' => $c['color'] ?? '#888888',
    ];
}

require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb"><a href="/index.php">Home</a> / <?= htmlspecialchars($QUIZ['nav_label']) ?></nav>

<section class="quiz" data-quiz
         data-quiz-id="<?= htmlspecialchars($QUIZ['id']) ?>"
         data-list-id="<?= htmlspecialchars($QUIZ['result_list_id']) ?>"
         data-list-name="<?= htmlspecialchars($QUIZ['result_list_name']) ?>"
         data-result-count="<?= (int) $QUIZ['result_count'] ?>">
  <h1><?= htmlspecialchars($QUIZ['title']) ?></h1>
  <p class="muted" style="max-width:60ch;"><?= htmlspecialchars($QUIZ['intro']) ?></p>

  <form class="quiz__form" data-quiz-form novalidate>
    <?php foreach ($QUIZ['questions'] as $qi => $question): ?>
      <fieldset class="quiz__q">
        <legend><span class="quiz__n"><?= $qi + 1 ?></span> <?= htmlspecialchars($question['q']) ?></legend>
        <div class="quiz__opts">
          <?php foreach ($question['a'] as $ai => $answer): ?>
            <label class="quiz__opt">
              <input type="radio" name="<?= htmlspecialchars($question['id']) ?>"
                     value="<?= htmlspecialchars($answer) ?>"<?= $ai === 0 ? ' checked' : '' ?>>
              <span><?= htmlspecialchars($answer) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>
    <?php endforeach; ?>

    <button type="submit" class="btn">See my matches</button>
  </form>

  <div class="quiz__results" data-quiz-results style="display:none;"></div>
</section>

<script type="application/json" data-quiz-catalog>
  <?= json_encode($catalog, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<script>
(function () {
  var root = document.querySelector('[data-quiz]');
  if (!root) return;

  var form     = root.querySelector('[data-quiz-form]');
  var resultsEl = root.querySelector('[data-quiz-results]');
  var catEl    = document.querySelector('[data-quiz-catalog]');
  var quizId   = root.getAttribute('data-quiz-id');
  var listId   = root.getAttribute('data-list-id');
  var listName = root.getAttribute('data-list-name');
  var count    = parseInt(root.getAttribute('data-result-count'), 10) || 3;

  var catalog = [];
  try { catalog = JSON.parse(catEl.textContent); } catch (e) { catalog = []; }

  function money(n) { return '$' + (Math.round(n * 100) / 100).toFixed(2); }

  // quiz_start on the first answer change (fired once).
  var started = false;
  form.addEventListener('change', function () {
    if (started) return;
    started = true;
    SNS.pushEvent('quiz_start', { quiz_id: quizId });
  });

  // Random distinct sample from the catalog.
  function sample(arr, k) {
    var copy = arr.slice();
    for (var i = copy.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var t = copy[i]; copy[i] = copy[j]; copy[j] = t;
    }
    return copy.slice(0, Math.min(k, copy.length));
  }

  function card(entry, idx) {
    var it = Object.assign({}, entry.item, {
      index: idx, item_list_id: listId, item_list_name: listName
    });
    var url  = '/product.php?id=' + encodeURIComponent(it.item_id);
    var data = JSON.stringify(it).replace(/"/g, '&quot;');
    var photo = '<div class="product-photo" style="height:180px;background:linear-gradient(135deg,' +
      entry.color + '22,' + entry.color + '55);border-color:' + entry.color + '33;">' +
      '<span class="product-photo__icon">' + entry.icon + '</span></div>';
    return '<article class="product-card">' +
        '<a class="prod-link" href="' + url + '" data-select-item data-item="' + data +
          '" data-list-id="' + listId + '" data-list-name="' + listName + '">' + photo + '</a>' +
        '<div class="product-card__body">' +
          '<span class="product-card__cat">' + (it.item_category || '') + '</span>' +
          '<h3 class="product-card__name"><a href="' + url + '" data-select-item data-item="' + data +
            '" data-list-id="' + listId + '" data-list-name="' + listName + '">' + it.item_name + '</a></h3>' +
          '<div class="product-card__meta"><span class="price">' + money(it.price) + '</span></div>' +
        '</div>' +
        '<div class="product-card__actions" data-product-scope>' +
          '<button class="btn" data-add-to-cart data-item="' + data + '">Add to cart</button>' +
          '<button class="wishlist-btn" data-toggle-wishlist data-item="' + data + '" aria-label="Add to wishlist">♥</button>' +
        '</div></article>';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    var answers = {};
    Array.prototype.forEach.call(form.querySelectorAll('input[type="radio"]:checked'), function (r) {
      answers[r.name] = r.value;
    });

    var picks = sample(catalog, count);
    var items = picks.map(function (entry, i) {
      return Object.assign({}, entry.item, {
        index: i + 1, item_list_id: listId, item_list_name: listName
      });
    });

    // quiz_complete — the answers plus what we returned.
    SNS.pushEvent('quiz_complete', {
      quiz_id: quizId,
      answers: answers,
      result_count: items.length,
      result_item_ids: items.map(function (i) { return i.item_id; })
    });

    // Paint the recommendations.
    if (!items.length) {
      resultsEl.innerHTML = '<div class="empty-state"><p>No matches to show right now.</p></div>';
    } else {
      resultsEl.innerHTML =
        '<div class="section-head"><h2>Your matches</h2></div>' +
        '<div class="product-grid">' + picks.map(function (entry, i) { return card(entry, i + 1); }).join('') + '</div>';
    }
    resultsEl.style.display = 'block';

    // Impression for the recommended list, then wire the freshly-injected cards.
    if (items.length) {
      SNS.pushEcommerce('view_item_list', {
        item_list_id: listId,
        item_list_name: listName,
        items: items
      });
      SNS.bindCards(resultsEl);
    }

    resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
