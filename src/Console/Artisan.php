<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console;

use Tamedevelopers\Database\Console\Commands\KeyCommand;
use Tamedevelopers\Database\Console\Commands\ScaffoldCommand;
use Tamedevelopers\Database\Console\Commands\MigrationCommand;

/**
 * Minimal artisan-like dispatcher for Tamedevelopers Database
 */
class Artisan
{
    /**
     * Registered commands map
     * @var array<string, array{handler: callable, description: string}>
     */
    protected array $commands = [];

    public function __construct()
    {
        // Register built-in commands here with name and description
        $this->register('scaffold', [new ScaffoldCommand(), 'handle'], 'Generate default scaffolding');
        $this->register('migrate', [new MigrationCommand(), 'handle'], 'Run database migrations');
        $this->register('key', [new KeyCommand(), 'handle'], 'Set the application key');
    }

    // key:generate                          Set the application key

    /**
     * Register a command by name with description
     *
     * @param string   $name
     * @param \Callable|array $handler      function(array $args): int
     * @param string   $description  Short description for `list`
     */
    public function register(string $name, $handler, $description = ''): void
    {
        $this->commands[$name] = [
            'handler' => $handler,
            'description' => $description,
        ];
    }

    /**
     * Handle argv input and dispatch
     */
    public function run(array $argv): int
    {
        // argv: [php, tame, command, ...args]
        $command = $argv[2] ?? 'list';
        $args = array_slice($argv, 3);

        if ($command === 'list') {
            $this->renderList();
            return 0;
        }

        if (!isset($this->commands[$command])) {
            fwrite(STDERR, "Command not found: {$command}\n");
            $this->renderList();
            return 1;
        }

        $handler = $this->commands[$command]['handler'];
        return (int) ($handler($args) ?? 0);
    }

    /**
     * Render list of available commands
     */
    private function renderList(): void
    {
        $names = array_keys($this->commands);
        sort($names);
        echo "Tamedevelopers Database CLI\n\n";
        echo "Usage: \nphp tame <command> [options]\n\n";
        echo "Available commands:\n";
        foreach ($names as $name) {
            $desc = $this->commands[$name]['description'] ?? '';
            echo "  - {$name}" . ($desc !== '' ? "  {$desc}" : "") . "\n";
        }
    }
}