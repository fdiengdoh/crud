<?php
namespace App\Helpers;

class CsrfHelper {
    private static $cookieName = 'csrf_token';

    /**
     * Generate/retrieve a token and sync it across Session and Cookie.
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Ensure the session has a token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $token = $_SESSION['csrf_token'];

        // 2. Sync the token to a cookie for JavaScript access
        // We set 'httponly' to false so JS can read it on cached pages.
        if (!isset($_COOKIE[self::$cookieName]) || $_COOKIE[self::$cookieName] !== $token) {
            setcookie(self::$cookieName, $token, [
                'expires' => time() + 3600 * 24, // 24 hours
                'path' => '/',
                'domain' => '', // Set to your domain if across subdomains
                'secure' => false,
                'httponly' => false, 
                'samesite' => 'Lax',
            ]);
        }

        return $token;
    }

    /**
     * Validate the token against either the Session or the Cookie.
     */
    public static function isValid($token) {
        if (empty($token)) {
            return false;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Check against Session (Primary check)
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }

        // 2. Check against Cookie (Double Submit fallback for cached pages)
        // This is safe because an attacker cannot read/set your cookies.
        if (isset($_COOKIE[self::$cookieName]) && hash_equals($_COOKIE[self::$cookieName], $token)) {
            return true;
        }

        return false;
    }
}
