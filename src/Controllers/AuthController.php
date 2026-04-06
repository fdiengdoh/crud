<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use App\Mailer;
use Delight\Auth\Auth;
use Delight\Auth\Role;
use Delight\Auth\AuthException;
use PDO;

/**
 * Optimized for PHP 8.5
 * Uses Strict Types, Asymmetric Visibility, and Match Expressions.
 */
final class AuthController {

    // Asymmetric Visibility: Publicly readable, but only writable by this class.
    // This allows $router to check $auth->isLoggedIn() without a getter method.
    public private(set) Auth $auth;
    public private(set) PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
        $this->auth = new Auth($this->pdo);
    }

    /**
     * Register a new user.
     */
    public function register(string $email, string $username, string $password, ?string $verificationLink = null): string {
        $verificationLink ??= $this->generateBaseUrl() . '/verify';
        
        $emailStatusMessage = '';

        try {
            $userId = $this->auth->register($email, $password, $username, function (string $selector, string $token) use (&$emailStatusMessage, $email, $username, $verificationLink) {
                $link = "{$verificationLink}/?selector=" . urlencode($selector) . "&token=" . urlencode($token);
                
                $result = Mailer::sendVerificationEmail($email, $username, $link);
                $emailStatusMessage = ($result === true) 
                    ? "A verification email has been sent to {$email}." 
                    : "Verification email could not be sent. " . $result;
            });

            $this->auth->admin()->addRoleForUserById($userId, Role::SUBSCRIBER);
            return "Registration successful. " . $emailStatusMessage;

        } catch (AuthException $e) {
            return match (get_class($e)) {
                \Delight\Auth\InvalidEmailException::class => "Invalid email address.",
                \Delight\Auth\InvalidPasswordException::class => "Invalid password.",
                \Delight\Auth\UserAlreadyExistsException::class => "User already exists.",
                \Delight\Auth\TooManyRequestsException::class => "Too many requests. Please try again later.",
                default => "An unknown registration error occurred."
            };
        }
    }

    /**
     * Log in an existing user.
     */
    public function login(string $email, string $password, bool $remember = false): string {
        try {
            // PHP 8.x handles the 'remember' boolean directly more efficiently
            $this->auth->login($email, $password, $remember ? (int)(60 * 60 * 24 * 365) : null);
            return "success";
        } catch (AuthException $e) {
            return match (get_class($e)) {
                \Delight\Auth\EmailNotVerifiedException::class => "Email not verified.",
                \Delight\Auth\TooManyRequestsException::class => "Too many requests.",
                default => "Invalid Credentials." // Combined for security
            };
        }
    }

    public function logout(): string {
        $this->auth->logOut();
        return "Logged out successfully.";
    }

    /**
     * Request a password reset.
     */
    public function forgotPassword(string $email, ?string $resetLink = null): string {
        $resetLink ??= $this->generateBaseUrl() . '/reset-password';

        try {
            $this->auth->forgotPassword($email, function (string $selector, string $token) use (&$resetLink) {  
                $resetLink .= "/?selector=" . urlencode($selector) . "&token=" . urlencode($token);
            });

            $result = Mailer::sendResetPasswordEmail($email, $email, $resetLink);
            
            return ($result === true) 
                ? "Password reset instructions sent to {$email}." 
                : "Reset instructions could not be sent. " . $result;

        } catch (AuthException $e) {
            return match (get_class($e)) {
                \Delight\Auth\InvalidEmailException::class => "Invalid email address.",
                \Delight\Auth\EmailNotVerifiedException::class => "Email not verified.",
                \Delight\Auth\ResetDisabledException::class => "Password reset is disabled.",
                default => "Too many requests. Please try again later."
            };
        }
    }

    public function resetPassword(string $selector, string $token, string $newPassword): string {
        try {
            $this->auth->resetPassword($selector, $token, $newPassword);
            return "Password reset successful.";
        } catch (AuthException $e) {
            return match (get_class($e)) {
                \Delight\Auth\InvalidSelectorTokenPairException::class => "Invalid link.",
                \Delight\Auth\TokenExpiredException::class => "Link expired.",
                \Delight\Auth\ResetDisabledException::class => "Reset disabled.",
                default => "Too many requests."
            };
        }
    }

    /**
     * Private helper to keep URL logic clean
     */
    private function generateBaseUrl(): string {
        $protocol = ($_SERVER['HTTPS'] ?? '') === 'on' ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . $host;
    }
}
