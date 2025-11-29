<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Hash;
use Tamedevelopers\Database\Traits\AuthTrait;
use Tamedevelopers\Database\Traits\ExceptionTrait;

/**
 * Class Auth Manager
 *
 * A lightweight authentication manager that provides guard-based
 * authentication functionality similar to Laravel's Auth system.
 *
 * This implementation ensures the authenticated user data is:
 * - Stored internally (and visible in debugger/dump), but
 * - Only retrievable via the user() method (property access is blocked)
 * - Optionally persisted in session per guard
 */
class Auth
{
    use AuthTrait, ExceptionTrait;

    /**
     * Instance of Database Object
     *
     * @var \Tamedevelopers\Database\Connectors\Connector
     */
    protected $conn;

    /**
     * The authenticated user object (array form).
     *
     * - Kept protected to prevent direct external access
     * - Visible in debug output through __debugInfo()
     *
     * @var array|null
     */
    protected $user = null;

    /**
     * The table name associated with the current guard.
     *
     * @var string|null
     */
    protected $table;

    /**
     * Flag to proceed without password verification
     *
     * @var bool
     */
    protected $proceedWithoutPassword = false;

    /**
     * Session key used to store authenticated user data.
     *
     * @var string
     */
    protected static $session = 'tame_auth_user';

    /**
     * constructor.
     *
     * @param string|null $table
     * @param string|null $connection
     */
    public function __construct($table = null, $connection = null)
    {
        $this->table = $table;
        $this->conn = DB::connection($connection);

        // Try to populate in-memory user from session (if any)
        $this->hydrateFromSession();
    }

    /**
     * Set the authentication guard.
     *
     * @param string $table  The database table to authenticate against.
     * @param string|null $connection [optional]
     *
     * @return static
     */
    public static function guard(string $table, $connection = null): static
    {
        return new static($table, $connection);
    }

    /**
     * Attempt to authenticate a user with given credentials.
     *
     * Note: This only validates credentials and sets the in-memory user.
     * It DOES NOT persist to session. Call login($userData) explicitly to persist.
     *
     * @param array $credentials  The credentials (e.g., ['email' => ..., 'password' => ...]).
     * @return bool  True if authentication is successful, otherwise false.
     */
    public function attempt(array $credentials): bool
    {
        // Retrieve the user record from DB based on credentials
        $record = $this->retrieveByCredentials($credentials);

        if (! $record) {
            return false;
        }

        // Normalize to array
        $recordArray = is_array($record) ? $record 
                    : (method_exists($record, 'toArray') 
                    ? $record->toArray() : (array) $record);

        // Verify password against hashed password in database
        $plain = $credentials['password'] ?? null;
        $hashed = $recordArray['password'] ?? null; 

        // Check password values
        $isPasswordNull = !is_null($plain) && !is_null($hashed);

        if (($isPasswordNull && Hash::check($plain, $hashed)) || $this->proceedWithoutPassword) {
            // Set authenticated user internally ONLY (no session persistence)
            $this->user = $recordArray;
            return true;
        }

        return false;
    }

    /**
     * Log the user in (set internal state and persist to session).
     *
     * @param array|null $userData
     * @param bool $persist  When true, also store in session
     * @return void
     */
    public function login($userData = null, bool $persist = true): void
    {
        if(!is_array($userData)){
            return;
        }

        $this->user = $userData;

        if ($persist) {
            $this->putUserInSession($userData);
        }
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user (from memory or session).
     *
     * @return array|null  The authenticated user or null if not logged in.
     */
    public function user(): ?array
    {
        if ($this->user !== null) {
            return $this->user;
        }

        // As a fallback, try to rehydrate from session lazily
        $this->hydrateFromSession();
        
        return $this->user;
    }

    /**
     * Determine if the current guard has an authenticated user.
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the authenticated user's id (or custom key) if available.
     *
     * @param string $key
     * @return mixed|null
     */
    public function id(string $key = 'id')
    {
        $user = $this->user();
        return is_array($user) ? ($user[$key] ?? null) : null;
    }

    /**
     * Log out the currently authenticated user.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->user = null;
        $this->forgetUserFromSession();
    }

    /**
     * Magic: block direct property access to 'user' and force using user().
     * Visible in dumps via __debugInfo(), but not readable directly.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name === 'user') {
            $className = get_class($this);
            try {
                throw new \RuntimeException("
                Cannot access protected property {$className}::$$name. Use user() method instead.");
            } catch (\Throwable $th) {
                $this->errorException($th);
            }
        }
        return null;
    }

    /**
     * Magic: prevent isset($auth->user) from exposing internals.
     */
    public function __isset(string $name): bool
    {
        if ($name === 'user') {
            return false;
        }
        return isset($this->$name);
    }

    /**
     * Magic: customize what is displayed during var-dump/dd().
     * We include a sanitized snapshot of the user without sensitive fields.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'guard' => $this->table,
            'connected' => (bool) $this->conn,
            'authenticated' => $this->check(),
            // show sanitized user so dumps reveal state, but never raw password
            'user' => $this->sanitizedUserForDebug(),
        ];
    }

}