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

  /* ----------------------------------------------------------------------
   * SPA page_view
   *
   * The GTM container + GA4 config tag load exactly once, on the first page.
   * On every client-side route change nothing re-fires a page_view, so the
   * router calls this after each swap to emit one with the *new* URL/title.
   * (On the very first load the GA4 config tag already sends page_view, so
   * this is only ever called by router.js — never on the landing page.)
   * -------------------------------------------------------------------- */
  function pushPageView() {
    runWhenGtmReady(function () {
      window.dataLayer.push({
        event: 'page_view',
        page_location: location.href,
        page_path: location.pathname + location.search,
        page_title: document.title
      });
    });
  }

  function cartValue(cart) {
    return round2(cart.reduce(function (sum, i) {
      return sum + (i.price * i.quantity);
    }, 0));
  }
  function round2(n) { return Math.round(n * 100) / 100; }

  /* ----------------------------------------------------------------------
   * Coupons (advanced ecommerce)
   *
   * A single applied coupon lives in localStorage. It reduces the order value
   * and rides through the whole funnel as the GA4 `coupon` param. The coupon
   * table is injected by the server (window.SNS_COUPONS).
   * -------------------------------------------------------------------- */
  var COUPON_KEY = 'sns_coupon';
  function getCoupon() {
    try { return JSON.parse(localStorage.getItem(COUPON_KEY)) || null; }
    catch (e) { return null; }
  }
  function setCoupon(c) {
    if (c) localStorage.setItem(COUPON_KEY, JSON.stringify(c));
    else localStorage.removeItem(COUPON_KEY);
  }
  function findCoupon(code) {
    var all = window.SNS_COUPONS || {};
    return all[(code || '').trim().toUpperCase()] || null;
  }
  function couponDiscount(subtotal, coupon) {
    coupon = coupon || getCoupon();
    if (!coupon) return 0;
    var d = coupon.type === 'percent' ? subtotal * (coupon.amount / 100) : coupon.amount;
    return round2(Math.min(d, subtotal));
  }
  /* One source of truth for order money used across the funnel pages. */
  function orderTotals(cart) {
    cart = cart || getCart();
    var subtotal = cartValue(cart);
    var coupon   = getCoupon();
    var discount = couponDiscount(subtotal, coupon);
    return {
      subtotal:   subtotal,
      discount:   discount,
      total:      round2(subtotal - discount),
      coupon:     coupon,
      couponCode: coupon ? coupon.code : null
    };
  }

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

  /* ----------------------------------------------------------------------
   * Item configurator
   *
   * A product scope ([data-product-scope]) may carry a primary variant
   * ([data-variant-select]) plus any number of configurator option groups
   * ([data-config-option]). Each <option> can declare a data-price delta.
   * We fold every chosen option into a single human-readable item_variant
   * and adjust the item price by the sum of the deltas, so the configured
   * price + variant ride through the whole funnel via the dataLayer.
   * -------------------------------------------------------------------- */
  function applyConfiguration(item, scope) {
    if (!item || !scope || !scope.querySelector) return item;
    var parts = [];
    var delta = 0;

    var variantSel = scope.querySelector('[data-variant-select]');
    if (variantSel && variantSel.value) {
      parts.push(variantSel.options[variantSel.selectedIndex].text);
    }
    scope.querySelectorAll('[data-config-option]').forEach(function (sel) {
      var opt = sel.options[sel.selectedIndex];
      if (!opt) return;
      parts.push(opt.getAttribute('data-name') || opt.text);
      delta += parseFloat(opt.getAttribute('data-price')) || 0;
    });

    if (parts.length) item.item_variant = parts.join(' / ');
    if (delta) item.price = round2(item.price + delta);
    return item;
  }

  /* Live-update the configured unit price shown on the product page, and
   * keep the wishlist heart in sync as options change. Wired once per scope. */
  function wireConfigurators() {
    document.querySelectorAll('[data-product-scope]').forEach(function (scope) {
      var priceEl = scope.querySelector('[data-live-price]');
      var addBtn  = scope.querySelector('[data-add-to-cart]');
      if (!priceEl || !addBtn || !bindOnce(scope)) return;

      var base    = (itemFromEl(addBtn) || {}).price || 0;
      var selects = scope.querySelectorAll('[data-variant-select],[data-config-option]');

      function update() {
        var delta = 0;
        scope.querySelectorAll('[data-config-option]').forEach(function (sel) {
          var opt = sel.options[sel.selectedIndex];
          if (opt) delta += parseFloat(opt.getAttribute('data-price')) || 0;
        });
        priceEl.textContent = '$' + round2(base + delta).toFixed(2);
      }
      selects.forEach(function (sel) { sel.addEventListener('change', update); });
      update();
    });
  }

  /* ----------------------------------------------------------------------
   * Recently viewed (advanced ecommerce)
   *
   * Product pages record themselves; any [data-recently-viewed] container is
   * then filled with cards and fires a view_item_list for the "Recently
   * Viewed" list. Cards reuse the standard data-select-item / data-add-to-cart
   * markup, so the normal binders below wire them (render runs first).
   * -------------------------------------------------------------------- */
  var RECENT_KEY = 'sns_recent';
  function getRecent() { return read(RECENT_KEY); }
  function recordRecent(item) {
    if (!item || !item.item_id) return;
    var list = getRecent().filter(function (i) { return i.item_id !== item.item_id; });
    list.unshift(item);
    write(RECENT_KEY, list.slice(0, 8));
  }
  function recordCurrentProduct() {
    var scope = document.querySelector('[data-recent-item]');
    if (!scope) return;
    try { recordRecent(JSON.parse(scope.getAttribute('data-recent-item'))); }
    catch (e) {}
  }
  function renderRecentlyViewed() {
    var host = document.querySelector('[data-recently-viewed]');
    if (!host) return;
    var exclude = host.getAttribute('data-exclude') || '';
    var items = getRecent().filter(function (i) { return i.item_id !== exclude; }).slice(0, 4);

    if (!items.length) { host.style.display = 'none'; return; }
    host.style.display = '';

    var listId = 'recently_viewed', listName = 'Recently Viewed';
    var icon = window.STORE_ICON || '🛍️';
    host.innerHTML =
      '<div class="section-head"><h2>Recently viewed</h2></div>' +
      '<div class="product-grid">' + items.map(function (it, i) {
        var url  = '/product.php?id=' + encodeURIComponent(it.item_id);
        var data = JSON.stringify(Object.assign({}, it, {
          index: i + 1, item_list_id: listId, item_list_name: listName
        })).replace(/"/g, '&quot;');
        return '<article class="product-card">' +
          '<a class="prod-link" href="' + url + '" data-select-item data-item="' + data +
            '" data-list-id="' + listId + '" data-list-name="' + listName + '">' +
            '<div class="product-photo" style="height:180px;background:var(--cream);border-color:var(--line);">' +
              '<span class="product-photo__icon">' + icon + '</span></div></a>' +
          '<div class="product-card__body">' +
            '<span class="product-card__cat">' + (it.item_category || '') + '</span>' +
            '<h3 class="product-card__name"><a href="' + url + '" data-select-item data-item="' + data +
              '" data-list-id="' + listId + '" data-list-name="' + listName + '">' + it.item_name + '</a></h3>' +
            '<div class="product-card__meta"><span class="price">$' + round2(it.price).toFixed(2) + '</span></div>' +
          '</div>' +
          '<div class="product-card__actions" data-product-scope>' +
            '<button class="btn" data-add-to-cart data-item="' + data + '">Add to cart</button>' +
            '<button class="wishlist-btn" data-toggle-wishlist data-item="' + data + '" aria-label="Add to wishlist">♥</button>' +
          '</div></article>';
      }).join('') + '</div>';

    pushEcommerce('view_item_list', {
      item_list_id: listId,
      item_list_name: listName,
      items: items.map(function (it, i) {
        return Object.assign({}, it, { index: i + 1, item_list_id: listId, item_list_name: listName });
      })
    });
  }

  /* ----------------------------------------------------------------------
   * Header search — SPA-aware submit to /search.php (view_search_results
   * fires on the search page itself, from its queued page dataLayer).
   * -------------------------------------------------------------------- */
  function wireSearch() {
    document.querySelectorAll('[data-search-form]').forEach(function (form) {
      if (!bindOnce(form)) return;
      form.addEventListener('submit', function (e) {
        var input = form.querySelector('input[name="q"]');
        var q = (input && input.value || '').trim();
        if (!q) { e.preventDefault(); return; }
        var url = '/search.php?q=' + encodeURIComponent(q);
        if (window.SNS_ROUTER && typeof window.SNS_ROUTER.navigate === 'function') {
          e.preventDefault();
          window.SNS_ROUTER.navigate(url, true);
        }
        // else: let the native GET submit happen (full page load).
      });
    });
  }

  /* ----------------------------------------------------------------------
   * Document downloads — file_download
   *
   * GA4's recommended event for a file link. Enhanced Measurement fires this
   * automatically for common extensions, but only ever with the link params;
   * pushing it ourselves lets us carry the document type and, on a product
   * page, which product the document belongs to.
   *
   * The link keeps its native `download` behaviour — we do not preventDefault,
   * so the browser saves the file while the event goes to the dataLayer. The
   * SPA router ignores [download] links, so no navigation is intercepted.
   * -------------------------------------------------------------------- */
  function wireDownloads() {
    document.querySelectorAll('[data-download]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        var file = link.getAttribute('data-file-name') || '';
        var dot  = file.lastIndexOf('.');

        var payload = {
          file_name:      file,
          file_extension: dot > -1 ? file.slice(dot + 1) : '',
          file_type:      link.getAttribute('data-doc-type') || '',
          document_id:    link.getAttribute('data-doc-id') || '',
          link_text:      link.getAttribute('data-doc-label') || link.textContent.trim(),
          link_url:       link.href,
          link_domain:    location.hostname
        };

        // Present only when the document hangs off a product page.
        var itemId = link.getAttribute('data-item-id');
        if (itemId) {
          payload.item_id   = itemId;
          payload.item_name = link.getAttribute('data-item-name') || '';
        }

        pushEvent('file_download', payload);
      });
    });
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
      items: [item]
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
    // Feed the user's "last category wishlisted" stat (user_data).
    if (window.SNS_USER && typeof window.SNS_USER.recordWishlist === 'function') {
      window.SNS_USER.recordWishlist(item);
    }
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

  /* ----------------------------------------------------------------------
   * Bind the standard product-card controls within a root element. Used by
   * initPage(document) and exposed as SNS.bindCards so dynamically-injected
   * card grids (quiz results, etc.) can be wired after the initial page init.
   * -------------------------------------------------------------------- */
  function bindProductControls(root) {
    root = root || document;

    /* Add to cart */
    root.querySelectorAll('[data-add-to-cart]').forEach(function (btn) {
      if (!bindOnce(btn)) return;
      btn.addEventListener('click', function () {
        var item = itemFromEl(btn);
        if (!item) return;

        // Product page may carry a variant selector, configurator options
        // and a quantity input — fold them all into the item.
        var scope = btn.closest('[data-product-scope]') || document;
        var qtyInput = scope.querySelector('[data-qty-input]');

        applyConfiguration(item, scope);
        if (qtyInput) {
          item.quantity = Math.max(1, parseInt(qtyInput.value, 10) || 1);
        }
        addToCart(item);
        flash(btn, 'Added ✓');
      });
    });

    /* Add / remove wishlist */
    root.querySelectorAll('[data-toggle-wishlist]').forEach(function (btn) {
      var scope = btn.closest('[data-product-scope]');

      // Build the item for the *currently selected* variant + configuration.
      function currentItem() {
        var it = itemFromEl(btn);
        return it ? applyConfiguration(it, scope) : it;
      }
      // Reflect saved state for this exact variant.
      function reflect() {
        var it = currentItem();
        var active = it && getWishlist().some(function (i) { return lineKey(i) === lineKey(it); });
        btn.classList.toggle('is-active', !!active);
      }

      reflect();
      if (!bindOnce(btn)) return;
      if (scope) {
        scope.querySelectorAll('[data-variant-select],[data-config-option]').forEach(function (sel) {
          sel.addEventListener('change', reflect);
        });
      }

      btn.addEventListener('click', function () {
        var it = currentItem();
        if (!it) return;
        var added = toggleWishlist(it);
        btn.classList.toggle('is-active', added);
        flash(btn, added ? 'Saved ♥' : 'Removed');
      });
    });

    /* Product card clicks (select_item) */
    root.querySelectorAll('[data-select-item]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        var item = itemFromEl(link);
        if (!item) return;
        selectItem(item, link.getAttribute('data-list-id'), link.getAttribute('data-list-name'));
      });
    });
  }

  /* ======================================================================
   * PAGE INIT — runs on first load and again after every SPA navigation.
   * ==================================================================== */
  function initPage() {
    refreshBadges();

    /* --- Record the current product + paint "Recently viewed" ------- *
     * Done before the binders so injected cards get wired below.       */
    recordCurrentProduct();
    renderRecentlyViewed();

    /* --- Flush page-load view_* events queued in <main> ------------- */
    // These wait for GTM exactly like interaction events. Order preserved.
    var pending = window.SNS_PENDING_EVENTS || [];
    pending.forEach(function (evt) {
      runWhenGtmReady(function () {
        window.dataLayer.push({ ecommerce: null });
        window.dataLayer.push(evt);
      });
    });
    window.SNS_PENDING_EVENTS = [];

    /* --- Product card controls (add_to_cart / wishlist / select_item) */
    bindProductControls(document);

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

    /* --- begin_checkout is owned by cart.php's renderCart() ----------
     * cart.php re-binds the checkout button on every re-render, so binding
     * it here as well would double-fire begin_checkout on the first render
     * (the long-standing bug). Leave it to the cart page. */

    /* --- Configurators, search + recently-viewed -------------------- */
    wireConfigurators();
    wireSearch();
    wireDownloads();

    /* --- Account chrome + user_data (auth.js) ----------------------- */
    if (window.SNS_USER && typeof window.SNS_USER.onPage === 'function') {
      window.SNS_USER.onPage();
    }
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
    pushPageView: pushPageView,
    initPage: initPage,
    currency: CURRENCY,
    refreshBadges: refreshBadges,
    getRecent: getRecent,
    recordRecent: recordRecent,
    applyConfiguration: applyConfiguration,
    addToCart: addToCart,
    bindCards: bindProductControls,
    getCoupon: getCoupon,
    setCoupon: setCoupon,
    findCoupon: findCoupon,
    couponDiscount: couponDiscount,
    orderTotals: orderTotals
  };
})();
