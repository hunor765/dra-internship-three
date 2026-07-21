/* ==========================================================================
 * Client-side "accounts" + the user_data dataLayer object.
 *
 * There is NO backend. A single user profile lives in localStorage; a session
 * flag marks whether they are signed in. Creating an account fires sign_up and
 * then pushes a user_data object carrying the business fields GTM/GA4 want:
 *
 *   user_id, email, phone_number, days_since_registration,
 *   last_category_ordered, last_category_wishlisted,
 *   total_revenue, last_purchase_date
 *
 * user_data is (re)pushed on every page/route while signed in, and whenever the
 * stats change (a purchase, a wishlist save), so the dataLayer always carries a
 * current snapshot. All pushes go through SNS.pushEvent so they wait for the
 * same GTM readiness gate as every other event.
 * ======================================================================== */

(function () {
  'use strict';

  var USER_KEY    = 'sns_user';
  var SESSION_KEY = 'sns_session';

  function round2(n) { return Math.round((+n || 0) * 100) / 100; }

  function readUser() {
    try { return JSON.parse(localStorage.getItem(USER_KEY)) || null; }
    catch (e) { return null; }
  }
  function writeUser(u) { localStorage.setItem(USER_KEY, JSON.stringify(u)); }

  function isLoggedIn() {
    var u = readUser();
    return !!(u && localStorage.getItem(SESSION_KEY) === u.user_id);
  }
  function currentUser() { return isLoggedIn() ? readUser() : null; }

  function daysSince(ms) {
    if (!ms) return 0;
    return Math.max(0, Math.floor((Date.now() - ms) / 86400000));
  }
  function todayISO() { return new Date().toISOString().slice(0, 10); }

  function newUserId() {
    return 'U-' + Date.now().toString(36) + '-' + Math.floor(Math.random() * 1e6).toString(36);
  }

  function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

  /* -------- dataLayer pushes ------------------------------------------ */
  function pushEvent(name, params) {
    if (window.SNS && typeof window.SNS.pushEvent === 'function') {
      window.SNS.pushEvent(name, params);
    } else {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(Object.assign({ event: name }, params || {}));
    }
  }

  /* The payload lives in the <head> snippet (see includes/header.php), which
   * pushes it before the GTM container so it is present at consent
   * initialisation. Re-pushes — a login, a purchase, an SPA route change —
   * go back through the same builder so the two can never drift.
   *
   * These pushes are deliberately not gated on GTM readiness: user_data
   * should never be held back, and the dataLayer is replayed by GTM anyway. */
  function pushUserData() {
    var u = currentUser();
    if (!u) return;
    if (window.SNS_USER_EARLY && typeof window.SNS_USER_EARLY.push === 'function') {
      window.SNS_USER_EARLY.push();
      return;
    }
    pushEvent('user_data', {
      user_id: u.user_id,
      user_data: {
        user_id:                  u.user_id,
        email:                    u.email,
        phone_number:             u.phone,
        days_since_registration:  daysSince(u.registered_at),
        last_category_ordered:    u.last_category_ordered || null,
        last_category_wishlisted: u.last_category_wishlisted || null,
        total_revenue:            round2(u.total_revenue),
        last_purchase_date:       u.last_purchase_date || null
      }
    });
  }

  /* -------- account lifecycle ----------------------------------------- */
  function register(data) {
    var u = {
      user_id:                  newUserId(),
      first_name:               (data.first_name || '').trim(),
      last_name:                (data.last_name || '').trim(),
      email:                    (data.email || '').trim(),
      phone:                    (data.phone || '').trim(),
      registered_at:            Date.now(),
      total_revenue:            0,
      last_purchase_date:       null,
      last_category_ordered:    null,
      last_category_wishlisted: null,
      orders:                   0
    };
    writeUser(u);
    localStorage.setItem(SESSION_KEY, u.user_id);

    pushEvent('sign_up', { method: 'email', user_id: u.user_id });
    pushUserData();
    updateHeader();
    return u;
  }

  function login(email) {
    var u = readUser();
    if (!u || u.email.toLowerCase() !== (email || '').trim().toLowerCase()) return false;
    localStorage.setItem(SESSION_KEY, u.user_id);
    pushEvent('login', { method: 'email', user_id: u.user_id });
    pushUserData();
    updateHeader();
    return true;
  }

  function logout() {
    localStorage.removeItem(SESSION_KEY);
    pushEvent('logout', {});
    updateHeader();
  }

  /* -------- stat updates that feed user_data -------------------------- */
  function recordPurchase(order) {
    var u = readUser();
    if (!u) return;
    u.total_revenue = round2((u.total_revenue || 0) + (+order.value || 0));
    u.last_purchase_date = todayISO();
    u.orders = (u.orders || 0) + 1;
    var items = order.items || [];
    if (items.length) {
      u.last_category_ordered = items[items.length - 1].item_category || u.last_category_ordered;
    }
    writeUser(u);
    if (isLoggedIn()) pushUserData();
  }

  function recordWishlist(item) {
    var u = readUser();
    if (!u || !item) return;
    u.last_category_wishlisted = item.item_category || u.last_category_wishlisted;
    writeUser(u);
    if (isLoggedIn()) pushUserData();
  }

  /* A refund against a stored order (GA4 recommended event). Reverses the
   * order's revenue from the lifetime total. */
  function refund(order) {
    if (!order) return;
    if (window.SNS && typeof window.SNS.pushEcommerce === 'function') {
      window.SNS.pushEcommerce('refund', {
        transaction_id: order.transaction_id,
        currency: order.currency,
        value: order.value,
        items: order.items
      });
    }
    var u = readUser();
    if (u) {
      u.total_revenue = round2(Math.max(0, (u.total_revenue || 0) - (+order.value || 0)));
      writeUser(u);
      if (isLoggedIn()) pushUserData();
    }
  }

  /* -------- header chrome --------------------------------------------- */
  function updateHeader() {
    var label = document.querySelector('[data-account-label]');
    if (!label) return;
    var u = currentUser();
    label.textContent = u ? (u.first_name || 'Account') : 'Sign in';
  }

  /* Runs on first load and every SPA route change (called from SNS.initPage).
   *
   * Deliberately does NOT push user_data. A real page load pushes it from the
   * <head>, before GTM; an SPA route pushes it from router.js, before that
   * route's page_view. initPage() runs after both, so pushing here would only
   * ever duplicate the event — and land it behind the page_view it belongs to. */
  function onPage() {
    updateHeader();
  }

  window.SNS_USER = {
    get: currentUser,
    isLoggedIn: isLoggedIn,
    register: register,
    login: login,
    logout: logout,
    recordPurchase: recordPurchase,
    recordWishlist: recordWishlist,
    refund: refund,
    pushUserData: pushUserData,
    updateHeader: updateHeader,
    onPage: onPage,
    isEmail: isEmail,
    daysSince: daysSince
  };
})();
