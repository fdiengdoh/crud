<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use PDO;

class CategoryController
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function createCategory(string $name, string $slug): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        return $stmt->execute([$name, $slug]);
    }

    public function getAllCategories(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryBySlug(string $slug): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCategory(int $id, string $name, string $slug): bool
    {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $id]);
    }

    public function deleteCategory(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllCategorySlugs(): array
    {
        $stmt = $this->pdo->query("SELECT slug FROM categories");
        $slugs = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $slugs;
    }

    public function getPostsByCategoryId(int $categoryId): array
    {
        $stmt = $this->pdo->prepare("SELECT p.* FROM posts p 
                                     JOIN post_categories pc ON p.id = pc.post_id 
                                     WHERE pc.category_id = ? AND p.status = 'published'
                                     ORDER BY p.created_at DESC");
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
