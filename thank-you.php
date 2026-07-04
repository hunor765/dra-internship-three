<?php
/**
 * Order confirmation / thank-you page.
 * GA4 event: purchase (fired once, on load, from the stored order).
 *
 * The order is read from localStorage (written by the payment step). We guard
 * against double-firing by tagging the order once it has been reported.
 */
require_once __DIR__ . '/includes/functions.php';
$PAGE_TITLE = 'Thank you';
require __DIR__ . '/includes/header.php';
?>

<div id="ty-none" style="display:none;">
  <div class="empty-state">
    <p>No recent order found.</p>
    <a class="btn" href="/index.php">Back to shop</a>
  </div>
</div>

<div id="ty-content" class="thankyou" style="display:none;">
  <div class="thankyou__check">🛍️✅</div>
  <h1>Thank you for your order!</h1>
  <p class="muted">A confirmation has been sent to your email (well — it would have been, on a real store).</p>

  <div class="order-box">
    <div class="summary__row"><span>Order number</span><span data-order-id></span></div>
    <div class="summary__row"><span>Payment method</span><span data-payment></span></div>
    <div class="summary__row"><span>Shipping</span><span data-shiptier></span></div>
    <hr style="border:none;border-top:1px solid var(--line);margin:12px 0;">
    <div data-lines></div>
    <div class="summary__row"><span>Shipping cost</span><span data-shipcost></span></div>
    <div class="summary__row summary__row--total"><span>Total paid</span><span data-total></span></div>
  </div>

  <a class="btn" href="/index.php" style="margin-top:24px;">Continue shopping</a>
</div>

<script>
(function () {
  function money(n) { return '$' + (Math.round(n * 100) / 100).toFixed(2); }

  document.addEventListener('DOMContentLoaded', function () {
    var raw = localStorage.getItem('sns_last_order');
    var none = document.getElementById('ty-none');
    var content = document.getElementById('ty-content');

    if (!raw) { none.style.display = 'block'; return; }

    var order;
    try { order = JSON.parse(raw); } catch (e) { none.style.display = 'block'; return; }

    content.style.display = 'block';

    // Populate the receipt.
    content.querySelector('[data-order-id]').textContent = order.transaction_id;
    content.querySelector('[data-payment]').textContent  = order.payment_type;
    content.querySelector('[data-shiptier]').textContent = order.shipping_tier;
    content.querySelector('[data-shipcost]').textContent = order.shipping ? money(order.shipping) : 'Free';
    content.querySelector('[data-total]').textContent    = money(order.value);
    content.querySelector('[data-lines]').innerHTML = order.items.map(function (i) {
      return '<div class="summary__row"><span>' + i.item_name +
        (i.item_variant ? ' (' + i.item_variant + ')' : '') +
        ' × ' + i.quantity + '</span><span>' + money(i.price * i.quantity) + '</span></div>';
    }).join('');

    // Fire purchase ONCE. Guard against refreshes double-counting revenue.
    if (localStorage.getItem('sns_purchase_fired') !== order.transaction_id) {
      SNS.pushEcommerce('purchase', {
        transaction_id: order.transaction_id,
        currency: order.currency,
        value: order.value,
        tax: order.tax,
        shipping: order.shipping,
        items: order.items
      });
      localStorage.setItem('sns_purchase_fired', order.transaction_id);
    }
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
