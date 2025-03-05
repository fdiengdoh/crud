<?php
namespace App\Controllers;

use App\Database;
use PDO;

class PostController {

    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // Retrieve posts by user if provided
    public function index($userId = null) {
        if ($userId) {
            $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
        }
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Generate an excerpt for each post
        foreach ($posts as &$post) {
            $post['excerpt'] = $this->generateExcerpt($post['content']);
        }
        return $posts;
    }

    // Retrieve a single post by ID or slug, including author's username and profile details
    public function show($identifier) {
        if (is_numeric($identifier)) {
            $stmt = $this->pdo->prepare("
                SELECT p.*, u.username, up.first_name, up.last_name, up.profile_picture, up.bio
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE p.id = ?
            ");
            $stmt->execute([$identifier]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT p.*, u.username, up.first_name, up.last_name, up.profile_picture, up.bio
                FROM posts p
                JOIN users u ON p.user_id = u.id
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE p.slug = ?
            ");
            $stmt->execute([$identifier]);
        }
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        $post ? $post['excerpt'] = $this->generateExcerpt($post['content']) : '';
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



    /**
     * Retrieve posts for a given category (if provided) or all posts,
     * with pagination support.
     *
     * @param string|null $category Slug of the category, or null for all posts.
     * @param int $page Page number (default 1).
     * @param int $limit Number of posts per page (default 10).
     * @return array
     */
    public function getPosts($category = null, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        if ($category) {
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
        // Generate an excerpt for each post
        foreach ($posts as &$post) {
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
    public function countPosts($category = null) {
        if ($category) {
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
     * @param string $featureImage  (optional) - defaults to 'assets/image/default-feature.jpg'.
     * @param string $description   (optional) - if not provided, auto-generated from content.
     * @param string $keywords      (optional) - comma-separated keywords.
     * @param string $createdAt     (optional) - if not provided, defaults to current timestamp.
     * @return int Last inserted post ID.
     */
    public function create($userId, $title, $content, $slug = null, $featureImage = 'assets/image/default-feature.jpg', $description = '', $keywords = '', $createdAt = null) {
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
            (user_id, title, content, slug, feature_image, description, keywords, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', ?)
        ");
        $stmt->execute([$userId, $title, $content, $slug, $featureImage, $description, $keywords, $createdAt]);
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
    public function update($postId, $title, $content, $featureImage = null, $slug = '', $description = '', $keywords = '', $createdAt = null) {
        // Auto-generate slug if not provided
        if (empty($slug)) {
            $slug = $this->slugify($title);
        }
        // Auto-generate description if not provided
        if (empty($description)) {
            $description = substr(strip_tags($content), 0, 100);
        }
        
        // Build the base SQL query and parameters array
        $fields = "title = ?, content = ?, slug = ?, description = ?, keywords = ?";
        $params = [$title, $content, $slug, $description, $keywords];
        
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

    /*/ Update an existing post with additional fields.
    // If a new feature image is provided (non-null), update it; otherwise, leave it unchanged.
    // Optional parameters: $slug, $description, $keywords.
    public function update($postId, $title, $content, $featureImage = null, $slug = '', $description = '', $keywords = '', $createdAt = null) {
        // If slug is empty, auto-generate from the title
        if (empty($slug)) {
            $slug = $this->slugify($title);
        }
        // If description is empty, auto-generate from the first 100 characters of the content
        if (empty($description)) {
            $description = substr(strip_tags($content), 0, 100);
        }

        if ($featureImage !== null) {
            $stmt = $this->pdo->prepare("
                UPDATE posts 
                SET title = ?, content = ?, slug = ?, description = ?, keywords = ?, feature_image = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$title, $content, $slug, $description, $keywords, $featureImage, $postId]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE posts 
                SET title = ?, content = ?, slug = ?, description = ?, keywords = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            return $stmt->execute([$title, $content, $slug, $description, $keywords, $postId]);
        }
    }*/

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
     * Helper function to generate a slug from text.
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
        if (empty($text)) {
            return 'n-a.html';
        }
        return $text . '.html';
    }
}
