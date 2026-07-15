# 🧵 Thread & Stitch — GA4 / GTM Training Store

A deliberately simple **single-page app (SPA)** ecommerce website in the
**fashion niche**, built with **HTML, CSS, vanilla JS and PHP**. Everything
is **fictional dummy data** — there is **no database**. It exists so interns can
learn **GA4 ecommerce tracking** and **GTM** by watching a real `dataLayer`.

---

## What it demonstrates

Every standard GA4 ecommerce event is wired into `window.dataLayer`:

| Event | Where it fires |
|-------|----------------|
| `view_promotion`   | Homepage hero banner (on load) |
| `select_promotion` | Clicking the hero CTA |
| `view_item_list`   | Homepage, category pages, wishlist (on load) |
| `select_item`      | Clicking any product card |
| `view_item`        | Product detail page (on load) |
| `add_to_cart`      | "Add to cart" buttons / qty increase / wishlist → cart |
| `remove_from_cart` | Removing a line / reducing qty |
| `view_cart`        | Cart page (on load) |
| `add_to_wishlist`  | "Save to wishlist" buttons |
| `remove_from_wishlist` | Un-saving an item (heart toggle or wishlist ✕) |
| `begin_checkout`   | "Checkout" button on the cart |
| `add_shipping_info`| "Continue to payment" (carries `shipping_tier`) |
| `add_payment_info` | "Place order" (carries `payment_type`) |
| `purchase`         | Thank-you page (fired once, with `transaction_id`) |

Each push follows GA4 best practice: it first clears the previous ecommerce
object with `dataLayer.push({ ecommerce: null })`, then pushes the event with a
properly-shaped `items` array (`item_id`, `item_name`, `item_brand`,
`item_category`, `price`, `quantity`, `item_variant`, `index`, list info…).

**Events wait for GTM.** No ecommerce event fires until the GTM container has
actually loaded. Page-load `view_*` events are queued in the `<head>`
(`SNS_PENDING_EVENTS`) and interaction events go through a readiness gate in
`main.js`; everything is flushed — in order — only once
`window.google_tag_manager['GTM-KQNM4DRL']` exists. A ~10s fallback still fires
queued events if GTM is blocked, so the site never silently loses data.

**`item_variant`** is attached wherever a product has variants — the selected
option on add-to-cart / add-to-wishlist, the default option on `view_item`, and
it then rides through the cart into `begin_checkout`, `add_shipping_info`,
`add_payment_info` and `purchase`.

## Content pages (non-ecommerce events)

Beyond the shop funnel, the site has content pages that fire **flat, non-ecommerce**
events. These have no `ecommerce` object, so they go through `SNS.pushEvent()` in
`assets/js/content.js` rather than `SNS.pushEcommerce()` — but they still wait for
the same GTM readiness gate.

| Event | Where it fires |
|-------|----------------|
| `form_start`         | First interaction with the contact form |
| `generate_lead`      | Contact form submitted successfully |
| `form_error`         | Contact / newsletter form fails validation |
| `newsletter_signup`  | Newsletter subscribe (carries `form_placement`: footer / blog_index / article_footer) |
| `view_article_list`  | Blog index (on load) |
| `select_article`     | Clicking a blog card |
| `view_article`       | Blog post (on load) |
| `article_scroll`     | 25 / 50 / 75 / 100% read depth, each once |
| `view_location_list` | Store locator (on load) |
| `select_location`    | Clicking a store card |
| `view_location`      | Single store page (on load) |
| `get_directions`     | "Get directions" button on a store page |
| `click_to_call`      | Tapping a store phone number |

The **newsletter** form appears in the footer of every page, plus inline on the blog
index and at the foot of each article. `form_placement` tells them apart in GA4.

## ⚠️ This is now a single-page app

Navigation is handled entirely on the client by `assets/js/router.js`. Clicking an
internal link no longer loads a page: the router fetches the target URL, swaps the
contents of `<main>`, re-executes the scripts that came with it, and updates the
address bar with `history.pushState()`.

The server still renders every page in full, so **deep links and refreshes work
exactly as before** — only in-app navigation is intercepted. The checkout steps keep
their hard redirects (`window.location.href`), so those two hops still cause a real
page load.

**This changes tracking profoundly, and mostly for the worse.** The GTM container
loads exactly once, on the first page. Work out what that means for:

- the events queued in the `<head>` (`SNS_PENDING_EVENTS`) on every *other* route;
- GTM's Page View trigger and the GA4 config tag;
- the `ecommerce` object between routes;
- what GTM's built-in **History Change** trigger can and cannot do about it.

Page scripts register through `SNS_READY(fn)` rather than `DOMContentLoaded`, because
`DOMContentLoaded` now fires only once in the life of the app. `SNS.initPage()` and
`SNS_CONTENT.initPage()` re-wire the DOM after each route change.

## Pages & flow
```
index.php ─► category.php ─► product.php ─► cart.php
                                              │
                    wishlist.php ◄────────────┤
                                              ▼
             checkout-shipping.php ─► checkout-payment.php ─► thank-you.php
             (shipping info)          (payment: COD / card)   (purchase)
```

Shipping information and payment information are on **separate pages**.
**Cash on Delivery** is the default payment option (a dummy card option is
also included). Cart & wishlist state persist across page loads via
`localStorage`, so client-side navigation keeps its data.

## Project structure

```
index.php                 Homepage: promo + featured list
category.php              Category listing (view_item_list)
product.php               Product detail + variant selector (view_item)
cart.php                  Cart (view_cart, remove, qty, begin_checkout)
wishlist.php              Saved items
checkout-shipping.php     Step 1 — shipping (add_shipping_info)
checkout-payment.php      Step 2 — payment / COD (add_payment_info)
thank-you.php             Step 3 — confirmation (purchase)
contact.php               Contact form (generate_lead)
blog.php                  Blog index (The Cutting Room)
blog-post.php             Single article (view_article, article_scroll)
locations.php             Store locator (view_location_list)
location.php              Single store (view_location, get_directions)
includes/
  data.php                All dummy categories, products, variants, promos
  functions.php           Helpers + GA4 item builder + product card renderer
  header.php              <head>, dataLayer bootstrap + GTM snippet
  footer.php              Footer + main.js include
assets/
  css/style.css           Styling
  js/main.js              Cart/wishlist logic + all interaction dataLayer pushes
  js/content.js           Contact form, newsletter, blog + location events
  js/router.js            SPA router — intercepts links, swaps <main>, pushState
vercel.json               PHP runtime + routing for Vercel
```

## 🔧 Add your GTM container

Open `includes/header.php` and replace the placeholder:

```php
$GTM_ID = 'GTM-KQNM4DRL';   // ← this store's container ID
```

Then in GTM, create Data Layer variables and GA4 Event tags for each event
above. Use **GTM Preview mode** + the browser console (`window.dataLayer`) to
watch events fire as you click through the store.

## 🚀 Deploy to Vercel

This uses the community **`vercel-php`** runtime (configured in `vercel.json`).

```bash
npm i -g vercel     # if you don't have it
vercel              # from the project root, then follow the prompts
vercel --prod       # to promote to production
```

Or push the folder to a Git repo and "Import Project" in the Vercel dashboard.

> **Note on PHP + Vercel:** Vercel has no first-party PHP runtime, so this
> relies on the community `vercel-php@0.7.4` runtime. If a deploy ever fails on
> the runtime version, bump it to the latest tag from
> <https://github.com/vercel-community/php/releases> in `vercel.json`.

## Local preview

With PHP installed:

```bash
php -S localhost:8000
```

Then open <http://localhost:8000>. (No build step, no dependencies.)

---

*All brands, products, prices, reviews and addresses in this project are
invented for training purposes.*
