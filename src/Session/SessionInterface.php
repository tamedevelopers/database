<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Session;

/**
 * Defines a lightweight, framework-agnostic session contract.
 */
interface SessionInterface
{
    /**
     * Start the session if not already started.
     * @return void
     */
    public function start(): void;

    /**
     * Get the current session ID.
     * @return string|null The session ID or null if none.
     */
    public function id(): ?string;

    /**
     * Regenerate the session ID.
     * @param bool $deleteOldSession When true, deletes the old session.
     * @return bool True on success, false otherwise.
     */
    public function regenerate(bool $deleteOldSession = false): bool;

    /**
     * Retrieve a value from the session.
     * @param string $key The key to retrieve.
     * @param mixed $default Default value if key is missing.
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Store a value in the session.
     * @param string $key The key to store under.
     * @param mixed $value The value to store.
     * @return void
     */
    public function put(string $key, $value): void;

    /**
     * Determine if the given key exists in the session.
     * @param string $key The key to check.
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove a key from the session.
     * @param string $key The key to remove.
     * @return void
     */
    public function forget(string $key): void;

    /**
     * Get all session data.
     * @return array
     */
    public function all(): array;

    /**
     * Destroy the session and clear stored data.
     * If a key is provided, only that key is removed.
     * @param string|null $key
     * @return void
     */
    public function destroy(?string $key = null): void;
}