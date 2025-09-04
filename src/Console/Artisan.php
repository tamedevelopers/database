<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console;

use Tamedevelopers\Database\Console\Commands\KeyCommand;
use Tamedevelopers\Database\Console\Commands\ScaffoldCommand;
use Tamedevelopers\Database\Console\Commands\MigrationCommand;
use Tamedevelopers\Database\Console\Commands\Traits\CommandTrait;

/**
 * Minimal artisan-like dispatcher for Tamedevelopers Database
 *
 * Supports Laravel-like syntax:
 *   php tame <command>[:subcommand] [--flag] [--option=value]
 * Examples:
 *   php tame key:generate
 *   php tame migrate:fresh --seed --database=mysql
 */
class Artisan
{
    use CommandTrait;
    
    /**
     * Registered commands map
     * @var array<string, array{instance?: object, handler?: callable, description: string}>
     */
    protected array $commands = [];


    public function __construct()
    {
        // Register built-in commands with class instances (enables subcommands and flags-as-methods)
        $this->register('scaffold', new ScaffoldCommand(), 'Generate default scaffolding');
        $this->register('migrate', new MigrationCommand(), 'Run database migrations');
        $this->register('key', new KeyCommand(), 'Set or manage the application key');
    }

    /**
     * Register a command by name with description
     *
     * @param string $name
     * @param callable|object $handler  Either a callable or a command class instance
     * @param string $description       Short description for `list`
     */
    public function register(string $name, $handler, string $description = ''): void
    {
        if (\is_object($handler) && !\is_callable($handler)) {
            $this->commands[$name] = [
                'instance' => $handler,
                'description' => $description,
            ];
            return;
        }

        // Fallback to callable handler registration
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
        // argv: [tame, command, ...args]
        // In PHP CLI, $argv[0] is the script name (tame), so command starts at index 1
        $commandInput = $argv[1] ?? 'list';
        $rawArgs = array_slice($argv, 2);

        // dd(
        //     $argv,
        //     $commandInput,
        //     $rawArgs
        // );

        if ($commandInput === 'list') {
            $this->renderList();
            return 0;
        }

        // Parse base and optional subcommand: e.g., key:generate -> [key, generate]
        [$base, $sub] = $this->splitCommand($commandInput);

        if (!isset($this->commands[$base])) {
            fwrite(STDERR, "Invalid command: {$commandInput}\n\n");
            $this->renderList();
            return 1;
        }

        $entry = $this->commands[$base];

        // Parse flags/options once and pass where applicable
        [$positionals, $options] = $this->parseArgs($rawArgs);

        // If registered with a class instance, we support subcommands and flag-to-method routing
        if (isset($entry['instance']) && \is_object($entry['instance'])) {
            $instance = $entry['instance'];

            // Resolve primary method to call
            $primaryMethod = $sub ?: 'handle';
            if (!method_exists($instance, $primaryMethod)) {
                fwrite(STDERR, "Invalid command/subcommand: {$commandInput}\n");
                // Show small hint for available methods on the instance (public only)
                $hints = $this->introspectPublicMethods($instance);
                if ($hints !== '') {
                    fwrite(STDERR, "Available methods: {$hints}\n\n");
                } else {
                    fwrite(STDERR, "\n");
                }
                $this->renderList();
                return 1;
            }

            $exitCode = (int) ($this->invokeCommandMethod($instance, $primaryMethod, $positionals, $options) ?? 0);

            // Route flags as methods on the same instance
            $invalidFlags = [];
            foreach ($options as $flag => $value) {
                $method = $this->optionToMethodName($flag);
                // Skip if this flag matches the already-run primary method
                if ($method === $primaryMethod) {
                    continue;
                }
                if (method_exists($instance, $method)) {
                    $this->invokeCommandMethod($instance, $method, $positionals, $options, $flag);
                } else {
                    $invalidFlags[] = $flag;
                }
            }

            if (!empty($invalidFlags)) {
                fwrite(STDERR, "Invalid option/method: --" . implode(', --', $invalidFlags) . "\n");
            }

            return $exitCode;
        }

        // Fallback: callable handler (no subcommands/flags routing)
        if (isset($entry['handler']) && \is_callable($entry['handler'])) {
            $handler = $entry['handler'];
            return (int) ($handler($rawArgs) ?? 0);
        }

        fwrite(STDERR, "Command not properly registered: {$commandInput}\n");
        return 1;
    }

    /**
     * Render list of available commands
     */
    private function renderList(): void
    {
        $names = array_keys($this->commands);
        sort($names);
        echo "Tamedevelopers Database CLI\n\n";
        echo "Usage: \nphp tame <command>[:subcommand] [options]\n\n";
        echo "Available commands:\n";
        foreach ($names as $name) {
            $desc = $this->commands[$name]['description'] ?? '';
            echo "  - {$name}" . ($desc !== '' ? "  {$desc}" : "") . "\n";
        }
    }

}