<?php
namespace App\Helpers;

use Delight\Auth\Auth;
use Delight\Auth\Role;

class AuthHelper {
    /**
     * Redirects to the login page if the user is not logged in
     * or does not have admin privileges.
     *
     * @param Auth $auth
     */
    public static function requireAdmin(Auth $auth) {
        if (!$auth->isLoggedIn() || !$auth->hasRole(Role::ADMIN)) {
            header("Location: /login");
            exit;
        }
    }
}