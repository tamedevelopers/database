<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Session;

use PDO;
use Redis;
use SessionHandlerInterface;
use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Tame;
use Tamedevelopers\Support\Time;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Database\Capsule\AppManager;
use Tamedevelopers\Database\Session\SessionInterface;
use Tamedevelopers\Database\Session\Handlers\RedisSessionHandler;
use Tamedevelopers\Database\Session\Handlers\DatabaseSessionHandler;

/**
 * Configurable session manager supporting file, database, and redis drivers.
 *
 * Responsibilities:
 * - Configure PHP sessions based on a chosen driver
 * - For file driver, ensure the directory exists (defaults to storage_path('session'))
 * - For database driver, install a PDO-backed handler
 * - For redis driver, install a phpredis-backed handler with TTL
 * - Provide a simple, framework-agnostic SessionInterface implementation
 */
class SessionManager implements SessionInterface
{
    /** @var array<string,mixed> Resolved session configuration */
    protected array $config;

    /** @var \Tamedevelopers\Database\Connectors\Connector> Instance of Database Object*/
    private $conn;

    /** @var mixed Instance of Database Object */
    public $db;

    /**
     * @param array<string,mixed> $config
     *  - driver: file|database|redis (default: file)
     *  - lifetime: int seconds (optional, default from ini)
     *  - connection: Database connection name (for database driver)
     *  - redis: [host, port, timeout, auth, database, prefix, ttl]
     */
    public function __construct(array $config = [])
    {
        // Run heavy boot only once
        AppManager::bootLoader();

        // Always merge config for each new Session instance
        $this->config = array_merge(config('session'), $config);

        // Always normalize values
        $this->config['lifetime'] = Time::toSeconds($this->config['lifetime']);
        $this->config['cookie']   = Str::camel($this->config['cookie']);
    }

    /** @inheritDoc */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $path       = $this->config['files'];
        $driver     = $this->config['driver'];
        $lifetime   = $this->config['lifetime'];
        $name       = $this->config['cookie'];

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true);
        }

        if($name){
            session_name($name);
        }

        if ($lifetime) {
            @ini_set('session.gc_maxlifetime', $lifetime);
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 100);
        }

        switch ($driver) {
            case 'database':
                $this->configureDatabaseDriver($lifetime);
                break;
            case 'redis':
                $this->configureRedisDriver($lifetime);
                break;
            default:
                $this->configureFileDriver($path, $lifetime);
                break;
        }

        @session_start();
    }

    /** Configure file session driver */
    private function configureFileDriver($path, $lifetime): void
    {
        if (!is_writable($path)) {
            throw new \RuntimeException("Session path not writable: {$path}");
        }

        ini_set('session.save_handler', 'files');
        ini_set('session.save_path', realpath($path));

        // when setting the path that the cookie is valid for, 
        // always remember to have that trailing '/' at the end.
        // session_set_cookie_params(0, '/yourpath/'); - CORRECT
        // session_set_cookie_params(0, '/yourpath'); - INCORRECT
        $path = rtrim($this->config['path'], '/') . '/';

        // Set secure session cookie parameters
        session_set_cookie_params([
            'lifetime' => $lifetime,  
            'path' => $path,
            'domain' => $this->config['domain'], 
            'secure' => $this->config['secure'] ?: false, // Ensure HTTPS usage
            'httponly' => $this->config['http_only'], // Prevent JavaScript access
            'samesite' => $this->config['same_site']
        ]);
    }

    /** Configure database session driver */
    private function configureDatabaseDriver($lifetime = null): void
    {
        if(empty($this->config['connection'])){
            $this->config['connection'] = config('database.default');
        }

        $this->conn = DB::connection($this->config['connection']);
        $this->db = $this->conn->dbConnection();

        if(!$this->dbConnect()){
            throw new \InvalidArgumentException($this->db['message']);
        }

        $handler = new DatabaseSessionHandler(
            $this->conn, 
            $this->config['table'], 
            $lifetime ?? (int) ini_get('session.gc_maxlifetime')
        );
        
        $this->registerHandler($handler);
    }

    /** Configure redis session driver */
    private function configureRedisDriver(?int $lifetime = null): void
    {
        if (!class_exists('\Redis')) {
            throw new \RuntimeException('Redis extension (phpredis) is required for redis session driver.');
        }

        $cfg        = (array) ($this->config['redis'] ?? []);
        $host       = (string) ($cfg['host'] ?? '127.0.0.1');
        $port       = (int) ($cfg['port'] ?? 6379);
        $timeout    = isset($cfg['timeout']) ? (float) $cfg['timeout'] : 1.5;
        $auth       = $cfg['auth'] ?? null;
        $database   = (int) ($cfg['database'] ?? 0);
        $prefix     = (string) ($cfg['prefix'] ?? 'sess:');
        $ttl        = $lifetime ?? (int) ($cfg['ttl'] ?? (int) ini_get('session.gc_maxlifetime'));

        $handler = new RedisSessionHandler(
            $host, $port, $timeout, $auth, $database, $prefix, $ttl
        );
        
        $this->registerHandler($handler);
    }

    /** Register custom session handler */
    private function registerHandler(SessionHandlerInterface $handler): void
    {
        @session_set_save_handler($handler, true);
    }

    /** Get Session Configuration */
    public function config(): array
    {
        return $this->config;
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
        $value = $_SESSION[$key] ?? $default;

        if (!empty($this->config['encrypt']) && $this->config['encrypt'] == true) {
            $value = $this->decryptValue($value);
        }

        return $value;
    }

    /** @inheritDoc */
    public function put(string $key, $value): void
    {
        if (!empty($this->config['encrypt']) && $this->config['encrypt'] == true) {
            $value = $this->encryptValue($value);
        }

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
        $data = (array) ($_SESSION ?? []);

        if (!empty($this->config['encrypt']) && $this->config['encrypt'] == true) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->decryptValue($value);
            }
        }

        return $data;
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

    /** Check Database connection */
    private function dbConnect(): bool
    {
        $status = $this->db['status'] ?? null;
        
        return $status == Constant::STATUS_200;
    }

    /** Encrypt value using OpenSSL AES-256-CBC */
    private function encryptValue($value): string
    {
        $value = serialize($value);

        return Tame::encryptStr($value);
    }

    /** Decrypt session value */
    private function decryptValue(?string $payload)
    {
        if (empty($payload)) {
            return null;
        }

        $decrypted = Tame::decryptStr($payload);

        return unserialize($decrypted);
    }

}