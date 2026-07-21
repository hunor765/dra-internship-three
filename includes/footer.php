</main>

<div class="wrap">
  <?php render_newsletter('footer'); ?>
</div>

<footer class="site-footer">
  <div class="wrap site-footer__inner">
    <div>
      <strong>Thread &amp; Stitch</strong>
      <p class="muted">A 100% fictional fashion shop, built as a GA4 &amp; GTM training playground.</p>
    </div>
    <div class="site-footer__cols">
      <div>
        <h4>Shop</h4>
        <?php global $CATEGORIES; foreach ($CATEGORIES as $cat): ?>
          <a href="/category.php?id=<?= urlencode($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <div>
        <h4>Account</h4>
        <a href="/account.php">Sign in / Account</a>
        <a href="/register.php">Create account</a>
        <a href="/wishlist.php">Wishlist</a>
        <a href="/cart.php">Cart</a>
      </div>
      <div>
        <h4>Company</h4>
        <a href="/blog.php">Blog</a>
        <a href="/locations.php">Store locations</a>
        <a href="/contact.php">Contact us</a>
      </div>
      <div>
        <h4>Learning</h4>
        <a href="/index.php">All GA4 events</a>
        <span class="muted">Open the console &amp; watch <code>dataLayer</code>.</span>
      </div>
    </div>
  </div>
</footer>

<script src="/assets/js/main.js"></script>
<script src="/assets/js/content.js"></script>
<script src="/assets/js/auth.js"></script>
<script src="/assets/js/router.js"></script>
</body>
</html>
