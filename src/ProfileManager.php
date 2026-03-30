<?php
declare(strict_types=1);

namespace App;

use Delight\Auth\Auth;
use PDO;

class ProfileManager
{
    protected Auth $auth;
    protected PDO $pdo;

    public function __construct(Auth $auth, PDO $pdo)
    {
        $this->auth = $auth;
        $this->pdo = $pdo;
    }

    /**
     * Create a new profile.
     *
     * @param int $userId User ID
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string|null $bio Biography (optional)
     * @return bool
     */
    public function createProfile(
        int $userId,
        string $firstName,
        string $lastName,
        ?string $bio = null
    ): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO user_profiles (user_id, first_name, last_name, bio, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $firstName, $lastName, $bio]);
    }

    /**
     * Read a profile by user ID.
     *
     * @param int $userId User ID
     * @return array|null
     */
    public function getProfile(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        return $profile !== false ? $profile : null;
    }

    /**
     * Update a profile.
     *
     * @param int $userId User ID
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string|null $bio Biography (optional)
     * @return bool
     */
    public function updateProfile(
        int $userId,
        string $firstName,
        string $lastName,
        ?string $bio = null
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE user_profiles
            SET first_name = ?, last_name = ?, bio = ?, updated_at = NOW()
            WHERE user_id = ?
        ");
        return $stmt->execute([$firstName, $lastName, $bio, $userId]);
    }

    /**
     * Delete a profile by user ID.
     *
     * @param int $userId User ID
     * @return bool
     */
    public function deleteProfile(int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM user_profiles WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
