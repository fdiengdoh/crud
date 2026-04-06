<?php
declare(strict_types=1);

namespace App\Helpers;

class Link
{
    public array $routes = [];

    /**
     * Optionally, you can pass an initial routes array.
     *
     * @param array $routes
     */
    public function __construct(array $routes = [])
    {
        if (!empty($routes)) {
            $this->routes = $routes;
        } else {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            // Set default routes based on the current app structure.
            $this->routes = [
                ''                      => [
                    'url'  => BASE_URL . '/',
                    'file' => APP_DIR . '/home.php'
                ],
                '/'                      => [
                    'url'  => BASE_URL . '/',
                    'file' => APP_DIR . '/home.php'
                ],
                '/login'                 => [
                    'url'  => BASE_URL . '/login',
                    'file' => APP_DIR . '/users/login.php'
                ],
                '/register'              => [
                    'url'  => BASE_URL . '/register',
                    'file' => APP_DIR . '/users/register.php'
                ],
                '/verify'                => [
                    'url'  => BASE_URL . '/verify',
                    'file' => APP_DIR . '/users/verify.php'
                ],
                '/forgot-password'       => [
                    'url'  => BASE_URL . '/forgot-password',
                    'file' => APP_DIR . '/users/forgot-password.php'
                ],
                '/reset-password'        => [
                    'url'  => BASE_URL . '/reset-password',
                    'file' => APP_DIR . '/users/reset-password.php'
                ],
                '/my-posts'              => [
                    'url'  => BASE_URL . '/my-posts',
                    'file' => APP_DIR . '/users/my-posts.php'
                ],
                '/admin'                 => [
                    'url'  => BASE_URL . '/admin',

                    'file' => APP_DIR . '/admin/admin-panel.php'
                ],
                '/admin/categories'      => [
                    'url'  => BASE_URL . '/admin/categories',
                    'file' => APP_DIR . '/admin/admin-categories.php'
                ],
                '/admin/view-logs'       => [
                    'url'  => BASE_URL . '/admin/view-logs',
                    'file' => APP_DIR . '/admin/view-logs.php'
                ],
                '/logout'                => [
                    'url'  => BASE_URL . '/logout',
                    'file' => APP_DIR . '/logout.php'
                ],
                '/post-create'           => [
                    'url'  => BASE_URL . '/post-create',
                    'file' => APP_DIR . '/users/post-create.php'
                ],
                '/post-edit'             => [
                    'url'  => BASE_URL . '/post-edit',
                    'file' => APP_DIR . '/users/post-edit.php'
                ],
                '/post-delete'           => [
                    'url'  => BASE_URL . '/post-delete',
                    'file' => APP_DIR . '/users/post-delete.php'
                ],
                // Additional routes can be added here.
            ];
        }
    }

    /**
     * Retrieve the URL for a given route key.
     *
     * @param string $key The route key (e.g., '/login')
     * @return string|null
     */
    public function getUrl(string $key): ?string
    {
        return $this->routes[$key]['url'] ?? null;
    }

    /**
     * Retrieve the file path for a given route key with strict security validation.
     *
     * @param string $key The route key (e.g., '/login')
     * @return string|null
     */
    public function getFile(string $key): ?string
    {
        // Check if the key even exists in our defined routes
        if (!isset($this->routes[$key]['file'])) {
            return null;
        }

        $requestedFile = $this->routes[$key]['file'];

        // Canonicalize the paths
        $realAppDir = realpath(APP_DIR);
        $realRequestedFile = realpath($requestedFile);

        // Validation Logic
        if ($realRequestedFile && $realAppDir) {
            // Check if the requested file actually resides inside APP_DIR
            if (str_starts_with($realRequestedFile, $realAppDir)) {
                return $requestedFile;
            }
        }

        // If the path is outside APP_DIR or doesn't exist, log a security warning
        error_log("Security Warning: Attempted access to file outside APP_DIR: " . $requestedFile);
        return null;
    }
}
