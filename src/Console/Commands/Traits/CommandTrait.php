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