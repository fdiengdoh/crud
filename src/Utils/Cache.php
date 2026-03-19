<?php
namespace App\Utils;

class Cache {
    private $cacheDir;
    private $ttl;
    private $enabled;

    public function __construct($config) {
        $this->cacheDir = $config['cache_dir'];
        $this->ttl = $config['cache_ttl'];
        $this->enabled = $config['cache_enabled'];

        // Ensure cache directory exists.
        if (!is_dir($this->cacheDir)) {
            // Use 0775 for directory permissions as it's typically safer than 0777
            mkdir($this->cacheDir, 0775, true);
        }
    }

    /**
     * Generate a cache file path based on the URL, preserving the URL structure.
     *
     * Example: "/2023/03/my-slug-file.html" becomes "{cacheDir}/2023/03/my-slug-file.html"
     *
     * @param string $url
     * @return string
     */
    public function getCacheFile($url) {
        if($url === '/' || $url === ''){
            $cacheFile = $this->cacheDir . '/home.html';
        }else{
            
            // Remove any leading slash from the URL
            $relativeUrl = ltrim($url, '/');
            // Build the full path by combining the cache directory and the relative URL path
            $cacheFile = $this->cacheDir . '/' . $relativeUrl;
            // Ensure that the directory for the cache file exists
            $dir = dirname($cacheFile);
            if (!is_dir($dir)) {
                // Ensure correct permissions (e.g., 0775)
                mkdir($dir, 0775, true);
            }
        }
        return $cacheFile;
    }

    /**
     * Check if a cached file exists and is still valid.
     *
     * @param string $url
     * @return bool
     */
    public function isCached($url) {
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
     * @param string $url
     * @return string|false The cached content as a string, or false if not found, expired, or error.
     */
    public function getCache($url) {
        if (!$this->enabled) {
            return false;
        }

        $file = $this->getCacheFile($url);

        if (!file_exists($file)) {
            return false; // File does not exist, so it's a cache miss.
        }

        // Use @file_get_contents to suppress potential warnings if reading somehow fails
        // (though file_exists should prevent most "no such file" issues).
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
     * @param string $url
     * @param string $content
     * @return bool True on success, false on failure.
     */
    public function storeCache($url, $content) {
        if (!$this->enabled) {
            return false;
        }
        $file = $this->getCacheFile($url);
        // file_put_contents returns bytes written or false on failure
        return file_put_contents($file, $content) !== false;
    }

    /**
     * Invalidate cache for a specific URL.
     *
     * @param string $url
     * @return bool True if file was deleted or didn't exist, false on error.
     */
    public function clearCache($url) {
        $file = $this->getCacheFile($url);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true; // Already gone or never existed
    }

    /**
     * Clear all cached files recursively.
     * @return bool True on complete success, false if any file/dir couldn't be deleted.
     */
    public function clearAllCache() {
        $success = true;
        if (!is_dir($this->cacheDir)) {
            return true; // Nothing to clear if directory doesn't exist
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    
        foreach ($files as $fileinfo) {
            if ($fileinfo->isFile()) {
                if (!unlink($fileinfo->getRealPath())) {
                    $success = false;
                    error_log("Cache Error: Failed to delete file: " . $fileinfo->getRealPath());
                }
            } elseif ($fileinfo->isDir()) {
                // @rmdir to suppress warning if dir is not empty (though CHILD_FIRST should handle this)
                if (!@rmdir($fileinfo->getRealPath())) {
                    $success = false;
                    error_log("Cache Error: Failed to remove directory: " . $fileinfo->getRealPath());
                }
            }
        }
        
        // Finally, try to remove the root cache directory itself if it's empty
        // Only attempt if it still exists and is empty
        if (is_dir($this->cacheDir) && count(glob($this->cacheDir . '/*')) === 0) {
            if (!@rmdir($this->cacheDir)) {
                $success = false;
                error_log("Cache Error: Failed to remove root cache directory: " . $this->cacheDir);
            }
        }
        return $success;
    }
}