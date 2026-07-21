<?php
/**
 * Front controller — one Serverless Function for the whole site.
 *
 * Vercel's Hobby plan allows 12 Serverless Functions per deployment, and
 * building every root .php page as its own function put us well past that
 * ("No more than 12 Serverless Functions can be added to a Deployment").
 * vercel.json now builds this file alone and rewrites every request to it;
 * the real page files ship alongside it via the builder's includeFiles, and
 * we require the right one here.
 *
 * URLs do not change — /product.php?id=… is still /product.php?id=… — so the
 * pages, the SPA router in assets/js/router.js and the GTM setup are all
 * unaware this exists.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

/* Local dev: `php -S localhost:8000 app.php` uses this as a router script.
   Let real static files (CSS, JS) serve themselves. Ignored on Vercel, where
   assets are served by @vercel/static before any rewrite is applied. */
if (PHP_SAPI === 'cli-server'
    && $path !== '/'
    && substr($path, -4) !== '.php'
    && is_file(__DIR__ . $path)) {
    return false;
}

// basename() strips any directory component, so /../../etc/passwd cannot escape.
$page = basename($path);
if ($page === '' || $page === 'app.php') {
    $page = 'index.php';
}

// Only ever serve a .php page that really sits in the project root. Anything
// else (a stray path, an attempt at includes/data.php) falls through to a 404.
$target = __DIR__ . '/' . $page;
if (substr($page, -4) !== '.php' || !is_file($target)) {
    http_response_code(404);
    $target = __DIR__ . '/index.php';
}

require $target;
