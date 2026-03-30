<?php
declare(strict_types=1);

namespace App;

use Delight\Auth\Role;

/**
 * Application authentication constants mapping to Delight\Auth roles.
 */
final class AuthConstants
{
    public const ROLE_ADMIN = Role::ADMIN;
    public const ROLE_AUTHOR = Role::AUTHOR;
    public const ROLE_SUBSCRIBER = Role::SUBSCRIBER;

    private function __construct(): void
    {
        // Private constructor to prevent instantiation
    }
}
