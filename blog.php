<?php
/**
 * Blog index — lists all articles.
 *
 * dataLayer events:
 *   - view_article_list (on load, queued in the <head> via $PAGE_DATALAYER)
 *   - select_article    (on card click, from content.js)
 *   - newsletter_signup (inline signup form, from content.js)
 */
require_once __DIR__ . '/includes/functions.php';

$PAGE_TITLE = 'Blog';

$articles = [];
$i = 1;
foreach ($BLOG_POSTS as $post) {
    $articles[] = [
        'article_slug'     => $post['slug'],
        'article_title'    => $post['title'],
        'article_category' => $post['category'],
        'article_author'   => $post['author'],
        'index'            => $i,
    ];
    $i++;
}

$PAGE_DATALAYER = [[
    'event'          => 'view_article_list',
    'list_name'      => 'Blog Index',
    'article_count'  => count($articles),
    'articles'       => $articles,
]];

require __DIR__ . '/includes/header.php';
?>

<nav class="breadcrumb">
  <a href="/index.php">Home</a> / Blog
</nav>

<h1><?= htmlspecialchars($BLOG_META['title']) ?></h1>
<p class="muted" style="max-width:65ch;"><?= htmlspecialchars($BLOG_META['intro']) ?></p>

<div class="blog-grid">
  <?php foreach ($BLOG_POSTS as $post): ?>
    <article class="blog-card">
      <a class="blog-card__media" href="/blog-post.php?slug=<?= urlencode($post['slug']) ?>"
         data-select-article
         data-article-slug="<?= htmlspecialchars($post['slug']) ?>"
         data-article-title="<?= htmlspecialchars($post['title']) ?>"
         data-article-category="<?= htmlspecialchars($post['category']) ?>"
         style="background:linear-gradient(135deg,<?= $post['color'] ?>22,<?= $post['color'] ?>55);">
        <span class="blog-card__icon"><?= $post['icon'] ?></span>
      </a>
      <div class="blog-card__body">
        <span class="blog-card__tag"><?= htmlspecialchars($post['category']) ?></span>
        <h3>
          <a href="/blog-post.php?slug=<?= urlencode($post['slug']) ?>"
             data-select-article
             data-article-slug="<?= htmlspecialchars($post['slug']) ?>"
             data-article-title="<?= htmlspecialchars($post['title']) ?>"
             data-article-category="<?= htmlspecialchars($post['category']) ?>"><?= htmlspecialchars($post['title']) ?></a>
        </h3>
        <p class="muted"><?= htmlspecialchars($post['excerpt']) ?></p>
        <div class="blog-card__meta">
          <span><?= htmlspecialchars($post['author']) ?></span>
          <span>·</span>
          <span><?= htmlspecialchars($post['date']) ?></span>
          <span>·</span>
          <span><?= (int) $post['read_time'] ?> min read</span>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<?php render_newsletter('blog_index'); ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
