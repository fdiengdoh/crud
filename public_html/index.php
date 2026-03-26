<?php
// public/index.php
/**
 * This file serves as the public-facing router and caching layer for the application.
 * It is optimized for performance by prioritizing cache lookups and minimizing database queries.
 */

// Set content type header early
header('Content-type: text/html; charset=utf-8');

// Global initialization - this should be minimal and fast.
 require_once __DIR__ . '/../init.php'; //init.php should be optimized for speed since it's included in every request.

use App\Helpers\Link;
use App\Utils\Cache;
use App\Controllers\PostController;
use App\Controllers\CategoryController;

// Instantiate the Link helper and define routes.
// The 'url' key is essential for your Link helper's getUrl() method.
$link = new Link();
$link->routes = [
    ''                          => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php', 'cache' => true],
    '/'                         => ['url' => BASE_URL . '/', 'file' => APP_DIR . '/home.php', 'cache' => true],

    '/404.html'                 => ['url' => BASE_URL  . '/404.html', 'file' => APP_DIR . '/404.php', 'cache' => true],
    '/ajax-handler'             => ['url' => BASE_URL  . '/ajax-handler', 'file' => APP_DIR . '/users/ajax_handler.php', 'cache' => false],
    '/report-comment'           => ['url' => BASE_URL . '/report-comment', 'file' => APP_DIR . '/report-comment.php', 'cache' => false],
    '/subscriber'               => ['url' => BASE_URL . '/subscriber', 'file' => APP_DIR . '/subscriber.php', 'cache' => false],
    '/sitemap.xml'              => ['url' => BASE_URL  . '/sitemap.xml', 'file' => APP_DIR . '/sitemap.php', 'cache' => true],

    // Add other static public routes here if any
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
    $decodedSlugs = json_decode($cachedPostSlugsData, true);
    if (is_array($decodedSlugs)) { // Successfully decoded to an array
        $allowedPostSlugs = $decodedSlugs;
    } else {
        // Log if JSON decoding failed, indicating corrupted cache or unexpected format
        error_log("Cache Error: Failed to decode JSON for post slugs. Key: {$slugsCacheKey}. Content: '{$cachedPostSlugsData}'");
        $cachedPostSlugsData = false; // Treat as cache miss to force re-fetch
    }
}

if ($cachedPostSlugsData === false) { // Cache miss or corrupted/invalid data treated as miss
    $fetchedSlugs = $postController->getAllSlugs(); // Get from DB

    // Ensure getAllSlugs always returns an array, even if empty
    if (!is_array($fetchedSlugs)) {
        error_log("PostController::getAllSlugs() did not return an array. Defaulting to empty array for key: {$slugsCacheKey}.");
        $fetchedSlugs = [];
    }
    $allowedPostSlugs = $fetchedSlugs; // Assign the fetched array

    // Store the fetched PHP array as a JSON string in cache
    $cache->storeCache($slugsCacheKey, json_encode($allowedPostSlugs));
}


// --- Process Category Slugs ---
$cachedCategorySlugsData = $cache->getCache($categorySlugsCacheKey); // Returns string (JSON) or false

if (is_string($cachedCategorySlugsData)) { // Cache hit and data is a string
    $decodedCategorySlugs = json_decode($cachedCategorySlugsData, true);
    if (is_array($decodedCategorySlugs)) { // Successfully decoded to an array
        $allowedCategorySlugs = $decodedCategorySlugs;
    } else {
        // Log if JSON decoding failed
        error_log("Cache Error: Failed to decode JSON for category slugs. Key: {$categorySlugsCacheKey}. Content: '{$cachedCategorySlugsData}'");
        $cachedCategorySlugsData = false; // Treat as cache miss
    }
}

if ($cachedCategorySlugsData === false) { // Cache miss or corrupted/invalid data treated as miss
    $fetchedCategorySlugs = $categoryController->getAllCategorySlugs(); // Get from DB
    
    // Ensure getAllCategorySlugs always returns an array
    if (!is_array($fetchedCategorySlugs)) {
        error_log("CategoryController::getAllCategorySlugs() did not return an array. Defaulting to empty array for key: {$categorySlugsCacheKey}.");
        $fetchedCategorySlugs = [];
    }
    $allowedCategorySlugs = $fetchedCategorySlugs; // Assign the fetched array

    // Store the fetched PHP array as a JSON string in cache
    $cache->storeCache($categorySlugsCacheKey, json_encode($allowedCategorySlugs));
}
// --- END OF SLUG CACHING OPTIMIZATION ---


// --- NEW OPTIMIZATION: CACHE POPULAR AND RECENT POSTS ---
// Define cache keys for these specific lists
$popularPostsCacheKey = 'popular_posts_list';
$recentPostsCacheKey = 'recent_posts_list';

// Initialize variables as empty arrays
$popularPosts = [];
$recentPosts = [];

// --- Process Popular Posts List ---
$cachedPopularPostsData = $cache->getCache($popularPostsCacheKey); // Returns string (JSON) or false

if (is_string($cachedPopularPostsData)) { // Cache hit and data is a string
    $decodedPopularPosts = json_decode($cachedPopularPostsData, true);
    if (is_array($decodedPopularPosts)) { // Successfully decoded to an array
        $popularPosts = $decodedPopularPosts;
    } else {
        error_log("Cache Error: Failed to decode JSON for popular posts. Key: {$popularPostsCacheKey}. Content: '{$cachedPopularPostsData}'");
        $cachedPopularPostsData = false; // Treat as cache miss
    }
}

if ($cachedPopularPostsData === false) { // Cache miss or corrupted/invalid data treated as miss
    // Assuming POPULAR_POST is a constant defined elsewhere (e.g., in init.php or a config file)
    // and specifies the limit for popular posts
    $fetchedPopularPosts = $postController->getPopularPosts(POPULAR_POST);

    if (!is_array($fetchedPopularPosts)) {
        error_log("PostController::getPopularPosts() did not return an array. Defaulting to empty array.");
        $fetchedPopularPosts = [];
    }
    $popularPosts = $fetchedPopularPosts; // Assign the fetched array

    // Store the fetched PHP array as a JSON string in cache
    $cache->storeCache($popularPostsCacheKey, json_encode($popularPosts));
}

// --- Process Recent Posts List ---
$cachedRecentPostsData = $cache->getCache($recentPostsCacheKey); // Returns string (JSON) or false

if (is_string($cachedRecentPostsData)) { // Cache hit and data is a string
    $decodedRecentPosts = json_decode($cachedRecentPostsData, true);
    if (is_array($decodedRecentPosts)) { // Successfully decoded to an array
        $recentPosts = $decodedRecentPosts;
    } else {
        error_log("Cache Error: Failed to decode JSON for recent posts. Key: {$recentPostsCacheKey}. Content: '{$cachedRecentPostsData}'");
        $cachedRecentPostsData = false; // Treat as cache miss
    }
}

if ($cachedRecentPostsData === false) { // Cache miss or corrupted/invalid data treated as miss
    // Assuming RECENT_POST is a constant defined elsewhere
    $fetchedRecentPosts = $postController->getRecentPosts(RECENT_POST);

    if (!is_array($fetchedRecentPosts)) {
        error_log("PostController::getRecentPosts() did not return an array. Defaulting to empty array.");
        $fetchedRecentPosts = [];
    }
    $recentPosts = $fetchedRecentPosts; // Assign the fetched array

    // Store the fetched PHP array as a JSON string in cache
    $cache->storeCache($recentPostsCacheKey, json_encode($recentPosts));
}
// --- END OF POPULAR/RECENT POST CACHING OPTIMIZATION ---

// Initialize script variables for views
$TScripts = ''; // additional top scripts
$BScripts = ''; // additional bottom scripts

// Get the current URL path and clean it once
$uri = preg_replace('#/+#', '/', $_SERVER['REQUEST_URI'] ?? ''); // Remove duplicate slashes for consistency
$requestUri = parse_url($uri, PHP_URL_PATH) ?? ''; // Ensure $requestUri is a string, default to empty if null

if ($requestUri !== '/') {
    $requestUri = rtrim($requestUri, '/');
}
// Normalize root request URI for consistent lookup
if ($requestUri === '') {
    $requestUri = '/';
}

// --- Route Matching Logic ---
$routeFile = null;      // The file to be included
$cacheAllowed = false;  // Whether the output should be cached
$cacheKey = $requestUri; // The key to use for caching this page

// 1. Check for Static Routes (fastest lookup via array key)
if (isset($link->routes[$requestUri])) {
    $routeData = $link->routes[$requestUri];
    $routeFile = $routeData['file'];
    $cacheAllowed = $routeData['cache'];
}
// 2. List post by author
elseif (strpos($requestUri, '/profile/') === 0) {
        // Public profile pages, e.g. /profile/username.
        $username = substr($requestUri, strlen('/profile/'));
        $_GET['username'] = $username;
        $routeFile = APP_DIR . '/users/profile.php';
    }
// 3. Check for Dynamic Category Routes
elseif (strpos($requestUri, '/search/label/') === 0) {
    $parts = explode('/', $requestUri);
    // Ensure the URL has at least 4 parts: /search/label/{slug}
    if (count($parts) >= 4) {
        $categorySlug = $parts[3];
        // Extract the page number from the URL
        $page = isset($parts[4]) && is_numeric($parts[4]) ? (int)$parts[4] : 1;

        // Check if the slug is valid (either 'all' or in the cached list)
        if ($categorySlug === 'all' || in_array($categorySlug, $allowedCategorySlugs)) {
            $_GET['slug'] = $categorySlug;
            $_GET['page'] = $page;
            $routeFile = APP_DIR . '/category.php';
            $cacheAllowed = true;
            // Use a consistent cache key that includes the page number
            $cacheKey = '/search/label/' . $categorySlug . '/' . $page;
        }
    }
}
// 4. Check for Dynamic Single Post Slugs
else {
    // We assume any remaining requests might be for a single post.
    $slug = ltrim($requestUri, '/');
    if (in_array($slug, $allowedPostSlugs)) {
        $_GET['slug'] = $slug;
        $routeFile = APP_DIR . '/single-post.php';
        $cacheAllowed = true;
    }
}

// 4. If no valid route was found, serve the 404 page directly.
if (!$routeFile) {
    // Include 404 file directly to avoid an extra HTTP redirect.
    $routeFile = APP_DIR . '/404.php';
    $cacheAllowed = true; // It's a good idea to cache 404 pages too.
    $cacheKey = '/404.html'; // Use a consistent key for all 404 pages.
    http_response_code(404); // Set the proper HTTP status code.
}

// --- Caching and Output Buffering ---
// Determine if we should force a cache refresh (e.g., via query parameter or HTTP header)
$forceRefresh = isset($_GET['refresh']) || (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'no-cache');

// Serve from cache only for GET requests when caching is allowed and the cache exists.
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$forceRefresh && $cacheAllowed && $cache->isCached($cacheKey)) {
    if (preg_match('/\.xml$/', $cacheKey)){
        // Set header for XML content.
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
    // This call uses your storeCache($url, $content) signature
    $cache->storeCache($cacheKey, $output);
}
?>
