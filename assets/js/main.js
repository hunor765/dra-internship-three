/* ==========================================================================
 * Thread & Stitch — client-side cart, wishlist and GA4 dataLayer plumbing.
 *
 * There is NO backend and NO database. Cart + wishlist live in localStorage
 * so they survive the full-page navigations of this (deliberately non-SPA)
 * site. Every meaningful interaction pushes a standard GA4 ecommerce event
 * to window.dataLayer for GTM to pick up.
 * ======================================================================== */

(function () {
  'use strict';

  window.dataLayer = window.dataLayer || [];
  var CURRENCY = window.STORE_CURRENCY || 'USD';
  var GTM_ID  = window.GTM_CONTAINER_ID;

  var CART_KEY = 'sns_cart';
  var WISH_KEY = 'sns_wishlist';

  /* ----------------------------------------------------------------------
   * GTM readiness gate
   *
   * Every ecommerce event is held until the GTM container has actually
   * loaded (i.e. window.google_tag_manager[GTM_ID] exists). Interaction
   * events fire immediately once GTM is up; the page-load view_* events
   * queued in the <head> are flushed the moment it becomes ready.
   *
   * A ~10s fallback fires anything still queued even if GTM never loads
   * (e.g. blocked by an ad blocker) so the training site keeps working.
   * -------------------------------------------------------------------- */
  var _gtmReady   = false;
  var _readyQueue = [];

  function gtmIsLoaded() {
    return !!(window.google_tag_manager && GTM_ID && window.google_tag_manager[GTM_ID]);
  }
  function markGtmReady() {
    if (_gtmReady) return;
    _gtmReady = true;
    _readyQueue.splice(0).forEach(function (fn) { try { fn(); } catch (e) {} });
  }
  function runWhenGtmReady(fn) {
    if (_gtmReady) { fn(); return; }
    _readyQueue.push(fn);
  }
  // Poll for the container. Starts as soon as main.js executes.
  (function pollGtm(attempt) {
    if (gtmIsLoaded()) { markGtmReady(); return; }
    if (attempt >= 100) { markGtmReady(); return; } // ~10s safety fallback
    setTimeout(function () { pollGtm(attempt + 1); }, 100);
  })(0);

  /* ----------------------------------------------------------------------
   * Storage helpers
   * -------------------------------------------------------------------- */
  function read(key) {
    try { return JSON.parse(localStorage.getItem(key)) || []; }
    catch (e) { return []; }
  }
  function write(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  function getCart()      { return read(CART_KEY); }
  function saveCart(c)    { write(CART_KEY, c); refreshBadges(); }
  function getWishlist()  { return read(WISH_KEY); }
  function saveWishlist(w){ write(WISH_KEY, w); refreshBadges(); }

  /* A stable line key: same product + same variant collapse into one line. */
  function lineKey(item) {
    return item.item_id + '::' + (item.item_variant || '');
  }

  /* ----------------------------------------------------------------------
   * GA4 dataLayer helper
   *
   * Always clears the previous ecommerce object before pushing a new one,
   * which is the documented GA4 best practice to avoid stale merged data.
   * -------------------------------------------------------------------- */
  function pushEcommerce(eventName, ecommerce) {
    runWhenGtmReady(function () {
      window.dataLayer.push({ ecommerce: null });
      window.dataLayer.push({
        event: eventName,
        ecommerce: ecommerce
      });
    });
  }

  /* ----------------------------------------------------------------------
   * Generic (non-ecommerce) event helper.
   *
   * Content events — contact form, newsletter, blog, locations — are flat
   * events with no `ecommerce` object, so they must NOT go through
   * pushEcommerce (which would nest them and null the ecommerce key).
   * They still wait for the GTM readiness gate like everything else.
   * -------------------------------------------------------------------- */
  function pushEvent(eventName, params) {
    runWhenGtmReady(function () {
      window.dataLayer.push(Object.assign({ event: eventName }, params || {}));
    });
  }

  function cartValue(cart) {
    return round2(cart.reduce(function (sum, i) {
      return sum + (i.price * i.quantity);
    }, 0));
  }
  function round2(n) { return Math.round(n * 100) / 100; }

  /* ----------------------------------------------------------------------
   * Badge counts in the header
   * -------------------------------------------------------------------- */
  function refreshBadges() {
    var cartQty = getCart().reduce(function (n, i) { return n + i.quantity; }, 0);
    var wishQty = getWishlist().length;
    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
      el.textContent = cartQty;
    });
    document.querySelectorAll('[data-wishlist-count]').forEach(function (el) {
      el.textContent = wishQty;
    });
  }

  /* ----------------------------------------------------------------------
   * Reading item data off a button's data-item attribute
   * -------------------------------------------------------------------- */
  function itemFromEl(el) {
    try { return JSON.parse(el.getAttribute('data-item')); }
    catch (e) { return null; }
  }

  /* ======================================================================
   * ADD TO CART
   * ==================================================================== */
  function addToCart(item) {
    var cart = getCart();
    var key  = lineKey(item);
    var existing = cart.find(function (i) { return lineKey(i) === key; });

    if (existing) {
      existing.quantity += item.quantity;
    } else {
      cart.push(item);
    }
    saveCart(cart);

    pushEcommerce('add_to_cart', {
      currency: CURRENCY,
      value: round2(item.price * item.quantity),
      items: [item]
    });
  }

  /* ======================================================================
   * REMOVE FROM CART
   * ==================================================================== */
  function removeFromCart(key) {
    var cart = getCart();
    var item = cart.find(function (i) { return lineKey(i) === key; });
    if (!item) return;

    cart = cart.filter(function (i) { return lineKey(i) !== key; });
    saveCart(cart);

    pushEcommerce('remove_from_cart', {
      currency: CURRENCY,
      value: round2(item.price * item.quantity),
      items: getCart()
    });
    // Re-render the cart page if we're on it.
    if (typeof window.renderCart === 'function') window.renderCart();
  }

  /* ======================================================================
   * CHANGE QUANTITY (fires add_to_cart or remove_from_cart on the delta)
   * ==================================================================== */
  function changeQty(key, newQty) {
    var cart = getCart();
    var item = cart.find(function (i) { return lineKey(i) === key; });
    if (!item) return;
    newQty = Math.max(1, parseInt(newQty, 10) || 1);

    var delta = newQty - item.quantity;
    if (delta === 0) return;

    var deltaItem = Object.assign({}, item, { quantity: Math.abs(delta) });
    item.quantity = newQty;
    saveCart(cart);

    pushEcommerce(delta > 0 ? 'add_to_cart' : 'remove_from_cart', {
      currency: CURRENCY,
      value: round2(deltaItem.price * deltaItem.quantity),
      items: [deltaItem]
    });
    if (typeof window.renderCart === 'function') window.renderCart();
  }

  /* ======================================================================
   * WISHLIST
   * ==================================================================== */
  function toggleWishlist(item) {
    var wl = getWishlist();
    var key = lineKey(item);
    var stored = wl.find(function (i) { return lineKey(i) === key; });

    if (stored) {
      wl = wl.filter(function (i) { return lineKey(i) !== key; });
      saveWishlist(wl);
      pushEcommerce('remove_from_wishlist', {
        currency: CURRENCY,
        value: round2(stored.price * stored.quantity),
        items: [stored]
      });
      return false; // removed
    }
    wl.push(item);
    saveWishlist(wl);

    pushEcommerce('add_to_wishlist', {
      currency: CURRENCY,
      value: round2(item.price * item.quantity),
      items: [item]
    });
    return true; // added
  }

  /* Remove a specific stored item from the wishlist, firing the event. */
  function removeFromWishlist(item) {
    var wl = getWishlist();
    var key = lineKey(item);
    var stored = wl.find(function (i) { return lineKey(i) === key; });
    if (!stored) return;

    wl = wl.filter(function (i) { return lineKey(i) !== key; });
    saveWishlist(wl);
    pushEcommerce('remove_from_wishlist', {
      currency: CURRENCY,
      value: round2(stored.price * stored.quantity),
      items: [stored]
    });
  }

  /* ======================================================================
   * SELECT ITEM — fired when a product card is clicked in a list.
   * We push, then let the navigation proceed.
   * ==================================================================== */
  function selectItem(item, listId, listName) {
    var payload = Object.assign({}, item);
    if (listId)   payload.item_list_id   = listId;
    if (listName) payload.item_list_name = listName;
    pushEcommerce('select_item', {
      item_list_id: listId,
      item_list_name: listName,
      items: [payload]
    });
  }

  /* ======================================================================
   * Wire up the DOM once loaded
   * ==================================================================== */
  /* ----------------------------------------------------------------------
   * Bind-once guard.
   *
   * initPage() now runs on EVERY client-side route change, not just once.
   * Elements inside <main> are rebuilt on each navigation so they are always
   * fresh, but persistent chrome (the footer newsletter form) would collect a
   * new listener every time without this.
   * -------------------------------------------------------------------- */
  function bindOnce(el) {
    if (el.__snsBound) return false;
    el.__snsBound = true;
    return true;
  }

  /* ======================================================================
   * PAGE INIT — runs on first load and again after every SPA navigation.
   * ==================================================================== */
  function initPage() {
    refreshBadges();

    /* --- Flush page-load view_* events queued in the <head> --------- */
    // These wait for GTM exactly like interaction events. Order preserved.
    var pending = window.SNS_PENDING_EVENTS || [];
    pending.forEach(function (evt) {
      runWhenGtmReady(function () {
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push(evt);
      });
    });
    window.SNS_PENDING_EVENTS = [];

    /* --- Add to cart buttons ---------------------------------------- */
    document.querySelectorAll('[data-add-to-cart]').forEach(function (btn) {
      if (!bindOnce(btn)) return;
      btn.addEventListener('click', function () {
        var item = itemFromEl(btn);
        if (!item) return;

        // Product page may carry a variant selector + quantity input.
        var scope = btn.closest('[data-product-scope]') || document;
        var variantSel = scope.querySelector('[data-variant-select]');
        var qtyInput   = scope.querySelector('[data-qty-input]');

        if (variantSel && variantSel.value) {
          item.item_variant = variantSel.options[variantSel.selectedIndex].text;
        }
        if (qtyInput) {
          item.quantity = Math.max(1, parseInt(qtyInput.value, 10) || 1);
        }
        addToCart(item);
        flash(btn, 'Added \u2713');
      });
    });

    /* --- Add / remove wishlist buttons ------------------------------ */
    document.querySelectorAll('[data-toggle-wishlist]').forEach(function (btn) {
      var scope = btn.closest('[data-product-scope]');
      var variantSel = scope && scope.querySelector('[data-variant-select]');

      // Build the item for the *currently selected* variant.
      function currentItem() {
        var it = itemFromEl(btn);
        if (it && variantSel && variantSel.value) {
          it.item_variant = variantSel.options[variantSel.selectedIndex].text;
        }
        return it;
      }
      // Reflect saved state for this exact variant.
      function reflect() {
        var it = currentItem();
        var active = it && getWishlist().some(function (i) { return lineKey(i) === lineKey(it); });
        btn.classList.toggle('is-active', !!active);
      }

      reflect();
      if (!bindOnce(btn)) return;
      if (variantSel) variantSel.addEventListener('change', reflect);

      btn.addEventListener('click', function () {
        var it = currentItem();
        if (!it) return;
        var added = toggleWishlist(it);
        btn.classList.toggle('is-active', added);
        flash(btn, added ? 'Saved \u2665' : 'Removed');
      });
    });

    /* --- Product card clicks (select_item) -------------------------- */
    document.querySelectorAll('[data-select-item]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        var item = itemFromEl(link);
        if (!item) return;
        selectItem(item, link.getAttribute('data-list-id'), link.getAttribute('data-list-name'));
      });
    });

    /* --- Promotion clicks (select_promotion) ------------------------ */
    document.querySelectorAll('[data-select-promotion]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        var promo;
        try { promo = JSON.parse(link.getAttribute('data-promotion')); }
        catch (e) { return; }
        pushEcommerce('select_promotion', { items: [promo] });
      });
    });

    /* --- Begin checkout button -------------------------------------- */
    document.querySelectorAll('[data-begin-checkout]').forEach(function (btn) {
      if (!bindOnce(btn)) return;
      btn.addEventListener('click', function (e) {
        var cart = getCart();
        if (!cart.length) return;
        pushEcommerce('begin_checkout', {
          currency: CURRENCY,
          value: cartValue(cart),
          items: cart
        });
      });
    });
  }

  SNS_READY(initPage);

  /* Small visual confirmation on a button press. */
  function flash(btn, text) {
    var original = btn.getAttribute('data-label') || btn.textContent;
    if (!btn.getAttribute('data-label')) btn.setAttribute('data-label', original.trim());
    btn.textContent = text;
    setTimeout(function () { btn.textContent = btn.getAttribute('data-label'); }, 1200);
  }

  /* ----------------------------------------------------------------------
   * Public API used by individual page scripts (cart / checkout / thank-you)
   * -------------------------------------------------------------------- */
  window.SNS = {
    getCart: getCart,
    saveCart: saveCart,
    clearCart: function () { write(CART_KEY, []); refreshBadges(); },
    getWishlist: getWishlist,
    saveWishlist: saveWishlist,
    removeFromCart: removeFromCart,
    changeQty: changeQty,
    toggleWishlist: toggleWishlist,
    removeFromWishlist: removeFromWishlist,
    lineKey: lineKey,
    cartValue: cartValue,
    round2: round2,
    pushEcommerce: pushEcommerce,
    pushEvent: pushEvent,
    initPage: initPage,
    currency: CURRENCY,
    refreshBadges: refreshBadges
  };
})();
