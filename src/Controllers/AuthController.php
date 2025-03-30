<?php
namespace App\Controllers;

use App\Database;
use Delight\Auth\Auth;
use Delight\Auth\Role;
use App\Mailer;

class AuthController {

    protected $auth;
    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
        $this->auth = new Auth($this->pdo);
    }

    /**
     * Register a new user.
     *
     * Sends a verification email via Mailer and assigns a default role of SUBSCRIBER.
     *
     * @param string $email
     * @param string $username
     * @param string $password
     * @return string
     */
    public function register($email, $username, $password, $verificationLink = null ) {
        if(!$verificationLink){
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $verificationLink = $protocol . $host . '/verify';
        }
        $emailStatusMessage = '';
        try {
            $userId = $this->auth->register($email, $password, $username, function ($selector, $token) use (&$emailStatusMessage, $email, $username) {
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
        } catch (\Delight\Auth\InvalidEmailException $e) {
            return "Invalid email address.";
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            return "Invalid password.";
        } catch (\Delight\Auth\UserAlreadyExistsException $e) {
            return "User already exists.";
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Log in an existing user.
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    public function login($email, $password, $remember = false) {
        $remember ? $rememberDuration = (int) (60 * 60 * 24 * 365.25) : $rememberDuration = null;
        try {
            $this->auth->login($email, $password, $remember);
            return "success";
        } catch (\Delight\Auth\InvalidEmailException $e) {
            return "Invalid Credentials.";
        } catch (\Delight\Auth\InvalidPasswordException $e) {
            return "Invalid Credentials.";
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            return "Email not verified.";
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Log out the current user.
     *
     * @return string
     */
    public function logout() {
        $this->auth->logOut();
        return "Logged out successfully.";
    }

    /**
     * Request a password reset.
     *
     * Sends a reset password email containing a reset link via Mailer.
     *
     * @param string $email
     * @return string
     */
    public function forgotPassword($email, $resetLink = null ) {
        if(!$resetLink){
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $resetLink = $protocol . $host . '/reset-password';
        }
        try {
            $this->auth->forgotPassword($email, function ($selector, $token) use (&$resetLink, $email) {    
                $resetLink = $resetLink ."/?selector=" . urlencode($selector) . "&token=" . urlencode($token);
            });
            // Use Mailer to send reset password email.
            $result = Mailer::sendResetPasswordEmail($email, $email, $resetLink);
            if ($result === true) {
                return "Password reset instructions have been sent to {$email}.";
            } else {
                return "Password reset instructions could not be sent. " . $result;
            }
        } catch (\Delight\Auth\InvalidEmailException $e) {
            return "Invalid email address.";
        } catch (\Delight\Auth\EmailNotVerifiedException $e) {
            return "Email not verified.";
        } catch (\Delight\Auth\ResetDisabledException $e) {
            return "Password reset is disabled.";
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Reset the user's password.
     *
     * @param string $selector
     * @param string $token
     * @param string $newPassword
     * @return string
     */
    public function resetPassword($selector, $token, $newPassword) {
        try {
            $this->auth->resetPassword($selector, $token, $newPassword);
            return "Password reset successful.";
        } catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
            return "Invalid selector/token pair.";
        } catch (\Delight\Auth\TokenExpiredException $e) {
            return "Token expired.";
        } catch (\Delight\Auth\ResetDisabledException $e) {
            return "Password reset is disabled.";
        } catch (\Delight\Auth\TooManyRequestsException $e) {
            return "Too many requests. Please try again later.";
        }
    }

    /**
     * Assign the admin role to a user by ID.
     *
     * @param int $userId
     * @return string
     */
    public function assignAdminRole($userId) {
        try {
            $this->auth->admin()->addRoleForUserById($userId, \Delight\Auth\Role::ADMIN);
            return "Admin role assigned to user with ID: " . $userId;
        } catch (\Exception $e) {
            return "Error assigning admin role: " . $e->getMessage();
        }
    }
}
