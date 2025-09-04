<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands\Traits;


/**
 * Example: php database scaffold --name=posts
 * This can run background-safe operations (no manual refresh required)
 */
trait CommandTrait{


    

    /**
     * Get an option value from a list of arguments.
     *
     * @param array $args List of arguments passed to the script
     * @param string $key Option key name (--option-name)
     * @return string|null The option value or null if not found
     */
    protected function getOption(array $args, string $key): ?string
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, "--{$key}=")) {
                return substr($arg, strlen($key) + 3);
            }
        }
        return null;
    }

}