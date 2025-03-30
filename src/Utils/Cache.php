<?php
// src/Utils/Cache.php
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
            mkdir($this->cacheDir, 0755, true);
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
                mkdir($dir, 0755, true);
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
        return file_exists($file) && (time() - filemtime($file) < $this->ttl);
    }

    /**
     * Retrieve cached content.
     *
     * @param string $url
     * @return string|false
     */
    public function getCache($url) {
        $file = $this->getCacheFile($url);
        return file_get_contents($file);
    }

    /**
     * Store content in cache.
     *
     * @param string $url
     * @param string $content
     */
    public function storeCache($url, $content) {
        $file = $this->getCacheFile($url);
        file_put_contents($file, $content);
    }

    /**
     * Invalidate cache for a specific URL.
     *
     * @param string $url
     */
    public function clearCache($url) {
        $file = $this->getCacheFile($url);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cached files recursively.
     */
    public function clearAllCache() {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
    
        foreach ($files as $fileinfo) {
            if ($fileinfo->isFile()) {
                // Delete all files, not just .html files
                unlink($fileinfo->getRealPath());
            } elseif ($fileinfo->isDir()) {
                // Remove the directory only after it is empty
                @rmdir($fileinfo->getRealPath());
            }
        }
    
        // Finally, remove the root cache directory itself if it's empty
        @rmdir($this->cacheDir);
    }
}
