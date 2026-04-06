<?php
// src/Controllers/ProfileController.php

namespace App\Controllers;

use App\Database;
use PDO;

class ProfileController {

    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    // Retrieve the profile for a given user ID
    public function showProfile($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Retrieve the public profile for a given username.
    // This method joins the user_profiles table with the users table
    // to obtain the username along with profile details.
    public function getProfileByUsername($username) {
        $stmt = $this->pdo->prepare("
            SELECT up.*, u.username 
            FROM user_profiles up 
            JOIN users u ON up.user_id = u.id 
            WHERE u.username = ?
        ");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create or update a profile for a given user ID
    public function saveProfile($userId, $firstName, $lastName, $bio = null) {
        $profile = $this->showProfile($userId);
        if ($profile) {
            // Update existing profile
            $stmt = $this->pdo->prepare("UPDATE user_profiles SET first_name = ?, last_name = ?, bio = ?, updated_at = NOW() WHERE user_id = ?");
            return $stmt->execute([$firstName, $lastName, $bio, $userId]);
        } else {
            // Insert new profile
            $stmt = $this->pdo->prepare("INSERT INTO user_profiles (user_id, first_name, last_name, bio, created_at) VALUES (?, ?, ?, ?, NOW())");
            return $stmt->execute([$userId, $firstName, $lastName, $bio]);
        }
    }

    // Update profile picture for a given user ID
    public function updateProfilePicture($userId, $profilePictureUrl) {
        $stmt = $this->pdo->prepare("UPDATE user_profiles SET profile_picture = ?, updated_at = NOW() WHERE user_id = ?");
        return $stmt->execute([$profilePictureUrl, $userId]);
    }

    // Delete the profile for a given user ID
    public function deleteProfile($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM user_profiles WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
