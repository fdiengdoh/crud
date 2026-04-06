<?php
namespace App;

use Delight\Auth\Auth;
use PDO;

class ProfileManager {
    protected $auth;
    protected $pdo;

    public function __construct(Auth $auth, PDO $pdo) {
        $this->auth = $auth;
        $this->pdo = $pdo;
    }

    // Create a new profile
    public function createProfile(int $userId, string $firstName, string $lastName, ?string $bio = null): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_profiles (user_id, first_name, last_name, bio, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $firstName, $lastName, $bio]);
    }

    // Read a profile
    public function getProfile(int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        return $profile !== false ? $profile : null;
    }

    // Update a profile
    public function updateProfile(int $userId, string $firstName, string $lastName, ?string $bio = null): bool {
        $stmt = $this->pdo->prepare("
            UPDATE user_profiles 
            SET first_name = ?, last_name = ?, bio = ?, updated_at = NOW()
            WHERE user_id = ?
        ");
        return $stmt->execute([$firstName, $lastName, $bio, $userId]);
    }

    // Delete a profile
    public function deleteProfile(int $userId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM user_profiles WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
