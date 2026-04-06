<?php
declare(strict_types=1);

namespace App\Helpers;

use Delight\Auth\Auth;
use Delight\Auth\Role;

class AuthHelper
{
    /**
     * Redirects to the login page if the user is not logged in
     * or does not have admin privileges.
     *
     * @param Auth $auth
     * @param string $redirect
     * @return void
     */
    public static function requireAdmin(Auth $auth, string $redirect = '/login'): void
    {
        if (!$auth->isLoggedIn() || !$auth->hasRole(Role::ADMIN)) {
            header("Location: $redirect");
            exit;
        }
    }
}
