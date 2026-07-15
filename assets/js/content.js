/* ==========================================================================
 * Content-page dataLayer plumbing — contact form, newsletter, blog, locations.
 *
 * These are the NON-ecommerce events. They are flat dataLayer pushes (no
 * `ecommerce` object), so they go through SNS.pushEvent rather than
 * SNS.pushEcommerce. Everything still waits for the GTM readiness gate.
 *
 * There is no backend: forms validate client-side, push their event, then
 * show an inline success state. Submissions are remembered in localStorage
 * so a returning visitor is not counted as a new lead twice.
 * ======================================================================== */

(function () {
  'use strict';

  var LEADS_KEY = 'sns_leads';
  var NEWS_KEY  = 'sns_newsletter';
  var _scrollHandler = null;

  function push(name, params) {
    if (window.SNS && typeof window.SNS.pushEvent === 'function') {
      window.SNS.pushEvent(name, params);
    }
  }

  /* initPage() re-runs on every SPA route change. Elements inside <main> are
   * rebuilt each time, but the footer newsletter form persists — without this
   * guard it would collect a fresh listener on every navigation. */
  function bindOnce(el) {
    if (el.__snsContentBound) return false;
    el.__snsContentBound = true;
    return true;
  }

  function isEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  /* Mark a form as errored/clean for a single field. */
  function setError(field, message) {
    var wrap = field.closest('.field');
    if (!wrap) return;
    wrap.classList.toggle('field--error', !!message);
    var note = wrap.querySelector('.field__error');
    if (message && !note) {
      note = document.createElement('span');
      note.className = 'field__error';
      wrap.appendChild(note);
    }
    if (note) note.textContent = message || '';
  }

  /* ======================================================================
   * CONTACT FORM
   *   form_start   — first real interaction with the form (fired once)
   *   generate_lead— successful submit (GA4 recommended event)
   *   form_error   — validation blocked the submit
   * ==================================================================== */
  function wireContactForm() {
    var form = document.querySelector('[data-contact-form]');
    if (!form || !bindOnce(form)) return;

    var started = false;
    var formName = form.getAttribute('data-form-name') || 'contact';

    form.addEventListener('input', function () {
      if (started) return;
      started = true;
      push('form_start', { form_name: formName, form_destination: location.pathname });
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      var name    = form.querySelector('[name="name"]');
      var email   = form.querySelector('[name="email"]');
      var subject = form.querySelector('[name="subject"]');
      var message = form.querySelector('[name="message"]');
      var errors  = [];

      setError(name, '');
      setError(email, '');
      setError(message, '');

      if (!name.value.trim()) {
        setError(name, 'Please tell us your name.');
        errors.push('name');
      }
      if (!isEmail(email.value.trim())) {
        setError(email, 'That email address does not look right.');
        errors.push('email');
      }
      if (message.value.trim().length < 10) {
        setError(message, 'A little more detail, please (10+ characters).');
        errors.push('message');
      }

      if (errors.length) {
        push('form_error', {
          form_name: formName,
          error_fields: errors.join(','),
          error_count: errors.length
        });
        return;
      }

      push('generate_lead', {
        form_name: formName,
        form_destination: location.pathname,
        lead_type: subject ? subject.value : 'general',
        currency: (window.SNS && window.SNS.currency) || 'USD',
        value: 0
      });

      // Remember the lead locally (no backend to post to).
      try {
        var leads = JSON.parse(localStorage.getItem(LEADS_KEY) || '[]');
        leads.push({ subject: subject ? subject.value : 'general' });
        localStorage.setItem(LEADS_KEY, JSON.stringify(leads));
      } catch (err) { /* storage full or blocked — the event still fired */ }

      var success = document.querySelector('[data-contact-success]');
      form.style.display = 'none';
      if (success) success.style.display = 'block';
    });
  }

  /* ======================================================================
   * NEWSLETTER  (footer + inline blog/location variants)
   *   newsletter_signup — on success, carries the placement so you can tell
   *                       the footer form from the in-article one in GA4.
   * ==================================================================== */
  function wireNewsletter() {
    document.querySelectorAll('[data-newsletter-form]').forEach(function (form) {
      if (!bindOnce(form)) return;
      var placement = form.getAttribute('data-placement') || 'footer';
      var input     = form.querySelector('[name="email"]');
      var done      = form.parentNode.querySelector('[data-newsletter-success]');

      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var value = input.value.trim();

        if (!isEmail(value)) {
          setError(input, 'Enter a valid email address.');
          push('form_error', {
            form_name: 'newsletter',
            form_placement: placement,
            error_fields: 'email',
            error_count: 1
          });
          return;
        }
        setError(input, '');

        var already = false;
        try {
          already = localStorage.getItem(NEWS_KEY) === '1';
          localStorage.setItem(NEWS_KEY, '1');
        } catch (err) { /* ignore */ }

        push('newsletter_signup', {
          form_name: 'newsletter',
          form_placement: placement,
          method: 'email',
          is_resubscribe: already
        });

        form.style.display = 'none';
        if (done) done.style.display = 'block';
      });
    });
  }

  /* ======================================================================
   * BLOG
   *   view_article    — article page load (queued in the <head> via PHP)
   *   select_article  — clicking a card in the blog index
   *   article_scroll  — 25 / 50 / 75 / 100% read depth, each fired once
   * ==================================================================== */
  function wireBlog() {
    document.querySelectorAll('[data-select-article]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        push('select_article', {
          article_slug:    link.getAttribute('data-article-slug'),
          article_title:   link.getAttribute('data-article-title'),
          article_category: link.getAttribute('data-article-category'),
          list_name: 'Blog Index'
        });
      });
    });

    // Drop the previous article's scroll listener before wiring a new one.
    if (_scrollHandler) {
      window.removeEventListener('scroll', _scrollHandler);
      _scrollHandler = null;
    }

    var article = document.querySelector('[data-article-body]');
    if (!article) return;

    var slug  = article.getAttribute('data-article-slug');
    var title = article.getAttribute('data-article-title');
    var marks = [25, 50, 75, 100];
    var hit   = {};

    function onScroll() {
      var rect    = article.getBoundingClientRect();
      var total   = rect.height - window.innerHeight;
      if (total <= 0) return;
      var scrolled = Math.min(100, Math.max(0, (-rect.top / total) * 100));

      marks.forEach(function (m) {
        if (scrolled >= m && !hit[m]) {
          hit[m] = true;
          push('article_scroll', {
            article_slug: slug,
            article_title: title,
            percent_scrolled: m
          });
        }
      });

      if (hit[100]) { window.removeEventListener('scroll', onScroll); _scrollHandler = null; }
    }

    _scrollHandler = onScroll;
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ======================================================================
   * LOCATIONS
   *   select_location  — clicking a store card on the index
   *   get_directions   — "Get directions" on a store page
   *   click_to_call    — tapping the store phone number
   * ==================================================================== */
  function wireLocations() {
    document.querySelectorAll('[data-select-location]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        push('select_location', {
          store_id:   link.getAttribute('data-store-id'),
          store_name: link.getAttribute('data-store-name'),
          store_city: link.getAttribute('data-store-city')
        });
      });
    });

    document.querySelectorAll('[data-get-directions]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        push('get_directions', {
          store_id:   link.getAttribute('data-store-id'),
          store_name: link.getAttribute('data-store-name'),
          store_city: link.getAttribute('data-store-city')
        });
      });
    });

    document.querySelectorAll('[data-click-to-call]').forEach(function (link) {
      if (!bindOnce(link)) return;
      link.addEventListener('click', function () {
        push('click_to_call', {
          store_id:   link.getAttribute('data-store-id'),
          store_name: link.getAttribute('data-store-name')
        });
      });
    });
  }

  /* Runs on first load and again after every SPA navigation. */
  function initPage() {
    wireContactForm();
    wireNewsletter();
    wireBlog();
    wireLocations();
  }

  SNS_READY(initPage);

  window.SNS_CONTENT = { initPage: initPage };
})();
