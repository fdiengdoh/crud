<?php
namespace App\Controllers;

use App\Database;
use PDO;

class CommentController {

    protected $pdo;
    // Define banned words (adjust as needed)
    protected $bannedWords = [
        'viagra', 
        'fuck',
        '#1',
        '100% more',
        '100% free',
        '100% satisfied',
        'Additional income',
        'Be your own boss',
        'Best price',
        'Big bucks',
        'Billion',
        'Cash bonus',
        'Cents on the dollar',
        'Consolidate debt',
        'Double your cash',
        'Double your income',
        'Earn extra cash',
        'Earn money',
        'Eliminate bad credit',
        'Extra cash',
        'Extra income',
        'Expect to earn',
        'Fast cash',
        'Financial freedom',
        'Free access',
        'Free consultation',
        'Free gift',
        'Free hosting',
        'Free info',
        'Free investment',
        'Free membership',
        'Free money',
        'Free preview',
        'Free quote',
        'Free trial',
        'Full refund',
        'Get out of debt',
        'Get paid',
        'Giveaway',
        'Guaranteed',
        'Increase sales',
        'Increase traffic',
        'Incredible deal',
        'Lower rates',
        'Lowest price',
        'Make money',
        'Million dollars',
        'Miracle',
        'Money back',
        'Once in a lifetime',
        'One time',
        'Pennies a day',
        'Potential earnings',
        'Prize',
        'Promise',
        'Pure profit',
        'Risk-free',
        'Satisfaction guaranteed',
        'Save big money',
        'Save up to',
        'Special promotion'
    ];

    public function __construct() {
        $this->pdo = Database::getConnection();
    }
    
    //Get page slug from comment id
    public function getSlug($id){
        $stmt = $this->pdo->prepare("SELECT slug FROM posts WHERE id = (SELECT post_id FROM comments WHERE id = ?)");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Save a comment.
     * Auto-approve unless the comment contains links or banned words (then mark as pending).
     *
     * @param int    $postId
     * @param string $author
     * @param string $email
     * @param string $comment
     * @return bool
     */
    public function saveComment($postId, $author, $email, $comment) {
        // Check for URLs in the comment
        $containsLink = preg_match('/https?:\\/\\//i', $comment);

        // Check for banned words in the comment
        $containsBanned = false;
        foreach ($this->bannedWords as $word) {
            if (stripos($comment, $word) !== false) {
                $containsBanned = true;
                break;
            }
        }

        // If contains link or banned word, mark as pending; else approved
        $status = ($containsLink || $containsBanned) ? 'pending' : 'approved';

        $stmt = $this->pdo->prepare("INSERT INTO comments (post_id, author, email, comment, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$postId, $author, $email, $comment, $status]);
    }

    /**
     * Retrieve comments for a post that are approved or reported (both visible publicly).
     *
     * @param int $postId
     * @return array
     */
    public function getApprovedComments($postId) {
        $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE post_id = ? AND status IN ('approved','reported') ORDER BY created_at ASC");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve comments pending moderation (both pending and reported).
     *
     * @return array
     */
    public function getPendingComments() {
        $stmt = $this->pdo->query("SELECT * FROM comments WHERE status IN ('pending','reported') ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Flag an existing comment (set its status to pending).
     *
     * @param int $commentId
     * @return bool
     */
    public function flagComment($commentId) {
        $stmt = $this->pdo->prepare("UPDATE comments SET status = 'pending', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    /**
     * Report a comment. This will mark an existing comment as "reported".
     *
     * @param int $commentId
     * @return bool
     */
    public function reportComment($commentId) {
        $stmt = $this->pdo->prepare("UPDATE comments SET status = 'reported', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    /**
     * Approve a pending or reported comment.
     *
     * @param int $commentId
     * @return bool
     */
    public function approveComment($commentId) {
        $stmt = $this->pdo->prepare("UPDATE comments SET status = 'approved', updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    /**
     * Delete a comment.
     *
     * @param int $commentId
     * @return bool
     */
    public function deleteComment($commentId) {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$commentId]);
    }
}