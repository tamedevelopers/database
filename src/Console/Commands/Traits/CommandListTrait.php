<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands\Traits;

/**
 * Provides helpers to build an associative array of available commands
 * from the Artisan registry, including subcommand methods on command classes.
 */
trait CommandListTrait
{
    /**
     * Build a flat associative list of commands => description.
     * Includes base commands and public subcommand methods (e.g., foo:bar).
     *
     * @param array<string, array{instance?: object, handler?: callable, description: string}> $registry
     * @return array<string,string>
     */
    protected function buildCommandList(array $registry): array
    {
        $list = [];

        foreach ($registry as $name => $entry) {
            $desc = (string)($entry['description'] ?? '');
            $list[$name] = $desc;

            if (isset($entry['instance']) && is_object($entry['instance'])) {
                $instance = $entry['instance'];
                foreach ($this->introspectPublicMethodsArray($instance) as $method => $summary) {
                    if ($method === 'handle') {
                        continue;
                    }
                    $list[$name . ':' . $method] = $summary;
                }
            }
        }

        ksort($list, SORT_NATURAL | SORT_FLAG_CASE);
        return $list;
    }

    /**
     * Build a grouped list keyed by the base command name.
     * Root commands without a colon are under key '__root'.
     *
     * @param array<string, array{instance?: object, handler?: callable, description: string}> $registry
     * @return array<string, array<string,string>>
     */
    protected function buildGroupedCommandList(array $registry): array
    {
        $flat = $this->buildCommandList($registry);
        $grouped = [];
        foreach ($flat as $cmd => $desc) {
            $pos = strpos($cmd, ':');
            if ($pos === false) {
                $grouped['__root'][$cmd] = $desc;
            } else {
                $group = substr($cmd, 0, $pos);
                $grouped[$group][$cmd] = $desc;
            }
        }
        // Sort groups and each group's items
        ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($grouped as &$items) {
            ksort($items, SORT_NATURAL | SORT_FLAG_CASE);
        }
        return $grouped;
    }

    /**
     * Introspect public methods and return [methodName => summary] map.
     * Summary is derived from the method's PHPDoc first line when available.
     *
     * @return array<string,string>
     */
    private function introspectPublicMethodsArray(object $instance): array
    {
        $out = [];
        try {
            $ref = new \ReflectionClass($instance);
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
                $name = $m->getName();
                if ($name === '__construct' || str_starts_with($name, '__')) {
                    continue;
                }
                $summary = $this->extractDocSummary($m->getDocComment() ?: '') ?: '';
                $out[$name] = $summary;
            }
        } catch (\Throwable $e) {
            // ignore
        }
        ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
        return $out;
    }

    /**
     * Extract the first non-empty line from a PHPDoc block as summary.
     */
    private function extractDocSummary(string $doc): string
    {
        if ($doc === '') {
            return '';
        }
        $doc = preg_replace('/^\s*\/\*\*|\*\/\s*$/', '', $doc ?? '');
        $lines = preg_split('/\r?\n/', (string)$doc) ?: [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^\s*\*\s?/', '', $line ?? ''));
            if ($line !== '' && strpos($line, '@') !== 0) {
                return $line;
            }
        }
        return '';
    }
}