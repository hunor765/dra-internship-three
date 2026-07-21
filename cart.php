<?php
/**
 * Cart page. Because the cart lives in localStorage (no backend), the line
 * items and the view_cart event are rendered/fired client-side.
 * GA4 events: view_cart (on load), remove_from_cart, add_to_cart (qty up),
 *             begin_checkout (button).
 */
require_once __DIR__ . '/includes/functions.php';
$PAGE_TITLE = 'Your Cart';
require __DIR__ . '/includes/header.php';
?>

<h1>Your cart</h1>

<div id="cart-root">
  <!-- filled by JS -->
</div>

<template id="cart-with-items">
  <div class="cart-layout">
    <div class="cart-lines" data-lines></div>
    <aside class="summary">
      <h3>Order summary</h3>
      <div class="summary__row"><span>Subtotal</span><span data-subtotal>$0.00</span></div>
      <div class="summary__row summary__row--discount" data-discount-row style="display:none;">
        <span>Discount <span class="coupon-tag" data-coupon-tag></span></span><span data-discount>-$0.00</span>
      </div>
      <div class="summary__row"><span>Shipping</span><span>Calculated next step</span></div>
      <div class="summary__row summary__row--total"><span>Total</span><span data-total>$0.00</span></div>

      <div class="coupon">
        <div class="coupon__row">
          <input type="text" data-coupon-input placeholder="Promo code" aria-label="Promo code" autocomplete="off">
          <button type="button" class="btn btn--ghost" data-coupon-apply>Apply</button>
        </div>
        <div class="coupon__status" data-coupon-status></div>
      </div>

      <a class="btn btn--block" href="/checkout-shipping.php" data-begin-checkout style="margin-top:16px;">Checkout</a>
      <a class="btn btn--ghost btn--block" href="/index.php" style="margin-top:10px;">Continue shopping</a>
    </aside>
  </div>
</template>

<script>
(function () {
  var root = document.getElementById('cart-root');

  function money(n) {
    return '$' + (Math.round(n * 100) / 100).toFixed(2);
  }

  function photo(item) {
    // Reuse a simple emoji tile; colour is not critical in the cart.
    return '<div class="product-photo" style="background:#f7edf0;border-color:#eadfe3;">'
         + '<span class="product-photo__icon">👗</span></div>';
  }

  window.renderCart = function () {
    var cart = SNS.getCart();
    root.innerHTML = '';

    if (!cart.length) {
      root.innerHTML =
        '<div class="empty-state"><p>Your cart is empty.</p>' +
        '<a class="btn" href="/index.php">Start shopping</a></div>';
      return;
    }

    var tpl = document.getElementById('cart-with-items').content.cloneNode(true);
    var linesEl = tpl.querySelector('[data-lines]');

    cart.forEach(function (item) {
      var key = SNS.lineKey(item);
      var row = document.createElement('div');
      row.className = 'cart-line';
      row.innerHTML =
        photo(item) +
        '<div>' +
          '<p class="cart-line__name">' + item.item_name + '</p>' +
          (item.item_variant ? '<div class="cart-line__variant">' + item.item_variant + '</div>' : '') +
          '<div class="cart-line__variant">' + money(item.price) + ' each</div>' +
          '<div class="cart-line__controls">' +
            '<label>Qty</label>' +
            '<input type="number" min="1" value="' + item.quantity + '" data-qty="' + encodeURIComponent(key) + '">' +
            '<button class="link-danger" data-remove="' + encodeURIComponent(key) + '">Remove</button>' +
          '</div>' +
        '</div>' +
        '<div class="price">' + money(item.price * item.quantity) + '</div>';
      linesEl.appendChild(row);
    });

    root.appendChild(tpl);

    // Wire qty + remove controls.
    root.querySelectorAll('[data-remove]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        SNS.removeFromCart(decodeURIComponent(btn.getAttribute('data-remove')));
      });
    });
    root.querySelectorAll('[data-qty]').forEach(function (input) {
      input.addEventListener('change', function () {
        SNS.changeQty(decodeURIComponent(input.getAttribute('data-qty')), input.value);
      });
    });

    // --- Order summary + coupon --------------------------------------
    function updateSummary() {
      var t = SNS.orderTotals(SNS.getCart());
      root.querySelector('[data-subtotal]').textContent = money(t.subtotal);
      root.querySelector('[data-total]').textContent = money(t.total);

      var dr = root.querySelector('[data-discount-row]');
      if (t.discount > 0) {
        dr.style.display = '';
        root.querySelector('[data-discount]').textContent = '-' + money(t.discount);
        root.querySelector('[data-coupon-tag]').textContent = t.couponCode || '';
      } else {
        dr.style.display = 'none';
      }

      var status = root.querySelector('[data-coupon-status]');
      var input  = root.querySelector('[data-coupon-input]');
      if (t.coupon) {
        if (input) input.value = t.couponCode;
        status.innerHTML = '<span class="coupon__ok">✓ ' + t.couponCode + ' — ' +
          (t.coupon.label || '') + '</span> <button type="button" class="link-danger" data-coupon-remove>Remove</button>';
        var rm = status.querySelector('[data-coupon-remove]');
        if (rm) rm.addEventListener('click', function () { SNS.setCoupon(null); updateSummary(); });
      } else {
        status.textContent = '';
      }
    }

    var applyBtn = root.querySelector('[data-coupon-apply]');
    if (applyBtn) {
      applyBtn.addEventListener('click', function () {
        var input  = root.querySelector('[data-coupon-input]');
        var status = root.querySelector('[data-coupon-status]');
        var code   = (input.value || '').trim();
        if (!code) return;
        var found = SNS.findCoupon(code);
        if (found) {
          SNS.setCoupon(found);
          SNS.pushEvent('apply_coupon', {
            coupon: found.code,
            discount_type: found.type,
            discount_amount: found.amount
          });
          updateSummary();
        } else {
          status.innerHTML = '<span class="coupon__err">“' + code + '” is not a valid code.</span>';
        }
      });
    }

    updateSummary();

    // begin_checkout on the checkout button (owned solely here — see main.js).
    var checkoutBtn = root.querySelector('[data-begin-checkout]');
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', function () {
        var t = SNS.orderTotals(SNS.getCart());
        var payload = { currency: SNS.currency, value: t.total, items: SNS.getCart() };
        if (t.couponCode) payload.coupon = t.couponCode;
        SNS.pushEcommerce('begin_checkout', payload);
      });
    }
  };

  SNS_READY(function () {
    renderCart();

    // Fire view_cart once on load (only if there is something to view).
    var cart = SNS.getCart();
    if (cart.length) {
      SNS.pushEcommerce('view_cart', {
        currency: SNS.currency,
        value: SNS.cartValue(cart),
        items: cart
      });
    }
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
