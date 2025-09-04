<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Database\Console\Commands\Traits\CommandTrait;

class KeyCommand
{   
    use CommandTrait;

    /**
     * Default entry when running: php tame key
     */
    public function handle(array $args = [], array $options = []): int
    {
        echo "Usage:\n";
        echo "  php tame key:generate [--force]\n\n";
        return 0;
    }

    /**
     * Subcommand: php tame key:generate
     */
    public function generate(array $args = [], array $options = []): int
    {
        // Ensure env booted
        Env::boot();
        Env::loadOrFail();


        dd(
            'sss'
        );

        // Initialize DB manager if required by your app flow
        $db = new DatabaseManager();

        // Example key generation (do the actual persistence in your implementation)
        $random = base64_encode(random_bytes(32));
        $key = 'base64:' . $random;

        $forced = isset($options['force']) && $options['force'] !== false;

        echo "Application key generated: {$key}\n";
        if ($forced) {
            echo "--force detected: would overwrite existing key (implement persistence).\n";
        } else {
            echo "Note: persist this key to your .env (APP_KEY) as needed.\n";
        }
        return 0;
    }

    /**
     * Flag method: --force
     * This will be invoked automatically when passing --force to any key command
     */
    public function force(array $args = [], array $options = []): int
    {
        // This can toggle internal state or validations as needed
        // Kept as no-op here to demonstrate flag -> method routing
        return 0;
    }
}