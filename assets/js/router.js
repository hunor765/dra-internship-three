/* ==========================================================================
 * Client-side router — this store is now a single-page app.
 *
 * Internal link clicks no longer cause a page load. We fetch the target URL,
 * pull the <main> out of the response, swap it into the current document,
 * re-execute the scripts that came with it, and update the URL with
 * history.pushState().
 *
 * The server still renders every page in full, so deep links and refreshes
 * work exactly as before — only in-app navigation is intercepted.
 *
 * Every in-app hop goes through here, including the checkout steps, which
 * call SNS.go() rather than assigning location.href. Only a file download
 * ([download] links) or an outright fetch failure causes a real page load.
 * ======================================================================== */

(function () {
  'use strict';

  var MAIN = 'main.page';

  function mainEl() { return document.querySelector(MAIN); }

  /* Should the router take over this click? */
  function routable(a) {
    if (!a || !a.getAttribute) return false;
    if (a.hasAttribute('data-no-spa')) return false;
    if (a.hasAttribute('download')) return false;

    var target = a.getAttribute('target');
    if (target && target !== '_self') return false;

    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#') return false;

    // Only same-origin http(s) URLs — skips mailto:, tel:, external maps links.
    var url;
    try { url = new URL(a.href, location.href); } catch (e) { return false; }
    if (url.origin !== location.origin) return false;
    if (url.protocol !== 'http:' && url.protocol !== 'https:') return false;

    return true;
  }

  /* Re-run <script> tags that arrived with the new markup.
   * innerHTML never executes scripts, so we clone each one into a fresh
   * element. Page scripts register their work through SNS_READY, which now
   * runs them immediately (document.readyState is already 'complete'). */
  function executeScripts(container) {
    container.querySelectorAll('script').forEach(function (old) {
      var s = document.createElement('script');
      for (var i = 0; i < old.attributes.length; i++) {
        s.setAttribute(old.attributes[i].name, old.attributes[i].value);
      }
      s.text = old.textContent;
      old.parentNode.replaceChild(s, old);
    });
  }

  function navigate(url, addToHistory) {
    var current = mainEl();
    if (!current) { location.href = url; return; }

    current.setAttribute('aria-busy', 'true');

    fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'spa' } })
      .then(function (res) { return res.text().then(function (html) { return { html: html, url: res.url || url }; }); })
      .then(function (res) {
        var doc = new DOMParser().parseFromString(res.html, 'text/html');
        var next = doc.querySelector(MAIN);

        // Anything we cannot parse falls back to a real navigation.
        if (!next) { location.href = url; return; }

        if (addToHistory) {
          history.pushState({ spa: true }, '', res.url);
        }
        document.title = doc.title;

        // Page-scoped globals from the previous route must not leak into
        // the next one (renderCart is defined by cart.php's script).
        window.renderCart = null;

        current.innerHTML = next.innerHTML;
        current.removeAttribute('aria-busy');
        window.scrollTo(0, 0);

        // The GA4 config tag fired page_view only once, on the first load.
        // Emit a fresh page_view for this route BEFORE the view_* events so
        // the sequence mirrors a real multi-page load (page_view, then the
        // ecommerce/content events for the new screen).
        if (window.SNS && typeof window.SNS.pushPageView === 'function') {
          window.SNS.pushPageView();
        }

        // Order matters, and mirrors the original page-load order:
        // the page's own script runs first, then the global wiring.
        executeScripts(current);

        if (window.SNS && typeof window.SNS.initPage === 'function') {
          window.SNS.initPage();
        }
        if (window.SNS_CONTENT && typeof window.SNS_CONTENT.initPage === 'function') {
          window.SNS_CONTENT.initPage();
        }
      })
      .catch(function () {
        location.href = url; // network hiccup — let the browser handle it
      });
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented) return;
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!routable(a)) return;

    // Same URL — nothing to do, but still swallow the click.
    if (a.href === location.href) { e.preventDefault(); return; }

    e.preventDefault();
    navigate(a.href, true);
  });

  window.addEventListener('popstate', function () {
    navigate(location.href, false);
  });

  window.SNS_ROUTER = { navigate: navigate };
})();
