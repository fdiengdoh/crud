<?php
declare(strict_types=1);

namespace App;

class Cache
{
    private string $cacheDir;
    private int $ttl;
    private bool $enabled;

    /**
     * Initialize cache with configuration.
     *
     * @param array $config Configuration array with keys: cache_dir, cache_ttl, cache_enabled
     */
    public function __construct(array $config): void
    {
        $this->cacheDir = $config['cache_dir'];
        $this->ttl = $config['cache_ttl'];
        $this->enabled = $config['cache_enabled'];

        // Ensure cache directory exists
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0775, true);
        }
    }

    /**
     * Generate a cache file path based on the URL, preserving the URL structure.
     *
     * Example: "/2023/03/my-slug-file.html" becomes "{cacheDir}/2023/03/my-slug-file.html"
     *
     * @param string $url The URL to cache
     * @return string The cache file path
     */
    public function getCacheFile(string $url): string
    {
        if ($url === '/' || $url === '') {
            $cacheFile = $this->cacheDir . '/home.html';
        } else {
            // Remove any leading slash from the URL
            $relativeUrl = ltrim($url, '/');
            // Build the full path by combining the cache directory and the relative URL path
            $cacheFile = $this->cacheDir . '/' . $relativeUrl;
            // Ensure that the directory for the cache file exists
            $dir = dirname($cacheFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
        return $cacheFile;
    }

    /**
     * Check if a cached file exists and is still valid.
     *
     * @param string $url The URL to check
     * @return bool True if cached and valid, false otherwise
     */
    public function isCached(string $url): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->getCacheFile($url);
        
        // Ensure the file exists before checking its modification time
        return file_exists($file) && (time() - filemtime($file) < $this->ttl);
    }

    /**
     * Retrieve cached content.
     *
     * @param string $url The URL to retrieve from cache
     * @return string|false The cached content as a string, or false if not found, expired, or error
     */
    public function getCache(string $url): string|false
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->getCacheFile($url);

        if (!file_exists($file)) {
            return false; // File does not exist, so it's a cache miss
        }

        // Use @file_get_contents to suppress potential warnings if reading fails
        $content = @file_get_contents($file);

        // If file_get_contents returns false (e.g., permission issue), treat as a miss
        if ($content === false) {
            error_log("Cache Error: Could not read cache file: " . $file);
            @unlink($file); // Attempt to delete a potentially problematic file
            return false;
        }

        return $content; // Return the raw content (HTML or JSON string)
    }

    /**
     * Store content in cache.
     *
     * @param string $url The URL to cache
     * @param string $content The content to store
     * @return bool True on success, false on failure
     */
    public function storeCache(string $url, string $content): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->getCacheFile($url);

        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }

        if (@file_put_contents($file, $content) === false) {
            error_log("Cache Error: Could not write to cache file: " . $file);
            return false;
        }

        return true;
    }

    /**
     * Delete a specific cache file.
     *
     * @param string $url The URL to delete from cache
     * @return bool True on success, false on failure
     */
    public function deleteCache(string $url): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->getCacheFile($url);

        if (!file_exists($file)) {
            return true; // Already doesn't exist, so consider it success
        }

        if (!@unlink($file)) {
            error_log("Cache Error: Could not delete cache file: " . $file);
            return false;
        }

        return true;
    }

    /**
     * Clear all cache files.
     *
     * @return bool True on success, false on failure
     */
    public function clearCache(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }

        return true;
    }
}
