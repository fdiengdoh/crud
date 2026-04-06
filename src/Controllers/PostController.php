<?php
declare(strict_types=1);

/*
 * This class will control queries related to posts, categories, users
 */
namespace App\Controllers;

use App\Database;
use PDO;

class PostController
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Retrieve posts by user (if provided) with optional filtering.
     *
     * @param array    $options   If provided.
     * @return array
     */
    public function index(array $options = []): array
    {
        $params = [];
        
        $sql = "SELECT p.* FROM posts p";
        
        if (!empty($options['category'])) {
            $sql .= " JOIN post_categories pc ON p.id = pc.post_id";
        }
        
        $where = [];
        
        if (!empty($options['category'])) {
            $where[] = "pc.category_id = ?";
            $params[] = $options['category'];
        }
        
        if (!empty($options['status'])) {
            $where[] = "p.status = ?";
            $params[] = $options['status'];
        }
        
        if (!empty($options['userId'])) {
            $where[] = "p.user_id = ?";
            $params[] = $options['userId'];
        }
        
        if (count($where) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if (!empty($options['limit']) && $options['limit'] > 0) {
            $sql .= " LIMIT " . (int)$options['limit'];
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($posts as &$post) {
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['feature_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
            }
            $post['excerpt'] = $this->generateExcerpt($post['content']);
        }
        
        return $posts;
    }

    public function show(int|string $identifier): array|false
    {
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
        if ($post) {
            $post['excerpt'] = $this->generateExcerpt($post['content']);
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['og_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
            }
        }
        return $post;
    }

    public function getPreviousPost(int $currentPostId): array|false|null
    {
        $stmt = $this->pdo->prepare("SELECT created_at FROM posts WHERE id = ?");
        $stmt->execute([$currentPostId]);
        $currentPost = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentPost) {
            return null;
        }
        $currentCreatedAt = $currentPost['created_at'];
        
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE status = 'published' AND created_at < ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$currentCreatedAt]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($post) {
            $post['excerpt'] = $this->generateExcerpt($post['content']);
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['feature_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
            }
        }
        return $post;
    }

    public function getNextPost(int $currentPostId): array|false|null
    {
        $stmt = $this->pdo->prepare("SELECT created_at FROM posts WHERE id = ?");
        $stmt->execute([$currentPostId]);
        $currentPost = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentPost) {
            return null;
        }
        $currentCreatedAt = $currentPost['created_at'];
        
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE status = 'published' AND created_at > ? ORDER BY created_at ASC LIMIT 1");
        $stmt->execute([$currentCreatedAt]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($post) {
            $post['excerpt'] = $this->generateExcerpt($post['content']);
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['feature_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
            }
        }
        return $post;
    }

    /**
     * Retrieve the most recent published posts.
     *
     * @param int $limit Number of recent posts to retrieve (default is 5).
     * @return array
     */
    public function getRecentPosts(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts 
            WHERE status = 'published'
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($posts as &$post) {
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['feature_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
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
    public function getPopularPosts(int $limit = 5): array
    {
        $limit = (int)$limit;
        $query = "SELECT * FROM posts 
                WHERE status = 'published'
                ORDER BY views DESC 
                LIMIT $limit";
        $stmt = $this->pdo->query($query);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($posts as &$post) {
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['feature_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
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
    public function incrementViews(int $postId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    public function getPosts(string $category = 'all', int $page = 1, int $limit = 10): array
    {
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
            if (empty($post['feature_image'])) {
                $extracted = $this->extractFirstImage($post['content']);
                $post['feature_image'] = $extracted ?: BASE_URL . '/assets/image/default-feature.webp';
            }
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
    public function countPosts(string $category = 'all'): int
    {
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
    private function generateExcerpt(string $content, int $length = 150): string
    {
        $text = strip_tags(html_entity_decode($content));
        return (strlen($text) > $length) ? substr($text, 0, $length) . '...' : $text;
    }

    /**
     * Create a new post with default status 'draft'.
     *
     * @param int    $userId
     * @param string $title
     * @param string $content
     * @param string|null $slug
     * @param string|null $featureImage
     * @param string $description
     * @param string $keywords
     * @param string|null $createdAt
     * @param string|null $a_script
     * @param bool   $allowComments
     * @return int Last inserted post ID.
     */
    public function create(int $userId, string $title, string $content, ?string $slug = null, ?string $featureImage = null, string $description = '', string $keywords = '', ?string $createdAt = null, ?string $a_script = null, bool $allowComments = true): int
    {
        if (empty($slug)) {
            $slug = $this->slugify($title);
        }
        if (empty($description)) {
            $description = substr(strip_tags($content), 0, 100);
        }
        $createdAt = $createdAt ?? date('Y-m-d H:i:s');
        
        $stmt = $this->pdo->prepare("
            INSERT INTO posts 
            (user_id, title, content, slug, feature_image, description, keywords, status, created_at, a_script, allow_comments)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'draft', ?, ?, ?)
        ");
        $stmt->execute([$userId, $title, $content, $slug, $featureImage, $description, $keywords, $createdAt, $a_script, $allowComments]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update an existing post with additional fields.
     *
     * @param int         $postId
     * @param string      $title
     * @param string      $content
     * @param string|null $featureImage
     * @param string      $slug
     * @param string      $description
     * @param string      $keywords
     * @param string|null $createdAt
     * @param string|null $a_script
     * @param bool|null   $allowComments
     * @return bool
     */
    public function update(int $postId, string $title, string $content, ?string $featureImage = null, string $slug = '', string $description = '', string $keywords = '', ?string $createdAt = null, ?string $a_script = null, ?bool $allowComments = null): bool
    {
        if (empty($slug)) {
            $slug = $this->slugify($title);
        }
        if (empty($description)) {
            $description = substr(strip_tags($content), 0, 100);
        }
        
        $fields = "title = ?, content = ?, slug = ?, description = ?, keywords = ?, a_script = ?";
        $params = [$title, $content, $slug, $description, $keywords, $a_script];
        
        if ($featureImage !== null) {
            $fields .= ", feature_image = ?";
            $params[] = $featureImage;
        }
        
        if ($createdAt !== null) {
            $fields .= ", created_at = ?";
            $params[] = $createdAt;
        }
        
        if ($allowComments !== null) {
            $fields .= ", allow_comments = ?";
            $params[] = $allowComments;
        }
        
        $fields .= ", updated_at = NOW()";
        $params[] = $postId;
        
        $stmt = $this->pdo->prepare("UPDATE posts SET $fields WHERE id = ?");
        return $stmt->execute($params);
    }

    public function delete(int $postId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE posts SET status = 'draft', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    public function publish(int $postId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE posts SET status = 'published', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$postId]);
    }

    public function archive(int $postId): bool
    {
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
    public function assignCategoriesToPost(int $postId, array $categoryIds): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM post_categories WHERE post_id = ?");
        $stmt->execute([$postId]);

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
    public function getCategoriesForPost(int $postId): array
    {
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
    private function extractFirstImage(string $htmlContent): ?string
    {
        $doc = new \DOMDocument();
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
    public function getAllSlugs(): array
    {
        $stmt = $this->pdo->query("SELECT slug FROM posts WHERE status = 'published'");
        $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $slugs;
    }

    /**
     * Helper function to generate a slug from text, with year and month directories.
     *
     * @param string $text
     * @return string
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        if (empty($text)) {
            $text = 'n-a';
        }
        
        $slug = $text . '.html';
        $year  = date('Y');
        $month = date('m');
        
        return $year . '/' . $month . '/' . $slug;
    }
}
