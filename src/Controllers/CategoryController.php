<?php
namespace App\Controllers;

use App\Database;
use PDO;

class CategoryController {

    protected $pdo;
    
    public function __construct() {
        $this->pdo = Database::getConnection();
    }
    
    // Create a new category
    public function createCategory($name, $slug) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        return $stmt->execute([$name, $slug]);
    }
    
    // Retrieve all categories
    public function getAllCategories() {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Retrieve a category by its slug
    public function getCategoryBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Update a category by its ID
    public function updateCategory($id, $name, $slug) {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $id]);
    }
    
    // Delete a category by its ID
    public function deleteCategory($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Retrieve published posts associated with a given category ID.
     *
     * @param int $categoryId
     * @return array
     */
    public function getPostsByCategoryId($categoryId) {
        $stmt = $this->pdo->prepare("SELECT p.* FROM posts p 
                                     JOIN post_categories pc ON p.id = pc.post_id 
                                     WHERE pc.category_id = ? AND p.status = 'published'
                                     ORDER BY p.created_at DESC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
