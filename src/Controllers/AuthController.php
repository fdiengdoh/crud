<?php
namespace App\Controllers;

use App\Database;
use Delight\Auth\Auth;
use Delight\Auth\Role;
use App\Mailer;

class AuthController {

    protected Auth $auth;
    protected \PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
        $this->auth = new Auth($this->pdo);
    }

    /**
     * Register a new user.
     *
     * Sends a verification email via Mailer and assigns a default role of SUBSCRIBER.
     */
    public function register(string $email, string $username, string $password, ?string $verificationLink = null): string {
        if (!$verificationLink) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $verificationLink = $protocol . $host . '/verify';
        }

        $emailStatusMessage = '';
        try {
            $userId = $this->auth->register($email, $password, $username, function (string $selector, string $token) use (&$emailStatusMessage, string $email, string $username, string $verificationLink): void {
                $verificationLink = $verificationLink . "/?selector=" . urlencode($selector) . "&token=" . urlencode($token);
                
                $result = Mailer::sendVerificationEmail($email, $username, $verificationLink);
                if ($result === true) {
                    $emailStatusMessage = "A verification email has been sent to {$email}.";
                } else {
                    $emailStatusMessage = "Verification email could not be sent. " . $result;
                }
            });
            $this->auth->admin()->addRoleForUserById($userId, Role::SUBSCRIBER);
            return "Registration successful. " . $emailStatusMessage;
        } catch (\Delight\Auth\InvalidEmailException) {
            return "Invalid email address.";
        } catch (\Delight\Auth\InvalidPasswordException) {
            return "Invalid password.";
        } catch (\Delight\Auth\UserAlreadyExistsException) {
            return "User already exists.";
        } catch (\Delight\Auth\TooManyRequestsException) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Log in an existing user.
     */
    public function login(string $email, string $password, bool $remember = false): string {
        $rememberDuration = $remember ? (int) (60 * 60 * 24 * 365.25) : null;
        try {
            $this->auth->login($email, $password, $remember);
            return "success";
        } catch (\Delight\Auth\InvalidEmailException) {
            return "Invalid Credentials.";
        } catch (\Delight\Auth\InvalidPasswordException) {
            return "Invalid Credentials.";
        } catch (\Delight\Auth\EmailNotVerifiedException) {
            return "Email not verified.";
        } catch (\Delight\Auth\TooManyRequestsException) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Log out the current user.
     */
    public function logout(): string {
        $this->auth->logOut();
        return "Logged out successfully.";
    }

    /**
     * Request a password reset.
     *
     * Sends a reset password email containing a reset link via Mailer.
     */
    public function forgotPassword(string $email, ?string $resetLink = null): string {
        if (!$resetLink) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetLink = $protocol . $host . '/reset-password';
        }
        try {
            $this->auth->forgotPassword($email, function (string $selector, string $token) use (&$resetLink, string $email): void {    
                $resetLink = $resetLink . "/?selector=" . urlencode($selector) . "&token=" . urlencode($token);
            });
            $result = Mailer::sendResetPasswordEmail($email, $email, $resetLink);
            if ($result === true) {
                return "Password reset instructions have been sent to {$email}.";
            } else {
                return "Password reset instructions could not be sent. " . $result;
            }
        } catch (\Delight\Auth\InvalidEmailException) {
            return "Invalid email address.";
        } catch (\Delight\Auth\EmailNotVerifiedException) {
            return "Email not verified.";
        } catch (\Delight\Auth\ResetDisabledException) {
            return "Password reset is disabled.";
        } catch (\Delight\Auth\TooManyRequestsException) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(string $selector, string $token, string $newPassword): string {
        try {
            $this->auth->resetPassword($selector, $token, $newPassword);
            return "Password reset successful.";
        } catch (\Delight\Auth\InvalidSelectorTokenPairException) {
            return "Invalid selector/token pair.";
        } catch (\Delight\Auth\TokenExpiredException) {
            return "Token expired.";
        } catch (\Delight\Auth\ResetDisabledException) {
            return "Password reset is disabled.";
        } catch (\Delight\Auth\TooManyRequestsException) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Assign the admin role to a user by ID.
     */
    public function assignAdminRole(int $userId): string {
        try {
            $this->auth->admin()->addRoleForUserById($userId, Role::ADMIN);
            return "Admin role assigned to user with ID: " . $userId;
        } catch (\Exception $e) {
            return "Error assigning admin role: " . $e->getMessage();
        }
    }
}