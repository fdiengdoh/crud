<?php
// public/index.php
/**
 * This file serves as the public-facing router and caching layer for the application.
 * It is optimized for performance by prioritizing cache lookups and minimizing database queries.
 */

declare(strict_types=1);

// Set content type header early
header('Content-type: text/html; charset=utf-8');

// Global initialization - this should be minimal and fast.
require_once __DIR__ . '/../init.php'; //init.php should be optimized for speed since it's included in every request.

use App\Helpers\Link;
use App\Utils\Cache;
use App\Controllers\PostController;
use App\Controllers\CategoryController;
use Uri\Rfc3986\Uri; // PHP 8.5 Built-in URI support

// Instantiate the Link helper and define routes.
$link = new Link();
$link->routes = [
    ''                          => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php', 'cache' => true],
    '/'                         => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php', 'cache' => true],

    '/404.html'                 => ['url' => BASE_URL  . '/404.html', 'file' => APP_DIR . '/404.php', 'cache' => true],
    '/ajax-handler'             => ['url' => BASE_URL  . '/ajax-handler', 'file' => APP_DIR . '/users/ajax_handler.php', 'cache' => false],
    '/report-comment'           => ['url' => BASE_URL . '/report-comment', 'file' => APP_DIR . '/report-comment.php', 'cache' => false],
    '/subscriber'               => ['url' => BASE_URL . '/subscriber', 'file' => APP_DIR . '/subscriber.php', 'cache' => false],
    '/sitemap.xml'              => ['url' => BASE_URL  . '/sitemap.xml', 'file' => APP_DIR . '/sitemap.php', 'cache' => true],

    // Add other static public routes here if needed, see sample login.index.php file
];

// --- CRITICAL OPTIMIZATION: CACHE SLUGS FROM THE DATABASE ---
$config = require CACHE_CONFIG; // Load cache configuration
$cache = new Cache($config);
$slugsCacheKey = 'all_post_slugs';
$categorySlugsCacheKey = 'all_category_slugs';

// Initialize as empty arrays to guarantee the type for in_array() later
$allowedPostSlugs = [];
$allowedCategorySlugs = [];

// Get the PostController and CategoryController instances (needed for database calls)
$postController = new PostController();
$categoryController = new CategoryController();


// --- Process Post Slugs ---
$cachedPostSlugsData = $cache->getCache($slugsCacheKey); // Returns string (JSON) or false

if (is_string($cachedPostSlugsData)) { // Cache hit and data is a string
    $decodedSlugs = json_decode($cachedPostSlugsData, true, flags: JSON_THROW_ON_ERROR);
    if (is_array($decodedSlugs)) { // Successfully decoded to an array
        $allowedPostSlugs = $decodedSlugs;
    }
} else {
    // Cache miss or corrupted data
    try {
        $fetchedSlugs = $postController->getAllSlugs(); // Get from DB
        
        // Ensure getAllSlugs always returns an array, even if empty
        $fetchedSlugs = match (true) {
            is_array($fetchedSlugs) => $fetchedSlugs,
            default => (error_log("PostController::getAllSlugs() did not return an array. Defaulting to empty array for key: {$slugsCacheKey}."), [])
        };
        
        $allowedPostSlugs = $fetchedSlugs;
        $cache->storeCache($slugsCacheKey, json_encode($allowedPostSlugs, flags: JSON_THROW_ON_ERROR));
    } catch (\JsonException $e) {
        error_log("JSON Error processing post slugs: " . $e->getMessage());
        $allowedPostSlugs = [];
    }
}


// --- Process Category Slugs ---
$cachedCategorySlugsData = $cache->getCache($categorySlugsCacheKey); // Returns string (JSON) or false

if (is_string($cachedCategorySlugsData)) { // Cache hit and data is a string
    try {
        $decodedCategorySlugs = json_decode($cachedCategorySlugsData, true, flags: JSON_THROW_ON_ERROR);
        if (is_array($decodedCategorySlugs)) { // Successfully decoded to an array
            $allowedCategorySlugs = $decodedCategorySlugs;
        }
    } catch (\JsonException $e) {
        error_log("JSON Error processing category slugs: " . $e->getMessage());
    }
} else {
    // Cache miss
    try {
        $fetchedCategorySlugs = $categoryController->getAllCategorySlugs(); // Get from DB
        
        $fetchedCategorySlugs = match (true) {
            is_array($fetchedCategorySlugs) => $fetchedCategorySlugs,
            default => (error_log("CategoryController::getAllCategorySlugs() did not return an array. Defaulting to empty array for key: {$categorySlugsCacheKey}."), [])
        };
        
        $allowedCategorySlugs = $fetchedCategorySlugs;
        $cache->storeCache($categorySlugsCacheKey, json_encode($allowedCategorySlugs, flags: JSON_THROW_ON_ERROR));
    } catch (\JsonException $e) {
        error_log("JSON Error storing category slugs: " . $e->getMessage());
        $allowedCategorySlugs = [];
    }
}
// --- END OF SLUG CACHING OPTIMIZATION ---


// --- NEW OPTIMIZATION: CACHE POPULAR AND RECENT POSTS ---
$popularPostsCacheKey = 'popular_posts_list';
$recentPostsCacheKey = 'recent_posts_list';

$popularPosts = [];
$recentPosts = [];

// --- Process Popular Posts List ---
$cachedPopularPostsData = $cache->getCache($popularPostsCacheKey);

if (is_string($cachedPopularPostsData)) {
    try {
        $decodedPopularPosts = json_decode($cachedPopularPostsData, true, flags: JSON_THROW_ON_ERROR);
        if (is_array($decodedPopularPosts)) {
            $popularPosts = $decodedPopularPosts;
        }
    } catch (\JsonException $e) {
        error_log("JSON Error processing popular posts: " . $e->getMessage());
    }
} else {
    try {
        $fetchedPopularPosts = $postController->getPopularPosts(POPULAR_POST);
        
        $fetchedPopularPosts = match (true) {
            is_array($fetchedPopularPosts) => $fetchedPopularPosts,
            default => (error_log("PostController::getPopularPosts() did not return an array. Defaulting to empty array."), [])
        };
        
        $popularPosts = $fetchedPopularPosts;
        $cache->storeCache($popularPostsCacheKey, json_encode($popularPosts, flags: JSON_THROW_ON_ERROR));
    } catch (\JsonException $e) {
        error_log("JSON Error storing popular posts: " . $e->getMessage());
        $popularPosts = [];
    }
}

// --- Process Recent Posts List ---
$cachedRecentPostsData = $cache->getCache($recentPostsCacheKey);

if (is_string($cachedRecentPostsData)) {
    try {
        $decodedRecentPosts = json_decode($cachedRecentPostsData, true, flags: JSON_THROW_ON_ERROR);
        if (is_array($decodedRecentPosts)) {
            $recentPosts = $decodedRecentPosts;
        }
    } catch (\JsonException $e) {
        error_log("JSON Error processing recent posts: " . $e->getMessage());
    }
} else {
    try {
        $fetchedRecentPosts = $postController->getRecentPosts(RECENT_POST);
        
        $fetchedRecentPosts = match (true) {
            is_array($fetchedRecentPosts) => $fetchedRecentPosts,
            default => (error_log("PostController::getRecentPosts() did not return an array. Defaulting to empty array."), [])
        };
        
        $recentPosts = $fetchedRecentPosts;
        $cache->storeCache($recentPostsCacheKey, json_encode($recentPosts, flags: JSON_THROW_ON_ERROR));
    } catch (\JsonException $e) {
        error_log("JSON Error storing recent posts: " . $e->getMessage());
        $recentPosts = [];
    }
}
// --- END OF POPULAR/RECENT POST CACHING OPTIMIZATION ---

// Initialize script variables for views
$TScripts = ''; // additional top scripts
$BScripts = ''; // additional bottom scripts

// Get the current URL path and clean it once
// Using PHP 8.5's improved URI handling
$uri = preg_replace('#/+#', '/', $_SERVER['REQUEST_URI'] ?? '');
$uriObject = Uri::createFromString($uri);
$requestUri = $uriObject->getPath() ?? '';

if ($requestUri !== '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Normalize root request URI for consistent lookup
if ($requestUri === '') {
    $requestUri = '/';
}

// --- Route Matching Logic ---
$routeFile = null;
$cacheAllowed = false;
$cacheKey = $requestUri;

// 1. Check for Static Routes (fastest lookup via array key)
if (isset($link->routes[$requestUri])) {
    $routeData = $link->routes[$requestUri];
    $routeFile = $routeData['file'];
    $cacheAllowed = $routeData['cache'];
}
// 2. List post by author
elseif (str_starts_with($requestUri, '/profile/')) {
    $username = substr($requestUri, strlen('/profile/'));
    $_GET['username'] = $username;
    $routeFile = APP_DIR . '/users/profile.php';
}
// 3. Check for Dynamic Category Routes
elseif (str_starts_with($requestUri, '/search/label/')) {
    $parts = explode('/', $requestUri);
    // Ensure the URL has at least 4 parts: /search/label/{slug}
    if (count($parts) >= 4) {
        $categorySlug = $parts[3];
        $page = isset($parts[4]) && is_numeric($parts[4]) ? (int)$parts[4] : 1;

        if ($categorySlug === 'all' || in_array($categorySlug, $allowedCategorySlugs, strict: true)) {
            $_GET['slug'] = $categorySlug;
            $_GET['page'] = $page;
            $routeFile = APP_DIR . '/category.php';
            $cacheAllowed = true;
            $cacheKey = '/search/label/' . $categorySlug . '/' . $page;
        }
    }
}
// 4. Check for Dynamic Single Post Slugs
else {
    $slug = ltrim($requestUri, '/');
    if (in_array($slug, $allowedPostSlugs, strict: true)) {
        $_GET['slug'] = $slug;
        $routeFile = APP_DIR . '/single-post.php';
        $cacheAllowed = true;
    }
}

// If no valid route was found, serve the 404 page directly.
if (!$routeFile) {
    $routeFile = APP_DIR . '/404.php';
    $cacheAllowed = true;
    $cacheKey = '/404.html';
    http_response_code(404);
}

// --- Caching and Output Buffering ---
$forceRefresh = isset($_GET['refresh']) || (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'no-cache');

// Serve from cache only for GET requests when caching is allowed and the cache exists.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$forceRefresh && $cacheAllowed && $cache->isCached($cacheKey)) {
    if (preg_match('/\.xml$/', $cacheKey)) {
        header("Content-Type: application/xml; charset=utf-8");
    }
    echo $cache->getCache($cacheKey);
    exit;
}

// If not served from cache, start output buffering to capture the page's output.
ob_start();

// Include the route file to generate the content.
require $routeFile;

// End output buffering and capture the generated output.
$output = ob_get_contents();
ob_end_flush();

// Only store the generated output in the cache for GET requests when caching is allowed.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$forceRefresh && $cacheAllowed && $config['cache_enabled']) {
    try {
        $cache->storeCache($cacheKey, $output);
    } catch (\Exception $e) {
        error_log("Cache storage error: " . $e->getMessage());
    }
}
?>
