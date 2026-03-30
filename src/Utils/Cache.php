<?php
declare(strict_types=1);

namespace App;

use Redis;
use RedisException;

class Cache
{
    private Redis $redis;
    private int $defaultTtl = 3600; // Default 1 hour

    public function __construct(string $host = 'localhost', int $port = 6379, int $defaultTtl = 3600)
    {
        $this->redis = new Redis();
        try {
            $this->redis->connect($host, $port);
        } catch (RedisException $e) {
            error_log("Redis Connection Error: " . $e->getMessage());
            throw $e;
        }
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Get a value from cache.
     *
     * @param string $key Cache key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        try {
            $value = $this->redis->get($key);
            return $value !== false ? unserialize($value) : null;
        } catch (RedisException $e) {
            error_log("Cache Get Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set a value in cache.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds (optional)
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        try {
            $ttl = $ttl ?? $this->defaultTtl;
            return $this->redis->setex($key, $ttl, serialize($value));
        } catch (RedisException $e) {
            error_log("Cache Set Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a key from cache.
     *
     * @param string $key Cache key
     * @return bool
     */
    public function delete(string $key): bool
    {
        try {
            return (bool)$this->redis->del($key);
        } catch (RedisException $e) {
            error_log("Cache Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all cache.
     *
     * @return bool
     */
    public function flush(): bool
    {
        try {
            return $this->redis->flushDb();
        } catch (RedisException $e) {
            error_log("Cache Flush Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if key exists in cache.
     *
     * @param string $key Cache key
     * @return bool
     */
    public function exists(string $key): bool
    {
        try {
            return (bool)$this->redis->exists($key);
        } catch (RedisException $e) {
            error_log("Cache Exists Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment a numeric cache value.
     *
     * @param string $key Cache key
     * @param int $increment Amount to increment by
     * @return int|false
     */
    public function increment(string $key, int $increment = 1): int|false
    {
        try {
            return $this->redis->incrBy($key, $increment);
        } catch (RedisException $e) {
            error_log("Cache Increment Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrement a numeric cache value.
     *
     * @param string $key Cache key
     * @param int $decrement Amount to decrement by
     * @return int|false
     */
    public function decrement(string $key, int $decrement = 1): int|false
    {
        try {
            return $this->redis->decrBy($key, $decrement);
        } catch (RedisException $e) {
            error_log("Cache Decrement Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Close Redis connection.
     *
     * @return void
     */
    public function close(): void
    {
        try {
            $this->redis->close();
        } catch (RedisException $e) {
            error_log("Cache Close Error: " . $e->getMessage());
        }
    }
}
