<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use PDO;

class CommentController
{
    protected PDO $pdo;
    protected array $bannedWords = [
        'viagra', 'fuck', '#1', '100% more', '100% free', '100% satisfied',
        'Additional income', 'Be your own boss', 'Best price', 'Big bucks',
        'Billion', 'Cash bonus', 'Cents on the dollar', 'Consolidate debt',
        'Double your cash', 'Double your income', 'Earn extra cash',
        'Earn money', 'Eliminate bad credit', 'Extra cash', 'Extra income',
        'Expect to earn', 'Fast cash', 'Financial freedom', 'Free access',
        'Free consultation', 'Free gift', 'Free hosting', 'Free info',
        'Free investment', 'Free membership', 'Free money', 'Free preview',
        'Free quote', 'Free trial', 'Full refund', 'Get out of debt',
        'Get paid', 'Giveaway', 'Guaranteed', 'Increase sales',
        'Increase traffic', 'Incredible deal', 'Lower rates', 'Lowest price',
        'Make money', 'Million dollars', 'Miracle', 'Money back',
        'Once in a lifetime', 'One time', 'Pennies a day', 'Potential earnings',
        'Prize', 'Promise', 'Pure profit', 'Risk-free', 'Satisfaction guaranteed',
        'Save big money', 'Save up to', 'Special promotion'
    ];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function getSlug(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT slug FROM posts WHERE id = (SELECT post_id FROM comments WHERE id = ?)");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveComment(int $postId, string $author, string $email, string $comment): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $containsLink = preg_match('/https?:\/\//i', $comment);
        $containsBanned = false;
        
        foreach ($this->bannedWords as $word) {
            if (stripos($comment, $word) !== false) {
                $containsBanned = true;
                break;
            }
        }

        $status = 'pending';

        $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, author, email, comment, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$postId, $author, $email, $comment, $status]);
    }

    public function getApprovedComments(int $postId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE post_id = ? AND status IN ('approved','reported') ORDER BY created_at ASC");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingComments(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM comments WHERE status IN ('pending','reported') ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function flagComment(int $commentId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE comments SET status = 'pending', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    public function reportComment(int $commentId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE comments SET status = 'reported', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    public function approveComment(int $commentId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE comments SET status = 'approved', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    public function deleteComment(int $commentId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$commentId]);
    }
}
