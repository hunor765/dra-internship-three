<?php
/**
 * Single blog article.
 *
 * dataLayer events:
 *   - view_article     (on load, queued in the <head> via $PAGE_DATALAYER)
 *   - article_scroll   (25 / 50 / 75 / 100% read depth, from content.js)
 *   - newsletter_signup(inline signup, from content.js)
 *   - select_article   (on the "read next" cards)
 */
require_once __DIR__ . '/includes/functions.php';

$slug = isset($_GET['slug']) ? (string) $_GET['slug'] : '';
$post = $BLOG_POSTS[$slug] ?? null;

if (!$post) {
    header('Location: /blog.php');
    exit;
}

$PAGE_TITLE = $post['title'];

$PAGE_DATALAYER = [[
    'event'            => 'view_article',
    'article_slug'     => $post['slug'],
    'article_title'    => $post['title'],
    'article_category' => $post['category'],
    'article_author'   => $post['author'],
    'article_date'     => $post['date'],
    'read_time'        => (int) $post['read_time'],
]];

require __DIR__ . '/includes/header.php';

// "Read next" — the other articles.
$others = array_values(array_filter($BLOG_POSTS, fn($p) => $p['slug'] !== $post['slug']));
$others = array_slice($others, 0, 2);
?>

<nav class="breadcrumb">
  <a href="/index.php">Home</a> /
  <a href="/blog.php">Blog</a> /
  <?= htmlspecialchars($post['title']) ?>
</nav>

<article class="article"
         data-article-body
         data-article-slug="<?= htmlspecialchars($post['slug']) ?>"
         data-article-title="<?= htmlspecialchars($post['title']) ?>">

  <div class="article__hero" style="background:linear-gradient(135deg,<?= $post['color'] ?>22,<?= $post['color'] ?>55);">
    <span class="article__hero-icon"><?= $post['icon'] ?></span>
  </div>

  <span class="blog-card__tag"><?= htmlspecialchars($post['category']) ?></span>
  <h1><?= htmlspecialchars($post['title']) ?></h1>

  <div class="article__meta">
    <span>By <strong><?= htmlspecialchars($post['author']) ?></strong></span>
    <span>·</span>
    <span><?= htmlspecialchars($post['date']) ?></span>
    <span>·</span>
    <span><?= (int) $post['read_time'] ?> min read</span>
  </div>

  <div class="article__body">
    <p class="article__lede"><?= htmlspecialchars($post['excerpt']) ?></p>
    <?php foreach ($post['body'] as $block): ?>
      <?php if ($block['type'] === 'h2'): ?>
        <h2><?= htmlspecialchars($block['text']) ?></h2>
      <?php elseif ($block['type'] === 'quote'): ?>
        <blockquote><?= htmlspecialchars($block['text']) ?></blockquote>
      <?php else: ?>
        <p><?= htmlspecialchars($block['text']) ?></p>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</article>

<?php render_newsletter('article_footer'); ?>

<?php if ($others): ?>
<section style="margin-top:52px;">
  <div class="section-head"><h2>Read next</h2></div>
  <div class="blog-grid">
    <?php foreach ($others as $o): ?>
      <article class="blog-card">
        <a class="blog-card__media" href="/blog-post.php?slug=<?= urlencode($o['slug']) ?>"
           data-select-article
           data-article-slug="<?= htmlspecialchars($o['slug']) ?>"
           data-article-title="<?= htmlspecialchars($o['title']) ?>"
           data-article-category="<?= htmlspecialchars($o['category']) ?>"
           style="background:linear-gradient(135deg,<?= $o['color'] ?>22,<?= $o['color'] ?>55);">
          <span class="blog-card__icon"><?= $o['icon'] ?></span>
        </a>
        <div class="blog-card__body">
          <span class="blog-card__tag"><?= htmlspecialchars($o['category']) ?></span>
          <h3>
            <a href="/blog-post.php?slug=<?= urlencode($o['slug']) ?>"
               data-select-article
               data-article-slug="<?= htmlspecialchars($o['slug']) ?>"
               data-article-title="<?= htmlspecialchars($o['title']) ?>"
               data-article-category="<?= htmlspecialchars($o['category']) ?>"><?= htmlspecialchars($o['title']) ?></a>
          </h3>
          <p class="muted"><?= htmlspecialchars($o['excerpt']) ?></p>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
