<?php
// src/Controllers/PostController.php
/*
 * This class will control queries related to posts, categories, users
 */
namespace App\Controllers;

use App\Database;
use PDO;

class PostController {

    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }
    
    /**
     * Retrieve posts by user (if provided) with optional filtering.
     *
     * @param array    $options   If provided.
     * @return array
     */
     public function index($options = []) {
        $params = [];
        
        // Build base query with aliases
        $sql = "SELECT p.* FROM posts p";
        
        // If a category filter is provided, add the JOIN
        if (!empty($options['category'])) {
            $sql .= " JOIN post_categories pc ON p.id = pc.post_id";
        }
        
        // Build the WHERE clause conditions
        $where = [];
        
        // Category condition
        if (!empty($options['category'])) {
            $where[] = "pc.category_id = ?";
            $params[] = $options['category'];
        }
        
        // Status condition: use provided status or default to 'published'
        if (!empty($options['status'])) {
            $where[] = "p.status = ?";
            $params[] = $options['status'];
        }
        
        // User filter if provided
        if (!empty($options['userId'])) {
            $where[] = "p.user_id = ?";
            $params[] = $options['userId'];
        }
        
        // Append the WHERE clause if there are conditions
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        // Add limit if provided and valid
        if (!empty($options['limit']) && $options['limit'] > 0) {
            $sql .= " LIMIT " . (int)$options['limit'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process posts: generate excerpt and extract first image if no feature image
        foreach ($posts as &$post) {
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['feature_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
            }
            $post['excerpt'] = $this->generateExcerpt($post['content']);
        }
        
        return $posts;
    }

    // Retrieve a single post by ID or slug, including author's username and profile details
    public function show($identifier) {
        if (is_numeric($identifier)) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.*, 
                    u.username, 
                    up.first_name, 
                    up.last_name, 
                    up.profile_picture, 
                    up.bio,
                    JSON_ARRAYAGG(JSON_OBJECT('slug', c.slug, 'name', c.name)) AS categories
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN post_categories pc ON p.id = pc.post_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.id = ?
                GROUP BY p.id
                ");
            $stmt->execute([$identifier]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT 
                    p.*, 
                    u.username, 
                    up.first_name, 
                    up.last_name, 
                    up.profile_picture, 
                    up.bio,
                    JSON_ARRAYAGG(JSON_OBJECT('slug', c.slug, 'name', c.name)) AS categories
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                LEFT JOIN post_categories pc ON p.id = pc.post_id
                LEFT JOIN categories c ON pc.category_id = c.id
                WHERE p.slug = ?
                GROUP BY p.id
                ");
            $stmt->execute([$identifier]);
        }
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post ? $post['excerpt'] = $this->generateExcerpt($post['content']) : '';
        // If there's no stored feature image, extract the first image from content.
        if ($post && empty($post['feature_image'])) {
            $extracted = $this->extractFirstImage($post['content']);
            $post['og_image'] = $extracted ? $extracted : BASE_URL . '/assets/image/default-feature.webp';
        }
        return $post;
    }

    // Retrieve the previous published post relative to the current post (by created_at)
    public function getPreviousPost($currentPostId) {
        // First, fetch the current post's created_at value
        $stmt = $this->pdo->prepare("SELECT created_at FROM posts WHERE id = ?");
        $stmt->execute([$currentPostId]);
        $currentPost = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentPost) {
            return null;
        }
        $currentCreatedAt = $currentPost['created_at'];
        // Find the most recent post that was published before the current post
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE status = 'published' AND created_at < ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$currentCreatedAt]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post ? $post['excerpt'] = $this->generateExcerpt($post['content']) : '';
        // If there's no stored feature image, extract the first image from content.
         if (isset($post['excerpt']) && empty($post['feature_image'])) {
             $extracted = $this->extractFirstImage($post['content']);
             $post['feature_image'] = $extracted ? $extracted : BASE_URL . '/assets/image/default-feature.webp';
         }
        return $post;
    }

    // Retrieve the next published post relative to the current post (by created_at)
    public function getNextPost($currentPostId) {
        // Fetch the current post's created_at value
        $stmt = $this->pdo->prepare("SELECT created_at FROM posts WHERE id = ?");
        $stmt->execute([$currentPostId]);
        $currentPost = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentPost) {
            return null;
        }
        $currentCreatedAt = $currentPost['created_at'];
        // Find the earliest post that was published after the current post
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE status = 'published' AND created_at > ? ORDER BY created_at ASC LIMIT 1");
        $stmt->execute([$currentCreatedAt]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post ? $post['excerpt'] = $this->generateExcerpt($post['content']) : '';
        if (isset($post['excerpt']) && empty($post['feature_image'])) {
             $extracted = $this->extractFirstImage($post['content']);
             $post['feature_image'] = $extracted ? $extracted : BASE_URL . '/assets/image/default-feature.webp';
         }
        return $post;
    }

    /**
     * Retrieve the most recent published posts.
     *
     * @param int $limit Number of recent posts to retrieve (default is 5).
     * @return array
     */
    public function getRecentPosts($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts 
            WHERE status = 'published'
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate an excerpt for each post if needed
        foreach ($posts as &$post) {
            // If there's no stored feature image, extract the first image from content.
             if (empty($post['feature_image'])) {
                 $extracted = $this->extractFirstImage($post['content']);
                 $post['feature_image'] = $extracted ? $extracted : BASE_URL . '/assets/image/default-feature.webp';
             }
            $post['excerpt'] = $this->generateExcerpt($post['content']);
        }
        return $posts;
    }


    /**
     * Retrieve the most popular published posts, sorted by views.
     *
     * @param int $limit Number of popular posts to retrieve.
     * @return array
     */
    public function getPopularPosts($limit = 5) {
        // Make sure $limit is an integer to prevent SQL injection
        $limit = (int)$limit;
        $query = "SELECT * FROM posts 
                WHERE status = 'published'
                ORDER BY views DESC 
                LIMIT $limit";
        $stmt = $this->pdo->query($query);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate excerpt for each post if needed
        foreach ($posts as &$post) {
            // If there's no stored feature image, extract the first image from content.
             if (empty($post['feature_image'])) {
                 $extracted = $this->extractFirstImage($post['content']);
                 $post['feature_image'] = $extracted ? $extracted : BASE_URL . '/assets/image/default-feature.webp';
             }
            $post['excerpt'] = $this->generateExcerpt($post['content']);
        }
        return $posts;
    }


    /**
     * Increment the view count for a given post.
     *
     * @param int $postId
     * @return bool
     */
    public function incrementViews($postId) {
        $stmt = $this->pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$postId]);
    }


    public function getPosts($category = 'all', $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        if ($category !== 'all') {
            $stmt = $this->pdo->prepare("
                SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM posts p
                JOIN post_categories pc ON p.id = pc.post_id
                JOIN categories c ON pc.category_id = c.id
                WHERE c.slug = ? AND p.status = 'published'
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bindParam(1, $category, PDO::PARAM_STR);
            $stmt->bindParam(2, $limit, PDO::PARAM_INT);
            $stmt->bindParam(3, $offset, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->pdo->prepare("
                SELECT * FROM posts
                WHERE status = 'published'
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bindParam(1, $limit, PDO::PARAM_INT);
            $stmt->bindParam(2, $offset, PDO::PARAM_INT);
            $stmt->execute();
        }
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($posts as &$post) {
             // If there's no stored feature image, extract the first image from content.
             if (empty($post['feature_image'])) {
                 $extracted = $this->extractFirstImage($post['content']);
                 $post['feature_image'] = $extracted ? $extracted : BASE_URL . '/assets/image/default-feature.webp';
             }
             // Generate an excerpt (assuming you have a method for that)
             $post['excerpt'] = $this->generateExcerpt($post['content']);
        }
        
        return $posts;
    }

    /**
     * Count the total number of published posts for a given category (if provided).
     *
     * @param string|null $category Slug of the category, or null for all posts.
     * @return int
     */
    public function countPosts($category = 'all') {
        if ($category !== 'all') {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total
                FROM posts p
                JOIN post_categories pc ON p.id = pc.post_id
                JOIN categories c ON pc.category_id = c.id
                WHERE c.slug = ? AND p.status = 'published'
            ");
            $stmt->execute([$category]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM posts WHERE status = 'published'");
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Generate an excerpt from rich-text content.
     *
     * @param string $content
     * @param int $length
     * @return string
     */
    private function generateExcerpt($content, $length = 150) {
        // Strip HTML tags and decode HTML entities
        $text = strip_tags(html_entity_decode($content));
    
        // Trim to desired length
        return (strlen($text) > $length) ? substr($text, 0, $length) . '...' : $text;
    }
    
    /**
     * Create a new post with default status 'draft'.
     * Allows optional manual entry for description, keywords, slug, and creation date.
     * If description is not provided, the first 100 characters of the content (stripped of HTML) are used.
     *
     * @param int    $userId
     * @param string $title
     * @param string $content
     * @param string $slug          (optional) - if not provided, auto-generated from the title.
     * @param string $featureImage  (optional) - defaults to 'assets/image/default-feature.webp'.
     * @param string $description   (optional) - if not provided, auto-generated from content.
     * @param string $keywords      (optional) - comma-separated keywords.
     * @param string $createdAt     (optional) - if not provided, defaults to current timestamp.
     * @return int Last inserted post ID.
     */
    public function create($userId, $title, $content, $slug = null, $featureImage = null, $description = '', $keywords = '', $createdAt = null, $a_script = null) {
        // Allow users to manually override the slug; otherwise, auto-generate from the title.
        if (empty($slug)) {
            $slug = $this->slugify($title);
        }
        // If description is not provided, use the first 100 characters of the content.
        if (empty($description)) {
            $description = substr(strip_tags($content), 0, 100);
        }
        // Use provided creation date or default to current datetime.
        $createdAt = $createdAt ?? date('Y-m-d H:i:s');
        
        $stmt = $this->pdo->prepare("
            INSERT INTO posts 
            (user_id, title, content, slug, feature_image, description, keywords, status, created_at, a_script)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?)
        ");
        $stmt->execute([$userId, $title, $content, $slug, $featureImage, $description, $keywords, $createdAt, $a_script]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update an existing post with additional fields.
     * Allows optional manual update of the 'created_at' date.
     *
     * @param int         $postId
     * @param string      $title
     * @param string      $content
     * @param string|null $featureImage   If provided, update the feature image; otherwise, keep the current one.
     * @param string      $slug           (Optional) Custom slug. Auto-generated from the title if empty.
     * @param string      $description    (Optional) Custom description. Auto-generated from content if empty.
     * @param string      $keywords       (Optional) Comma-separated keywords.
     * @param string|null $createdAt      (Optional) New created_at datetime in 'Y-m-d H:i:s' format. If not provided, leaves the current value unchanged.
     * @return bool
     */
    public function update($postId, $title, $content, $featureImage = null, $slug = '', $description = '', $keywords = '', $createdAt = null, $a_script = null) {
        // Auto-generate slug if not provided
        if (empty($slug)) {
            $slug = $this->slugify($title);
        }
        // Auto-generate description if not provided
        if (empty($description)) {
            $description = substr(strip_tags($content), 0, 100);
        }
        
        // Build the base SQL query and parameters array
        $fields = "title = ?, content = ?, slug = ?, description = ?, keywords = ?, a_script = ?";
        $params = [$title, $content, $slug, $description, $keywords, $a_script];
        
        // Conditionally update feature image if provided
        if ($featureImage !== null) {
            $fields .= ", feature_image = ?";
            $params[] = $featureImage;
        }
        
        // Conditionally update created_at if provided
        if ($createdAt !== null) {
            $fields .= ", created_at = ?";
            $params[] = $createdAt;
        }
        
        // Always update updated_at to NOW()
        $fields .= ", updated_at = NOW()";
        
        // Add the post ID for the WHERE clause
        $params[] = $postId;
        
        $sql = "UPDATE posts SET $fields WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Instead of deleting, change post status to 'draft'
    public function delete($postId) {
        $stmt = $this->pdo->prepare("UPDATE posts SET status = 'draft', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    // Publish a post (set status to 'published')
    public function publish($postId) {
        $stmt = $this->pdo->prepare("UPDATE posts SET status = 'published', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    // Archive a post
    public function archive($postId) {
        $stmt = $this->pdo->prepare("UPDATE posts SET status = 'archive', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    /**
     * Assign multiple categories to a post.
     *
     * @param int   $postId
     * @param array $categoryIds Array of category IDs.
     * @return void
     */
    public function assignCategoriesToPost($postId, array $categoryIds) {
        // Remove existing associations
        $stmt = $this->pdo->prepare("DELETE FROM post_categories WHERE post_id = ?");
        $stmt->execute([$postId]);

        // Insert new associations
        $stmtInsert = $this->pdo->prepare("INSERT INTO post_categories (post_id, category_id) VALUES (?, ?)");
        foreach ($categoryIds as $catId) {
            $stmtInsert->execute([$postId, $catId]);
        }
    }

    /**
     * Retrieve categories assigned to a post.
     *
     * @param int $postId
     * @return array
     */
    public function getCategoriesForPost($postId) {
        $stmt = $this->pdo->prepare("SELECT c.* FROM categories c 
                                     JOIN post_categories pc ON c.id = pc.category_id 
                                     WHERE pc.post_id = ?");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Extract the first image URL from HTML content.
     *
     * @param string $htmlContent
     * @return string|null  Returns the src attribute of the first image, or null if none found.
     */
    private function extractFirstImage(string $htmlContent): ?string {
        $doc = new \DOMDocument();
        // Suppress errors due to malformed HTML
        @$doc->loadHTML($htmlContent);
        $images = $doc->getElementsByTagName('img');
        if ($images->length > 0) {
            return $images->item(0)->getAttribute('src');
        }
        return null;
    }
    
    /**
     * Retrieve all allowed post slugs.
     *
     * @return array An array of post slugs.
     */
    public function getAllSlugs(): array {
        // Query for published posts only.
        $stmt = $this->pdo->query("SELECT slug FROM posts WHERE status = 'published'");
        // Fetch the slugs as a simple array.
        $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $slugs;
    }



    /**
     * Helper function to generate a slug from text, with year and month directories.
     *
     * @param string $text
     * @return string
     */
    private function slugify($text) {
        // Replace non-letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // Trim
        $text = trim($text, '-');
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // Lowercase
        $text = strtolower($text);
        
        // Ensure we have something usable
        if (empty($text)) {
            $text = 'n-a';
        }
        
        // Append the .html extension
        $slug = $text . '.html';
        
        // Prepend the current year and month as directories
        $year  = date('Y');
        $month = date('m');
        
        return $year . '/' . $month . '/' . $slug;
    }
}
