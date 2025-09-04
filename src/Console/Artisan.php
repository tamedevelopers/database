<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console;

use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Database\Console\Commands\KeyCommand;
use Tamedevelopers\Database\Console\Commands\ScaffoldCommand;
use Tamedevelopers\Database\Console\Commands\MigrationCommand;
use Tamedevelopers\Database\Console\Commands\Traits\CommandListTrait;

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
    use CommandListTrait;

    /**
     * Registered commands map
     * @var array<string, array{instance?: object, handler?: callable, description: string}>
     */
    protected static array $commands = [];

    public function __construct()
    {
        // Ensure environment variables are loaded before accessing them
        Manager::startEnvIFNotStarted();
        
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
            self::$commands[$name] = [
                'instance' => $handler,
                'description' => $description,
            ];
            return;
        }

        // Fallback to callable handler registration
        self::$commands[$name] = [
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

        if (!isset(self::$commands[$base])) {
            Logger::error("Invalid command: {$commandInput}\n\n");
            $this->renderList();
            return 1;
        }

        $entry = self::$commands[$base];

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
        echo "Tamedevelopers Database CLI\n\n";
        echo "Usage:\nphp tame <command>[:subcommand] [options]\n\n";

        $grouped = $this->buildGroupedCommandList(self::$commands);

        echo "Available commands:\n";
        // Root commands first
        if (isset($grouped['__root'])) {
            foreach ($grouped['__root'] as $cmd => $desc) {
                $label = str_pad($cmd, 40, ' ');
                echo "  {$label} " . ($desc ?: '') . "\n";
            }
            echo "\n";
            unset($grouped['__root']);
        }

        // Then grouped by base (e.g., auth, cache, migrate:*)
        foreach ($grouped as $group => $items) {
            echo $group . "\n";
            foreach ($items as $cmd => $desc) {
                $label = str_pad($cmd, 40, ' ');
                echo "  {$label} " . ($desc ?: '') . "\n";
            }
        }
    }

    /**
     * Split command into base and optional subcommand
     * @return array{0:string,1:?string}
     */
    private function splitCommand(string $input): array
    {
        $parts = explode(':', $input, 2);
        $base = $parts[0] ?? '';
        $sub = $parts[1] ?? null;
        return [$base, $sub];
    }

    /**
     * Parse raw args into positionals and options/flags.
     * Options like: --name=value, --name value, --flag
     * Short flags like -abc will be split into a,b,c set to true
     *
     * @param array $args
     * @return array{0:array,1:array<string,mixed>}
     */
    private function parseArgs(array $args): array
    {
        $positionals = [];
        $options = [];

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];

            if (str_starts_with($arg, '--')) {
                $eqPos = strpos($arg, '=');
                if ($eqPos !== false) {
                    $key = substr($arg, 2, $eqPos - 2);
                    $val = substr($arg, $eqPos + 1);
                    $options[$key] = $val;
                } else {
                    $key = substr($arg, 2);
                    // If next token exists and is not an option, treat as value
                    $next = $args[$i + 1] ?? null;
                    if ($next !== null && !str_starts_with((string)$next, '-')) {
                        $options[$key] = $next;
                        $i++; // consume next
                    } else {
                        $options[$key] = true;
                    }
                }
            } elseif (str_starts_with($arg, '-')) {
                // Short flags cluster: -abc => a=true, b=true, c=true
                $cluster = substr($arg, 1);
                foreach (str_split($cluster) as $ch) {
                    $options[$ch] = true;
                }
            } else {
                $positionals[] = $arg;
            }
        }

        return [$positionals, $options];
    }

    /**
     * Convert an option name (e.g., "seed" or "database") to a method name (foo-bar => fooBar)
     */
    private function optionToMethodName(string $option): string
    {
        $name = ltrim($option, '-');
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name ?? '');
        $parts = preg_split('/[-_]/', (string)$name) ?: [];
        $camel = '';
        foreach ($parts as $idx => $p) {
            $p = strtolower($p);
            $camel .= $idx === 0 ? $p : ucfirst($p);
        }
        return $camel ?: 'handle';
    }

    /**
     * Invoke a command method with flexible signature support.
     * Method may declare: fn():int, fn(array $args):int, fn(array $args, array $options):int
     */
    private function invokeCommandMethod(object $instance, string $method, array $args, array $options, ?string $invokedByFlag = null)
    {
        try {
            $ref = new \ReflectionMethod($instance, $method);
            $paramCount = $ref->getNumberOfParameters();
            if ($paramCount >= 2) {
                return $ref->invoke($instance, $args, $options);
            }
            if ($paramCount === 1) {
                return $ref->invoke($instance, $args);
            }
            return $ref->invoke($instance);
        } catch (\Throwable $e) {
            $flagInfo = $invokedByFlag ? " (from --{$invokedByFlag})" : '';
            fwrite(STDERR, "Error running {$method}{$flagInfo}: {$e->getMessage()}\n");
            return 1;
        }
    }

    /**
     * Introspect public methods (excluding magic/constructor) for hints
     */
    private function introspectPublicMethods(object $instance): string
    {
        try {
            $ref = new \ReflectionClass($instance);
            $methods = [];
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
                $name = $m->getName();
                if ($name === '__construct' || str_starts_with($name, '__')) {
                    continue;
                }
                $methods[] = $name;
            }
            sort($methods);
            return implode(', ', $methods);
        } catch (\Throwable $e) {
            return '';
        }
    }
    
}