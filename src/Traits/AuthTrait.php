<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;


/**
 * 
 * @property mixed $session
 */
trait AuthTrait
{
    // --------------------
    // Internal helpers
    // --------------------

    /** Ensure session is started. */
    protected function ensureSessionStarted(): void
    {
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    /** Persist user to session, namespaced by guard (table). */
    protected function putUserInSession(array $user): void
    {
        $this->ensureSessionStarted();
        // Avoid storing password hash in session
        $toStore = $user;
        unset($toStore['password']);

        $_SESSION[static::$session] = $_SESSION[static::$session] ?? [];
        $key = $this->table ?: '_default_';
        $_SESSION[static::$session][$key] = $toStore;
    }

    /** Load user from session into memory, if present. */
    protected function hydrateFromSession(): void
    {
        $this->ensureSessionStarted();

        $key = $this->table ?: '_default_';
        if (isset($_SESSION[static::$session][$key]) && is_array($_SESSION[static::$session][$key])) {
            $this->user = $_SESSION[static::$session][$key];
        }
    }

    /** Remove persisted user for this guard from session. */
    protected function forgetUserFromSession(): void
    {
        $this->ensureSessionStarted();
        $key = $this->table ?: '_default_';
        if (isset($_SESSION[static::$session][$key])) {
            unset($_SESSION[static::$session][$key]);
        }
    }

    /**
     * Produce a sanitized user array for debug output (mask sensitive fields).
     */
    protected function sanitizedUserForDebug(): ?array
    {
        $u = $this->user();
        if (!is_array($u)) {
            return null;
        }

        $masked = $u;
        if (isset($masked['password']) && is_string($masked['password'])) {
            $masked['password'] = $this->maskString($masked['password']);
        }
        if (isset($masked['remember_token']) && is_string($masked['remember_token'])) {
            $masked['remember_token'] = $this->maskString($masked['remember_token']);
        }
        return $masked;
    }

    /** Basic middle-mask helper for sensitive strings */
    protected function maskString(string $value, int $keepStart = 3, int $keepEnd = 2): string
    {
        $len = strlen($value);
        if ($len <= ($keepStart + $keepEnd)) {
            return str_repeat('*', max(0, $len));
        }
        
        return substr($value, 0, $keepStart) 
                . str_repeat('*', $len - ($keepStart + $keepEnd)) 
                . substr($value, -$keepEnd);
    }

    /**
     * Optionally change the base session key used for persisting auth state.
     *
     * @param string|null $key  When null, returns the current key
     * @return string  The active session key
     */
    protected function sessionKey(?string $key = null): string
    {
        if ($key !== null && $key !== '') {
            static::$session = $key;
        }
        
        return static::$session;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials  The login credentials (excluding password check).
     * @return mixed  Returns the user record if found, otherwise null.
     */
    protected function retrieveByCredentials(array $credentials)
    {
        $this->assertGuardSet();
        $this->assertTableExists();
        $this->assertPasswordPresent($credentials);

        $query = $this->credentialsWithoutPassword($credentials);
        
        return $this->fetchFirstBy($query);
    }

    /**
     * Retrieve a user by primary key (or custom key).
     *
     * @param mixed $id
     * @param string $key
     * @return mixed|null
     */
    protected function retrieveById($id, string $key = 'id')
    {
        $this->assertGuardSet();
        $this->assertTableExists();
        return $this->fetchFirstBy([$key => $id]);
    }

    /**
     * Fetch the first record matching the given conditions.
     *
     * @param array $conditions
     * @return mixed|null
     */
    protected function fetchFirstBy(array $conditions)
    {
        $db = $this->conn->table($this->table);
        
        foreach ($conditions as $column => $value) {
            $db->where($column, $value);
        }

        return $db->first();
    }

    /** Remove password from credentials before querying. */
    protected function credentialsWithoutPassword(array $credentials): array
    {
        $query = $credentials;
        unset($query['password']);

        return $query;
    }

    /** Ensure a guard/table is set. */
    protected function assertGuardSet(): void
    {
        if (!$this->table) {
            try {
                throw new \RuntimeException("No guard set. Call Auth::guard('table') first.");
            } catch (\Throwable $th) {
                $this->errorException($th);
            }
        }
    }

    /** Ensure the target table exists. */
    protected function assertTableExists(): void
    {
        if (!$this->conn->tableExists($this->table)) {
            try {
                throw new \RuntimeException("Auth guard [{$this->table}] is not defined.");
            } catch (\Throwable $th) {
                $this->errorException($th);
            }
        }
    }

    /** Ensure password is present in the credentials array. */
    protected function assertPasswordPresent(array $credentials): void
    {
        if (!isset($credentials['password'])) {
            try {
                throw new \RuntimeException("Password field ['password'] is required in credentials.");
            } catch (\Throwable $th) {
                $this->errorException($th);
            }
        }
    }
}