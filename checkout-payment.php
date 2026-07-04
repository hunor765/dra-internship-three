<?php
/**
 * Checkout — Step 2: Payment (separate page from shipping).
 * Payment options include Cash on Delivery (the default).
 * GA4 event: add_payment_info (on "Place order"). We then build the order,
 * stash it in localStorage and forward to the thank-you page, which fires
 * the purchase event.
 */
require_once __DIR__ . '/includes/functions.php';
$PAGE_TITLE = 'Checkout · Payment';
require __DIR__ . '/includes/header.php';
?>

<div class="steps">
  <span class="is-done">1 · Shipping ✓</span>
  <span class="is-current">2 · Payment</span>
  <span>3 · Confirmation</span>
</div>

<h1>Payment</h1>

<div id="checkout-empty" style="display:none;">
  <div class="empty-state">
    <p>Your cart is empty — nothing to pay for.</p>
    <a class="btn" href="/index.php">Back to shop</a>
  </div>
</div>

<form id="payment-form" class="checkout-layout" style="display:none;">
  <div class="form-card">
    <h3 style="margin-top:0;">Choose a payment method</h3>

    <label class="pay-option is-selected" data-pay-option>
      <input type="radio" name="payment_type" value="Cash on Delivery" checked>
      <div>
        <div class="pay-option__title">💵 Cash on Delivery</div>
        <div class="muted">Pay with cash when your order arrives. No card needed.</div>
      </div>
    </label>

    <label class="pay-option" data-pay-option>
      <input type="radio" name="payment_type" value="Credit Card">
      <div>
        <div class="pay-option__title">💳 Credit / Debit Card</div>
        <div class="muted">Visa, Mastercard, Amex (dummy — enter anything).</div>
      </div>
    </label>

    <div id="card-fields" style="display:none;margin-top:8px;">
      <div class="form-grid">
        <div class="field field--full"><label>Card number</label><input value="4242 4242 4242 4242"></div>
        <div class="field"><label>Expiry</label><input value="12 / 29"></div>
        <div class="field"><label>CVC</label><input value="123"></div>
      </div>
    </div>
  </div>

  <aside class="summary">
    <h3>Order summary</h3>
    <div data-summary-lines></div>
    <div class="summary__row"><span>Subtotal</span><span data-subtotal>$0.00</span></div>
    <div class="summary__row"><span>Shipping (<span data-tier>Standard</span>)</span><span data-shipping>Free</span></div>
    <div class="summary__row summary__row--total"><span>Total</span><span data-total>$0.00</span></div>
    <button type="submit" class="btn btn--clay btn--block" style="margin-top:16px;">Place order</button>
    <a class="btn btn--ghost btn--block" href="/checkout-shipping.php" style="margin-top:10px;">Back to shipping</a>
  </aside>
</form>

<script>
(function () {
  var form  = document.getElementById('payment-form');
  var empty = document.getElementById('checkout-empty');
  var cardFields = null;

  function money(n) { return '$' + (Math.round(n * 100) / 100).toFixed(2); }
  function tier()   { return localStorage.getItem('sns_shipping_tier') || 'Standard'; }
  function shippingCost() { return tier() === 'Express' ? 6.90 : 0; }

  function renderSummary() {
    var cart = SNS.getCart();
    form.querySelector('[data-summary-lines]').innerHTML = cart.map(function (i) {
      return '<div class="summary__row"><span>' + i.item_name +
        (i.item_variant ? ' (' + i.item_variant + ')' : '') +
        ' × ' + i.quantity + '</span><span>' + money(i.price * i.quantity) + '</span></div>';
    }).join('');
    var subtotal = SNS.cartValue(cart);
    var ship = shippingCost();
    form.querySelector('[data-tier]').textContent = tier();
    form.querySelector('[data-subtotal]').textContent = money(subtotal);
    form.querySelector('[data-shipping]').textContent = ship ? money(ship) : 'Free';
    form.querySelector('[data-total]').textContent = money(subtotal + ship);
  }

  document.addEventListener('DOMContentLoaded', function () {
    var cart = SNS.getCart();
    if (!cart.length) { empty.style.display = 'block'; return; }
    form.style.display = 'grid';
    cardFields = document.getElementById('card-fields');
    renderSummary();

    form.querySelectorAll('[data-pay-option]').forEach(function (opt) {
      opt.addEventListener('click', function () {
        form.querySelectorAll('[data-pay-option]').forEach(function (o) { o.classList.remove('is-selected'); });
        opt.classList.add('is-selected');
        var isCard = form.querySelector('input[name="payment_type"]:checked').value === 'Credit Card';
        cardFields.style.display = isCard ? 'block' : 'none';
      });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var cart = SNS.getCart();
      if (!cart.length) return;

      var paymentType = form.querySelector('input[name="payment_type"]:checked').value;
      var subtotal = SNS.cartValue(cart);
      var ship = shippingCost();

      // add_payment_info — fires as the customer commits a payment method.
      SNS.pushEcommerce('add_payment_info', {
        currency: SNS.currency,
        value: subtotal,
        payment_type: paymentType,
        items: cart
      });

      // Build the order object that the thank-you page will use for purchase.
      var order = {
        transaction_id: 'SNS-' + Date.now().toString().slice(-8) + '-' + Math.floor(Math.random() * 900 + 100),
        currency: SNS.currency,
        value: SNS.round2(subtotal + ship),   // GA4 purchase value = total revenue
        tax: 0,
        shipping: SNS.round2(ship),
        shipping_tier: tier(),
        payment_type: paymentType,
        items: cart
      };

      localStorage.setItem('sns_last_order', JSON.stringify(order));

      // Empty the cart now that the order is placed.
      SNS.clearCart();

      window.location.href = '/thank-you.php';
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
