<?php
/**
 * Create an account.
 *
 * There is no backend — the profile is stored in localStorage. Submitting a
 * valid form calls SNS_USER.register(), which fires:
 *   - sign_up   (GA4 recommended event)
 *   - user_data (the business fields: user_id, email, phone_number,
 *                days_since_registration, last_category_ordered/wishlisted,
 *                total_revenue, last_purchase_date)
 * form_start / form_error fire like the other forms on the site.
 */
require_once __DIR__ . '/includes/functions.php';
$PAGE_TITLE = 'Create your account';
require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb"><a href="/index.php">Home</a> / Create account</nav>

<div class="auth-page">
  <div class="form-card">
    <h1>Create your account</h1>
    <p class="muted">Join <?= htmlspecialchars($STORE['name']) ?>. Creating an account pushes a
      <code>sign_up</code> event and a <code>user_data</code> object to the dataLayer.</p>

    <form data-register-form novalidate>
      <div class="form-grid">
        <div class="field"><label for="r-first">First name</label>
          <input id="r-first" name="first_name" placeholder="Fern"></div>
        <div class="field"><label for="r-last">Last name</label>
          <input id="r-last" name="last_name" placeholder="Greenfield"></div>
        <div class="field field--full"><label for="r-email">Email</label>
          <input id="r-email" name="email" type="email" placeholder="you@example.com"></div>
        <div class="field field--full"><label for="r-phone">Phone number</label>
          <input id="r-phone" name="phone" type="tel" placeholder="+44 7700 900123"></div>
        <div class="field field--full"><label for="r-pass">Password</label>
          <input id="r-pass" name="password" type="password" placeholder="••••••••"></div>
      </div>
      <button type="submit" class="btn btn--block" style="margin-top:18px;">Create account</button>
      <p class="muted" style="margin-top:12px;font-size:.9rem;">
        Already have an account? <a href="/account.php">Sign in</a>.
      </p>
    </form>

    <div data-register-success style="display:none;">
      <div class="contact-success__icon">✅</div>
      <h2>You're in!</h2>
      <p class="muted">A <code>sign_up</code> event and a <code>user_data</code> object just hit the dataLayer.</p>
      <a class="btn" href="/account.php">Go to your account</a>
    </div>
  </div>
</div>

<script>
(function () {
  var form    = document.querySelector('[data-register-form]');
  var success = document.querySelector('[data-register-success]');
  if (!form) return;

  var started = false;
  form.addEventListener('input', function () {
    if (started) return;
    started = true;
    SNS.pushEvent('form_start', { form_name: 'register' });
  });

  function setError(name, msg) {
    var field = form.querySelector('[name="' + name + '"]');
    if (!field) return;
    var wrap = field.closest('.field');
    wrap.classList.toggle('field--error', !!msg);
    var note = wrap.querySelector('.field__error');
    if (msg && !note) { note = document.createElement('span'); note.className = 'field__error'; wrap.appendChild(note); }
    if (note) note.textContent = msg || '';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var v = {};
    ['first_name', 'last_name', 'email', 'phone', 'password'].forEach(function (n) {
      v[n] = (form.querySelector('[name="' + n + '"]').value || '').trim();
    });

    var errors = [];
    setError('first_name', ''); setError('email', ''); setError('phone', '');
    if (!v.first_name) { setError('first_name', 'Please tell us your first name.'); errors.push('first_name'); }
    if (!SNS_USER.isEmail(v.email)) { setError('email', 'That email address does not look right.'); errors.push('email'); }
    if (v.phone.replace(/[^0-9]/g, '').length < 7) { setError('phone', 'Please enter a valid phone number.'); errors.push('phone'); }

    if (errors.length) {
      SNS.pushEvent('form_error', { form_name: 'register', error_fields: errors.join(','), error_count: errors.length });
      return;
    }

    SNS_USER.register(v);   // fires sign_up + user_data
    form.style.display = 'none';
    success.style.display = 'block';
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
