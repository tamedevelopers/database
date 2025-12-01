<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Database\Session\SessionManager;


/**
 * Native PHP session implementation for SessionInterface.
 * Supports optional configuration via configure() before start().
 */
class Session extends SessionManager
{
    /**
     * Configure session behavior (call before start()).
     * Supported keys: name, expire, path, cookie (array params for session_set_cookie_params), env
     * - path: when provided, ensures directory exists and sets session.save_path
     * - expire: accepts integer seconds
     */
    public function configure(array $config = []): void
    {
        $this->config = array_merge($this->config, [
            'driver' => 'file'
        ], $config);
    }

}