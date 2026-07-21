# 🧵 Thread & Stitch — GA4 / GTM Training Store

A deliberately simple **single-page app (SPA)** ecommerce website in the
**fashion niche**, built with **HTML, CSS, vanilla JS and PHP**. Everything
is **fictional dummy data** — there is **no database**. It exists so interns can
learn **GA4 ecommerce tracking** and **GTM** by watching a real `dataLayer`.

---

## Ecommerce events

| Event | Where it fires |
|-------|----------------|
| `view_promotion`   | Homepage hero banner (on load) |
| `select_promotion` | Clicking the hero CTA |
| `view_item_list`   | Homepage, category pages, wishlist, search, quiz results, recently viewed |
| `select_item`      | Clicking any product card |
| `view_item`        | Product detail page (on load) |
| `view_search_results` | Search results (carries `search_term`, `results_count`) |
| `add_to_cart`      | "Add to cart" buttons / qty increase / wishlist → cart |
| `remove_from_cart` | Removing a line / reducing qty |
| `view_cart`        | Cart page (on load) |
| `add_to_wishlist` / `remove_from_wishlist` | Wishlist buttons and the heart toggle |
| `apply_coupon`     | Redeeming a promo code on the cart |
| `begin_checkout`   | "Checkout" button on the cart |
| `add_shipping_info`| "Continue to payment" (carries `shipping_tier`) |
| `add_payment_info` | "Place order" (carries `payment_type`) |
| `purchase`         | Thank-you page (fired once, with `transaction_id`) |
| `refund`           | "Request a refund" on the account page |

Each push follows GA4 best practice: it clears the previous ecommerce object with
`dataLayer.push({ ecommerce: null })`, then pushes a properly-shaped `items`
array (`item_id`, `item_name`, `item_brand`, `item_category`, `price`,
`quantity`, `item_variant`, `index`, list info…).

**Ecommerce events wait for GTM.** Nothing fires until the container has loaded.
Page-load `view_*` events are queued in `SNS_PENDING_EVENTS` and interaction
events go through a readiness gate in `main.js`; everything flushes, in order,
once `window.google_tag_manager['GTM-KQNM4DRL']` exists. A ~10s fallback fires
queued events even if GTM is blocked.

## Content & engagement events

Flat, non-ecommerce events — no `ecommerce` object — pushed through
`SNS.pushEvent()`.

| Event | Where it fires |
|-------|----------------|
| `page_view` | Every SPA route change (see below) |
| `form_start` / `form_error` | Contact, newsletter and registration forms |
| `generate_lead` | Contact form submitted successfully |
| `newsletter_signup` | Newsletter subscribe (carries `form_placement`) |
| `view_article_list` / `select_article` / `view_article` / `article_scroll` | The Cutting Room (blog) |
| `view_location_list` / `select_location` / `view_location` / `get_directions` / `click_to_call` | Store locator |
| `quiz_start` / `quiz_complete` | The Style quiz |
| `view_document_list` | Downloads & resources page |
| `file_download` | Any document download |
| `sign_up` / `login` / `logout` | Account creation and sign in |
| `user_data` | Every page load and route change while signed in |
| `cookie_consent_update` | Cookie banner choices |

## ⚠️ This is a single-page app — and that is the main lesson

Navigation is handled entirely on the client by `assets/js/router.js`. Clicking
an internal link does not load a page: the router fetches the target URL, swaps
the contents of `<main>`, re-executes the scripts that came with it, and updates
the address bar with `history.pushState()`. **The checkout steps route through
the same router**, so the entire funnel — landing page to purchase — is *one*
document load. The server still renders every page in full, so deep links and
refreshes work exactly as before.

An SPA breaks GA4 tracking in ways that are easy to miss. Here is how this site
deals with each one — study these, because the failure modes are the point:

- **Page-load `view_*` events are emitted inside `<main>`, not `<head>`.** The
  router only swaps `<main>`, so a `<head>` script would never run again after
  the first load and `SNS_PENDING_EVENTS` would be empty on every later route.
- **`page_view` is pushed manually per route.** The GA4 config tag fires once,
  on the first load. Without `SNS.pushPageView()` every screen would be
  attributed to the *landing* page's `page_path`.
- **`ecommerce` is cleared before each push**, so values cannot leak between
  routes.
- **GTM's History Change trigger** fires on `pushState` and is available as a
  trigger — but it cannot recover an ecommerce payload on its own. The site has
  to re-push the events; that is the real lesson.

**In GTM Preview you will see one container load for the whole session,** with
every route's events inside it. That is correct SPA behaviour, but it means the
Preview group heading keeps the *landing page's* title the whole way through.
Judge the route from `page_location` / `page_path` inside each event — never
from the group heading.

Page scripts register through `SNS_READY(fn)` rather than `DOMContentLoaded`,
which now fires only once in the life of the app. `SNS.initPage()` and
`SNS_CONTENT.initPage()` re-wire the DOM after each route change.

## `user_data` and consent ordering

The `user_data` payload is pushed from the `<head>` **before** the GTM snippet,
and the same object is published as the global **`window.SNS_USER_DATA`**. Both
matter, for different reasons:

- The **event** cannot beat GTM's own bootstrap. GTM runs Consent Initialization
  → Consent Default → Initialization → Consent Update first, then replays
  messages queued before `gtm.js`. So `user_data` appears immediately after that
  sequence and before Container Loaded — the earliest an *event* can be.
- The **global** has no ordering problem. Create a GTM Variable of type
  **JavaScript Variable** with Global Variable Name `SNS_USER_DATA.email` (or
  `.user_id`, `.phone_number`, …) and it resolves for any tag, including one
  firing on the **Consent Initialization** trigger. A Data Layer Variable will
  *not* work for that, because GTM's model has no `user_data` yet.

It is `null` when nobody is signed in — register an account before testing.

Fields: `user_id`, `email`, `phone_number`, `days_since_registration`,
`last_category_ordered`, `last_category_wishlisted`, `total_revenue`,
`last_purchase_date`.

## Other things worth exploring

- **Coupons** — `THREAD10` (10% off) and `STYLE15` ($15 off). The `coupon`
  param rides from `begin_checkout` all the way to `purchase`.
- **Product configurator** — option groups fold into `item_variant` and adjust
  the price, which then travels through the whole funnel.
- **Recently viewed** — localStorage-backed, fires its own `view_item_list`.
- **Documents** — fabric & care guides, size guides and store guides, generated as real
  PDFs on request. Each download fires `file_download`.

## Pages & flow
```
index.php ─► category.php ─► product.php ─► cart.php
                                              │
                    wishlist.php ◄────────────┤
                                              ▼
             checkout-shipping.php ─► checkout-payment.php ─► thank-you.php
             (shipping info)          (payment: COD / card)   (purchase)
```

Cart, wishlist, coupon and account state persist in `localStorage`.

## Project structure

```
app.php                   Front controller — every request routes through it
index.php                 Homepage: promo + featured list
category.php              Category listing (view_item_list)
product.php               Product detail, configurator, documents (view_item)
search.php                Search results (view_search_results)
quiz.php                  Product quiz (quiz_start / quiz_complete)
cart.php                  Cart, coupons (view_cart, begin_checkout)
wishlist.php              Saved items
checkout-shipping.php     Step 1 — shipping (add_shipping_info)
checkout-payment.php      Step 2 — payment / COD (add_payment_info)
thank-you.php             Step 3 — confirmation (purchase)
account.php               Dashboard, user_data preview, refund
register.php              Account creation (sign_up)
downloads.php             Resource centre (view_document_list)
download.php              Generates and serves a document as a PDF
contact.php               Contact form (generate_lead)
blog.php / blog-post.php  The Cutting Room
locations.php / location.php  Store locator
includes/
  data.php                Categories, products, variants, promos, coupons, documents
  functions.php           Helpers + GA4 item builder + card/document renderers
  header.php              <head>, dataLayer bootstrap, user_data, GTM snippet
  footer.php              Footer + script includes
  pdf.php                 Dependency-free PDF writer
assets/
  css/style.css           Styling
  js/main.js              Cart/wishlist/coupons + interaction dataLayer pushes
  js/content.js           Contact, newsletter, blog + location events
  js/auth.js              Client-side accounts + user_data
  js/router.js            SPA router — intercepts links, swaps <main>, pushState
vercel.json               PHP runtime + routing for Vercel
```

## 🔧 Add your GTM container

Open `includes/header.php` and replace the placeholder:

```php
$GTM_ID = 'GTM-KQNM4DRL';   // ← this store's container ID
```

Then in GTM, create Data Layer Variables and GA4 Event tags for the events
above. Use **GTM Preview** + the browser console (`window.dataLayer`) to watch
events fire as you click through the store.

## 🚀 Deploy to Vercel

Uses the community **`vercel-php`** runtime (configured in `vercel.json`).

```bash
npm i -g vercel
vercel              # then follow the prompts
vercel --prod       # promote to production
```

> **Note on the function count:** Vercel's Hobby plan allows **12 Serverless
> Functions per deployment** and this site has 20 pages. `vercel.json`
> therefore builds only `app.php` and rewrites every non-asset request to it,
> which keeps the whole site at **one** function. Do not change `src` back to
> `*.php` — the deploy will be rejected.

## Local preview

```bash
php -S localhost:8000
```

Then open <http://localhost:8000>. (No build step, no dependencies.)

---

*All brands, products, prices, reviews and addresses in this project are
invented for training purposes.*
