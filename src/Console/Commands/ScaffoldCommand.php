<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Support\Env;

/**
 * Example: php database scaffold --name=posts
 * This can run background-safe operations (no manual refresh required)
 */
class ScaffoldCommand
{
    /**
     * Handle the command
     * @param array $args
     * @return int exit code
     */
    public function handle(array $args)
    {
        // Basic args parsing: --name=ModelName
        $name = $this->getOption($args, 'name') ?? 'example';

        // Ensure env and logger are booted
        Env::boot();
        Env::loadOrFail();

        // Example: ensure connection booted (if your manager requires it)
        // Adjust to your initialization flow if different
        $db = new DatabaseManager();

        
    }

    /**
     * Get an option value from a list of arguments.
     *
     * @param array $args List of arguments passed to the script
     * @param string $key Option key name (--option-name)
     * @return string|null The option value or null if not found
     */
    private function getOption(array $args, string $key): ?string
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, "--{$key}=")) {
                return substr($arg, strlen($key) + 3);
            }
        }
        return null;
    }
}