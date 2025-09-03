<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;


trait DBImportTrait{
    
    /**
     * Check if data is Readable
     * 
     * @param array|null $readFile
     * @return bool
    */
    protected function isReadable(?array $readFile = null)
    {
        if(!is_array($readFile) || count($readFile) === 0){
            return false;
        }
        return true;
    }

    /**
     * Check if sql string is a comment
     * 
     * @param string|null $string
     * @return bool
    */
    protected function isComment($string = null)
    {
        // if first two is --
        // or it's empty
        if(substr($string, 0, 2) === '--' || $string === ''){
            return true;
        }
        return false;
    }

    /**
     * Check if sql string is a Query
     * 
     * @param string|null $string
     * @return bool
    */
    protected function isQuery($string = null)
    {
        // check is last char is `;`
        if(substr(trim($string), -1, 1) == ';'){
            return true;
        }
        return false;
    }

    /**
     * Split SQL into individual statements while respecting strings and comments.
     * - Handles single quotes '...'
     * - Handles double quotes "..."
     * - Handles backticks `...`
     * - Skips semicolons inside quotes and comments
     * - Ignores -- line comments and # comments
     * - Handles /* block comments *\/ safely
     */
    protected function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';

        $inSingle = false;   // '
        $inDouble = false;   // "
        $inBacktick = false; // `
        $inLineComment = false; // -- or # until end of line
        $inBlockComment = false; // /* ... */

        $len = strlen($sql);
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            $next = $i + 1 < $len ? $sql[$i + 1] : '';

            // Handle end of line comment
            if ($inLineComment) {
                $buffer .= $ch;
                if ($ch === "\n") {
                    $inLineComment = false;
                }
                continue;
            }

            // Handle end of block comment
            if ($inBlockComment) {
                $buffer .= $ch;
                if ($ch === '*' && $next === '/') {
                    $buffer .= $next;
                    $i++;
                    $inBlockComment = false;
                }
                continue;
            }

            // Start of line comments: -- ... or # ... (only if not in any quote)
            if (!$inSingle && !$inDouble && !$inBacktick) {
                if ($ch === '-' && $next === '-') {
                    $inLineComment = true;
                    $buffer .= $ch;
                    continue;
                }
                if ($ch === '#') {
                    $inLineComment = true;
                    $buffer .= $ch;
                    continue;
                }
                if ($ch === '/' && $next === '*') {
                    $inBlockComment = true;
                    $buffer .= $ch;
                    continue;
                }
            }

            // Toggle quotes
            if (!$inDouble && !$inBacktick && $ch === "'" && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $inSingle = !$inSingle;
                $buffer .= $ch;
                continue;
            }
            if (!$inSingle && !$inBacktick && $ch === '"' && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $inDouble = !$inDouble;
                $buffer .= $ch;
                continue;
            }
            if (!$inSingle && !$inDouble && $ch === '`' && ($i === 0 || $sql[$i - 1] !== '\\')) {
                $inBacktick = !$inBacktick;
                $buffer .= $ch;
                continue;
            }

            // Statement delimiter
            if ($ch === ';' && !$inSingle && !$inDouble && !$inBacktick) {
                $statements[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $ch;
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        // Trim and filter empties
        return array_values(array_filter(array_map('trim', $statements), fn($s) => $s !== ''));
    }

}