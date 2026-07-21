<?php
/**
 * Account dashboard (client-side, localStorage — no backend).
 *
 * Signed out: a sign-in form (+ link to create an account).
 * Signed in:  profile, lifetime stats, a live preview of the user_data object
 *             pushed to the dataLayer, the last order, a refund action
 *             (fires the GA4 refund event) and sign out.
 */
require_once __DIR__ . '/includes/functions.php';
$PAGE_TITLE = 'Your account';
require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb"><a href="/index.php">Home</a> / Account</nav>

<!-- Signed-out state --------------------------------------------------- -->
<div class="auth-page" data-account-guest style="display:none;">
  <div class="form-card">
    <h1>Sign in</h1>
    <p class="muted">Signing in re-pushes your <code>user_data</code> object to the dataLayer.</p>
    <form data-login-form novalidate>
      <div class="field"><label for="l-email">Email</label>
        <input id="l-email" name="email" type="email" placeholder="you@example.com"></div>
      <div class="field"><label for="l-pass">Password</label>
        <input id="l-pass" name="password" type="password" placeholder="••••••••"></div>
      <button type="submit" class="btn btn--block" style="margin-top:16px;">Sign in</button>
      <div class="field__error" data-login-error style="margin-top:10px;"></div>
      <p class="muted" style="margin-top:12px;font-size:.9rem;">
        New here? <a href="/register.php">Create an account</a>.
      </p>
    </form>
  </div>
</div>

<!-- Signed-in dashboard ------------------------------------------------ -->
<div data-account-dash style="display:none;">
  <div class="account-head">
    <h1>Hi, <span data-dash-name></span> 👋</h1>
    <button class="btn btn--ghost" data-logout>Sign out</button>
  </div>

  <div class="account-grid">
    <div class="info-card">
      <h3>Profile</h3>
      <div class="summary__row"><span>Name</span><span data-dash-fullname></span></div>
      <div class="summary__row"><span>Email</span><span data-dash-email></span></div>
      <div class="summary__row"><span>Phone</span><span data-dash-phone></span></div>
      <div class="summary__row"><span>User ID</span><span data-dash-userid></span></div>
      <div class="summary__row"><span>Member since</span><span data-dash-since></span></div>
    </div>

    <div class="info-card">
      <h3>Your activity</h3>
      <div class="summary__row"><span>Days registered</span><span data-dash-days></span></div>
      <div class="summary__row"><span>Total revenue</span><span data-dash-revenue></span></div>
      <div class="summary__row"><span>Orders placed</span><span data-dash-orders></span></div>
      <div class="summary__row"><span>Last purchase</span><span data-dash-lastpurchase></span></div>
      <div class="summary__row"><span>Last category ordered</span><span data-dash-lastordered></span></div>
      <div class="summary__row"><span>Last category wishlisted</span><span data-dash-lastwished></span></div>
    </div>
  </div>

  <div class="info-card" style="margin-top:22px;">
    <h3>Last order</h3>
    <div data-dash-order></div>
  </div>

  <div class="info-card" style="margin-top:22px;">
    <h3><code>user_data</code> pushed to the dataLayer</h3>
    <p class="muted" style="margin-top:0;">This is re-pushed on every page while you're signed in.</p>
    <pre class="userdata-preview" data-dash-userdata></pre>
  </div>
</div>

<script>
(function () {
  var guest = document.querySelector('[data-account-guest]');
  var dash  = document.querySelector('[data-account-dash]');
  if (!guest || !dash) return;

  function money(n) { return '$' + (Math.round((+n || 0) * 100) / 100).toFixed(2); }
  function fmtDate(ms) { try { return new Date(ms).toLocaleDateString(); } catch (e) { return '—'; } }

  function render() {
    var u = SNS_USER.get();
    if (!u) {
      dash.style.display = 'none';
      guest.style.display = 'block';
      return;
    }
    guest.style.display = 'none';
    dash.style.display = 'block';

    function set(sel, val) { var el = dash.querySelector(sel); if (el) el.textContent = (val === null || val === undefined || val === '') ? '—' : val; }

    set('[data-dash-name]', u.first_name || 'there');
    set('[data-dash-fullname]', ((u.first_name || '') + ' ' + (u.last_name || '')).trim());
    set('[data-dash-email]', u.email);
    set('[data-dash-phone]', u.phone);
    set('[data-dash-userid]', u.user_id);
    set('[data-dash-since]', fmtDate(u.registered_at));
    set('[data-dash-days]', SNS_USER.daysSince(u.registered_at));
    set('[data-dash-revenue]', money(u.total_revenue));
    set('[data-dash-orders]', u.orders || 0);
    set('[data-dash-lastpurchase]', u.last_purchase_date || '—');
    set('[data-dash-lastordered]', u.last_category_ordered || '—');
    set('[data-dash-lastwished]', u.last_category_wishlisted || '—');

    // user_data preview (mirrors what auth.js pushes).
    var preview = {
      user_id: u.user_id,
      email: u.email,
      phone_number: u.phone,
      days_since_registration: SNS_USER.daysSince(u.registered_at),
      last_category_ordered: u.last_category_ordered || null,
      last_category_wishlisted: u.last_category_wishlisted || null,
      total_revenue: Math.round((u.total_revenue || 0) * 100) / 100,
      last_purchase_date: u.last_purchase_date || null
    };
    var pv = dash.querySelector('[data-dash-userdata]');
    if (pv) pv.textContent = JSON.stringify(preview, null, 2);

    // Last order + refund.
    renderOrder();
  }

  function renderOrder() {
    var host = dash.querySelector('[data-dash-order]');
    var raw = localStorage.getItem('sns_last_order');
    if (!raw) { host.innerHTML = '<p class="muted">No orders yet.</p>'; return; }
    var order; try { order = JSON.parse(raw); } catch (e) { host.innerHTML = '<p class="muted">No orders yet.</p>'; return; }

    var refunded = localStorage.getItem('sns_refunded') === order.transaction_id;
    var lines = (order.items || []).map(function (i) {
      return '<div class="summary__row"><span>' + i.item_name +
        (i.item_variant ? ' (' + i.item_variant + ')' : '') + ' × ' + i.quantity +
        '</span><span>' + money(i.price * i.quantity) + '</span></div>';
    }).join('');

    host.innerHTML =
      '<div class="summary__row"><span>Order number</span><span>' + order.transaction_id + '</span></div>' +
      lines +
      '<div class="summary__row summary__row--total"><span>Total paid</span><span>' + money(order.value) + '</span></div>' +
      (refunded
        ? '<p class="coupon__ok" style="margin-top:12px;">✓ Refunded — a <code>refund</code> event was pushed.</p>'
        : '<button class="btn btn--ghost" data-refund style="margin-top:12px;">Request a refund</button>');

    var rb = host.querySelector('[data-refund]');
    if (rb) rb.addEventListener('click', function () {
      SNS_USER.refund(order);                                  // fires refund
      localStorage.setItem('sns_refunded', order.transaction_id);
      renderOrder();
      render();   // revenue changed
    });
  }

  // Login form.
  var loginForm = document.querySelector('[data-login-form]');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var email = (loginForm.querySelector('[name="email"]').value || '').trim();
      var err = document.querySelector('[data-login-error]');
      if (SNS_USER.login(email)) {
        err.textContent = '';
        render();
      } else {
        err.textContent = 'No account found with that email. Create one instead.';
      }
    });
  }

  var logoutBtn = document.querySelector('[data-logout]');
  if (logoutBtn) logoutBtn.addEventListener('click', function () { SNS_USER.logout(); render(); });

  render();
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
