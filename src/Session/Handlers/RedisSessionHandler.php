<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Session\Handlers;

use SessionHandlerInterface;

/**
 * Redis-backed session handler using phpredis.
 *
 * Responsibilities:
 * - Store session payload in Redis with a key prefix and TTL
 * - Use SETEX to ensure automatic expiration
 * - Avoid hard type declarations for Redis to satisfy IDEs when extension is missing
 */
final class RedisSessionHandler implements SessionHandlerInterface
{
    /** @var object Underlying phpredis client instance */
    private $redis;

    /** @var string Key prefix for all session entries */
    private string $prefix;

    /** @var int Time-to-live in seconds for session entries */
    private int $ttl;

    /**
     * @param string $host Redis host (default: 127.0.0.1)
     * @param int $port Redis port (default: 6379)
     * @param float $timeout Connection timeout in seconds (default: 1.5)
     * @param mixed $auth Password or array for ACL auth (optional)
     * @param int $database Redis database index (default: 0)
     * @param string $prefix Key prefix for session entries (default: sess:)
     * @param int $ttl TTL in seconds for session data (default: 1440)
     */
    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        float $timeout = 1.5,
        $auth = null,
        int $database = 0,
        string $prefix = 'sess:',
        int $ttl = 1440
    ) {
        // Avoid hard typehint to keep IDE analyzers happy when extension is missing
        $className = '\Redis';
        $this->redis = new $className();
        $this->redis->connect($host, $port, $timeout);
        if ($auth !== null && $auth !== '') {
            $this->redis->auth($auth);
        }
        if ($database > 0) {
            $this->redis->select($database);
        }
        $this->prefix = $prefix;
        $this->ttl = $ttl;
    }

    private function key(string $id): string
    {
        return $this->prefix . $id;
    }

    public function open($savePath, $sessionName): bool { return true; }
    
    public function close(): bool { return true; }

    public function read($id): string|false
    {
        $data = $this->redis->get($this->key($id));
        return $data === false ? '' : (string) $data;
    }

    public function write($id, $data): bool
    {
        return (bool) $this->redis->setex($this->key($id), $this->ttl, (string) $data);
    }

    public function destroy($id): bool
    {
        return (bool) $this->redis->del($this->key($id));
    }

    public function gc($max_lifetime): int|false
    {
        // Redis handles expiration automatically via TTL
        return 0;
    }
}