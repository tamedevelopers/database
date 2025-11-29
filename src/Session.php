<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Database\Session\SessionInterface;


/**
 * Native PHP session implementation for SessionInterface.
 * Supports optional configuration via configure() before start().
 */
final class Session implements SessionInterface
{
    /** @var array<string,mixed> */
    private array $config = [];

    /**
     * Configure session behavior (call before start()).
     * Supported keys: name, expire, path, cookie (array params for session_set_cookie_params), env
     * - path: when provided, ensures directory exists and sets session.save_path
     * - expire: accepts integer seconds
     */
    public function configure(array $config = []): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /** @inheritDoc */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $cfg = $this->applyDefaults($this->config);

        // Name
        if (!empty($cfg['name']) && is_string($cfg['name'])) {
            @session_name($cfg['name']);
        }

        // Cookie params
        if (!empty($cfg['expire'])) {
            $lifetime = (int) $cfg['expire'];
            $cookie = $cfg['cookie'] ?? [
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ];
            @session_set_cookie_params($cookie);
            @ini_set('session.gc_maxlifetime', (string) $lifetime);
            @ini_set('session.gc_probability', '1');
            @ini_set('session.gc_divisor', '100');
        }

        // Path (create if missing)
        if (!empty($cfg['path'])) {
            $path = (string) $cfg['path'];
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true);
            }
            if (File::isDirectory($path)) {
                @ini_set('session.save_path', $path);
            }
        }

        @session_start();
    }

    /** @inheritDoc */
    public function id(): ?string
    {
        return session_id() ?: null;
    }

    /** @inheritDoc */
    public function regenerate(bool $deleteOldSession = false): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->start();
        }
        return @session_regenerate_id($deleteOldSession);
    }

    /** @inheritDoc */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /** @inheritDoc */
    public function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** @inheritDoc */
    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION ?? []);
    }

    /** @inheritDoc */
    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** @inheritDoc */
    public function all(): array
    {
        return (array) ($_SESSION ?? []);
    }

    /** @inheritDoc */
    public function destroy(?string $key = null): void
    {
        if ($key !== null) {
            unset($_SESSION[$key]);
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_unset();
            @session_destroy();
        }
    }

    /**
     * Apply default values similar to legacy Configuration::setSession
     * - expire: default 10 days (in seconds)
     * - path: defaults to storage_path('session') if available
     */
    private function applyDefaults(array $config): array
    {
        $defaults = [
            'name' => null,
            'expire' => 10 * 24 * 60 * 60,
            'path' => storage_path('session'),
            'cookie' => null,
            'env' => env('APP_ENV', 'production'),
        ];

        return array_merge($defaults, $config);
    }
}