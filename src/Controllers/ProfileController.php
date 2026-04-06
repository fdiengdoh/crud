<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use PDO;

class ProfileController
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function showProfile(int $userId): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProfileByUsername(string $username): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT up.*, u.username 
            FROM user_profiles up 
            JOIN users u ON up.user_id = u.id 
            WHERE u.username = ?
        ");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveProfile(int $userId, string $firstName, string $lastName, ?string $bio = null): bool
    {
        $profile = $this->showProfile($userId);
        if ($profile) {
            $stmt = $this->pdo->prepare("UPDATE user_profiles SET first_name = ?, last_name = ?, bio = ?, updated_at = NOW() WHERE user_id = ?");
            return $stmt->execute([$firstName, $lastName, $bio, $userId]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO user_profiles (user_id, first_name, last_name, bio, created_at) VALUES (?, ?, ?, ?, NOW())");
            return $stmt->execute([$userId, $firstName, $lastName, $bio]);
        }
    }

    public function updateProfilePicture(int $userId, string $profilePictureUrl): bool
    {
        $stmt = $this->pdo->prepare("UPDATE user_profiles SET profile_picture = ?, updated_at = NOW() WHERE user_id = ?");
        return $stmt->execute([$profilePictureUrl, $userId]);
    }

    public function deleteProfile(int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM user_profiles WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
