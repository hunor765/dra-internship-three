<?php
/**
 * Checkout — Step 1: Shipping information (kept separate from payment).
 * GA4 event: add_shipping_info (on "Continue to payment").
 */
require_once __DIR__ . '/includes/functions.php';
$PAGE_TITLE = 'Checkout · Shipping';
require __DIR__ . '/includes/header.php';
?>

<div class="steps">
  <span class="is-current">1 · Shipping</span>
  <span>2 · Payment</span>
  <span>3 · Confirmation</span>
</div>

<h1>Shipping details</h1>

<div id="checkout-empty" style="display:none;">
  <div class="empty-state">
    <p>Your cart is empty — nothing to check out.</p>
    <a class="btn" href="/index.php">Back to shop</a>
  </div>
</div>

<form id="shipping-form" class="checkout-layout" style="display:none;">
  <div class="form-card">
    <div class="form-grid">
      <div class="field"><label>First name</label><input name="first" value="Mia" required></div>
      <div class="field"><label>Last name</label><input name="last" value="Laurent" required></div>
      <div class="field field--full"><label>Email</label><input name="email" type="email" value="mia.laurent@example.com" required></div>
      <div class="field field--full"><label>Street address</label><input name="address" value="9 Boutique Row" required></div>
      <div class="field"><label>City</label><input name="city" value="Milton" required></div>
      <div class="field"><label>Postcode</label><input name="zip" value="ML1 3CD" required></div>
      <div class="field field--full">
        <label>Country</label>
        <select name="country">
          <option>United Kingdom</option>
          <option>United States</option>
          <option>Germany</option>
          <option>Canada</option>
        </select>
      </div>
    </div>

    <h3 style="margin-top:24px;">Shipping method</h3>
    <label class="pay-option is-selected" data-ship-option>
      <input type="radio" name="shipping_tier" value="Standard" data-tier="Standard" checked>
      <div><div class="pay-option__title">Standard — Free</div>
        <div class="muted">3–5 working days</div></div>
    </label>
    <label class="pay-option" data-ship-option>
      <input type="radio" name="shipping_tier" value="Express" data-tier="Express">
      <div><div class="pay-option__title">Express — $6.90</div>
        <div class="muted">1–2 working days</div></div>
    </label>
  </div>

  <aside class="summary">
    <h3>Order summary</h3>
    <div data-summary-lines></div>
    <div class="summary__row"><span>Subtotal</span><span data-subtotal>$0.00</span></div>
    <div class="summary__row"><span>Shipping</span><span data-shipping>Free</span></div>
    <div class="summary__row summary__row--total"><span>Total</span><span data-total>$0.00</span></div>
    <button type="submit" class="btn btn--block" style="margin-top:16px;">Continue to payment</button>
    <a class="btn btn--ghost btn--block" href="/cart.php" style="margin-top:10px;">Back to cart</a>
  </aside>
</form>

<script>
(function () {
  var form  = document.getElementById('shipping-form');
  var empty = document.getElementById('checkout-empty');

  function money(n) { return '$' + (Math.round(n * 100) / 100).toFixed(2); }
  function shippingCost() {
    var sel = form.querySelector('input[name="shipping_tier"]:checked');
    return (sel && sel.value === 'Express') ? 6.90 : 0;
  }

  function renderSummary() {
    var cart = SNS.getCart();
    var linesEl = form.querySelector('[data-summary-lines]');
    linesEl.innerHTML = cart.map(function (i) {
      return '<div class="summary__row"><span>' + i.item_name +
        (i.item_variant ? ' (' + i.item_variant + ')' : '') +
        ' × ' + i.quantity + '</span><span>' + money(i.price * i.quantity) + '</span></div>';
    }).join('');
    var subtotal = SNS.cartValue(cart);
    var ship = shippingCost();
    form.querySelector('[data-subtotal]').textContent = money(subtotal);
    form.querySelector('[data-shipping]').textContent = ship ? money(ship) : 'Free';
    form.querySelector('[data-total]').textContent = money(subtotal + ship);
  }

  SNS_READY(function () {
    var cart = SNS.getCart();
    if (!cart.length) { empty.style.display = 'block'; return; }
    form.style.display = 'grid';
    renderSummary();

    // Highlight selected shipping option + recompute totals.
    form.querySelectorAll('[data-ship-option]').forEach(function (opt) {
      opt.addEventListener('click', function () {
        form.querySelectorAll('[data-ship-option]').forEach(function (o) { o.classList.remove('is-selected'); });
        opt.classList.add('is-selected');
        setTimeout(renderSummary, 0);
      });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var tier = form.querySelector('input[name="shipping_tier"]:checked').value;

      // Persist chosen tier so the payment + purchase steps can reuse it.
      localStorage.setItem('sns_shipping_tier', tier);

      window.location.href = '/checkout-payment.php';

      SNS.pushEcommerce('add_shipping_info', {
        currency: SNS.currency,
        value: SNS.cartValue(SNS.getCart()),
        shipping_tier: tier,
        items: SNS.getCart()
      });
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
