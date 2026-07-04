<?php
/**
 * Wishlist page. Rendered client-side from localStorage.
 * GA4 events: view_item_list (list = "wishlist", on load),
 *             add_to_cart (move to cart), select_item (click through).
 * (GA4 has add_to_wishlist but no standard "remove"/"view wishlist" event,
 *  so we model the page view as a view_item_list.)
 */
require_once __DIR__ . '/includes/functions.php';
$PAGE_TITLE = 'Your Wishlist';
require __DIR__ . '/includes/header.php';
?>

<h1>Your wishlist ♥</h1>

<div id="wishlist-root"></div>

<script>
(function () {
  var root = document.getElementById('wishlist-root');

  function money(n) { return '$' + (Math.round(n * 100) / 100).toFixed(2); }

  function render() {
    var wl = SNS.getWishlist();
    root.innerHTML = '';

    if (!wl.length) {
      root.innerHTML =
        '<div class="empty-state"><p>You have not saved anything yet.</p>' +
        '<a class="btn" href="/index.php">Find something to love</a></div>';
      return;
    }

    var grid = document.createElement('div');
    grid.className = 'product-grid';

    wl.forEach(function (item, i) {
      var idx = i + 1;
      var url = '/product.php?id=' + encodeURIComponent(item.item_id);
      var card = document.createElement('article');
      card.className = 'product-card';
      card.innerHTML =
        '<a href="' + url + '"><div class="product-photo" style="height:180px;background:#f7edf0;border-color:#eadfe3;">' +
          '<span class="product-photo__icon">👗</span></div></a>' +
        '<div class="product-card__body">' +
          '<span class="product-card__cat">' + (item.item_category || '') + '</span>' +
          '<h3 class="product-card__name"><a href="' + url + '">' + item.item_name +
            (item.item_variant ? ' — ' + item.item_variant : '') + '</a></h3>' +
          '<div class="product-card__meta"><span class="price">' + money(item.price) + '</span></div>' +
        '</div>' +
        '<div class="product-card__actions">' +
          '<button class="btn" data-move="' + i + '">Add to cart</button>' +
          '<button class="wishlist-btn is-active" data-drop="' + i + '">✕</button>' +
        '</div>';
      grid.appendChild(card);
    });

    root.appendChild(grid);

    root.querySelectorAll('[data-move]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = SNS.getWishlist()[+btn.getAttribute('data-move')];
        if (!item) return;
        var cart = SNS.getCart();
        var key = SNS.lineKey(item);
        var existing = cart.find(function (c) { return SNS.lineKey(c) === key; });
        if (existing) { existing.quantity += 1; } else { cart.push(Object.assign({}, item)); }
        SNS.saveCart(cart);
        SNS.pushEcommerce('add_to_cart', {
          currency: SNS.currency,
          value: SNS.round2(item.price * item.quantity),
          items: [item]
        });
        btn.textContent = 'Added ✓';
        setTimeout(function () { btn.textContent = 'Add to cart'; }, 1200);
      });
    });

    root.querySelectorAll('[data-drop]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var item = SNS.getWishlist()[+btn.getAttribute('data-drop')];
        if (item) SNS.removeFromWishlist(item); // fires remove_from_wishlist
        render();
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    render();
    var wl = SNS.getWishlist();
    if (wl.length) {
      SNS.pushEcommerce('view_item_list', {
        item_list_id: 'wishlist',
        item_list_name: 'Wishlist',
        items: wl.map(function (it, i) {
          return Object.assign({}, it, {
            index: i + 1,
            item_list_id: 'wishlist',
            item_list_name: 'Wishlist'
          });
        })
      });
    }
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
