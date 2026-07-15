<?php
/**
 * Contact page — contact form + support details.
 *
 * dataLayer events (all fired from content.js, all correctly gated on GTM):
 *   - form_start    (first interaction with the form)
 *   - generate_lead (successful submit)
 *   - form_error    (validation failure)
 */
require_once __DIR__ . '/includes/functions.php';

$PAGE_TITLE = 'Contact us';
require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb">
  <a href="/index.php">Home</a> / Contact
</nav>

<h1>Get in touch</h1>
<p class="muted" style="max-width:60ch;">
  Questions about an order, a product, or something you saw on the blog?
  Send us a note and a (fictional) human will reply within one working day.
</p>

<div class="contact-layout">
  <form class="form-card" data-contact-form data-form-name="contact" novalidate>
    <div class="form-grid">
      <div class="field"><label for="c-name">Your name</label>
        <input id="c-name" name="name" placeholder="Alex Green"></div>
      <div class="field"><label for="c-email">Email</label>
        <input id="c-email" name="email" type="email" placeholder="you@example.com"></div>

      <div class="field field--full">
        <label for="c-subject">What is this about?</label>
        <select id="c-subject" name="subject">
          <?php foreach ($CONTACT['subjects'] as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field field--full">
        <label for="c-order">Order number <span class="muted">(optional)</span></label>
        <input id="c-order" name="order" placeholder="SNS-12345678-123">
      </div>

      <div class="field field--full">
        <label for="c-message">Message</label>
        <textarea id="c-message" name="message" rows="6"
                  placeholder="Tell us what you need a hand with…"></textarea>
      </div>
    </div>

    <button type="submit" class="btn btn--block" style="margin-top:18px;">Send message</button>
    <p class="muted" style="margin-top:10px;font-size:.85rem;">
      No data leaves your browser — this is a training site. Submitting pushes a
      <code>generate_lead</code> event to the dataLayer.
    </p>
  </form>

  <div class="contact-success" data-contact-success style="display:none;">
    <div class="contact-success__icon">✅</div>
    <h2>Message sent!</h2>
    <p class="muted">
      Thanks for reaching out — we'll be in touch shortly.
      A <code>generate_lead</code> event has just been pushed to the dataLayer;
      open your console and inspect <code>window.dataLayer</code> to see it.
    </p>
    <a class="btn btn--ghost" href="/index.php">Back to the shop</a>
  </div>

  <aside class="contact-aside">
    <div class="info-card">
      <h3>Support</h3>
      <p><a href="mailto:<?= htmlspecialchars($CONTACT['email']) ?>"><?= htmlspecialchars($CONTACT['email']) ?></a></p>
      <p><a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $CONTACT['phone'])) ?>"><?= htmlspecialchars($CONTACT['phone']) ?></a></p>
      <p class="muted"><?= htmlspecialchars($CONTACT['hours']) ?></p>
    </div>

    <div class="info-card">
      <h3>Head office</h3>
      <p class="muted"><?= nl2br(htmlspecialchars($CONTACT['address'])) ?></p>
    </div>

    <div class="info-card">
      <h3>Visit a store</h3>
      <p class="muted">We have <?= count($LOCATIONS) ?> shops you can walk into.</p>
      <a class="btn btn--ghost btn--block" href="/locations.php">Find a store</a>
    </div>
  </aside>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
