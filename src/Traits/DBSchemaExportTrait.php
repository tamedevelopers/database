<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Server;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Collections\Collection;

/**
 * Trait DBSchemaExportTrait
 *
 * Contains helper methods for DBSchemaExport functionality.
 */
trait DBSchemaExportTrait
{
    /** Resolve the default string length from Schema config (ORM_MAX_STRING_LENGTH) or fallback to 255. */
    protected function defaultStringLength(): int
    {
        return \defined('ORM_MAX_STRING_LENGTH') ? (int) ORM_MAX_STRING_LENGTH : 255;
    }

    /** Ensure database/migrations directory exists and return its path. */
    protected function ensureMigrationsDir(): array
    {
        // Prefer the same base used by MigrationTrait
        $base = Env::getServers('server');
        if (empty($base)) {
            // Fallback: current working directory as base
            $base = rtrim(getcwd(), '/\\') . DIRECTORY_SEPARATOR;
        }

        $databaseDir   = Server::pathReplacer($base . 'database' . DIRECTORY_SEPARATOR);
        $migrationsDir = Server::pathReplacer($databaseDir . 'migrations' . DIRECTORY_SEPARATOR);

        if (!File::isDirectory($databaseDir)) {
            if (!@mkdir($databaseDir, 0777) && !is_dir($databaseDir)) {
                $this->message = sprintf('Failed to create directory: %s', $databaseDir);
                return [null, ''];
            }
            // create a basic .gitignore similar to MigrationTrait
            $gitignore = $databaseDir . '.gitignore';
            if (!File::exists($gitignore)) {
                File::put($gitignore, "/database\n.env\n");
            }
        }

        if (!File::isDirectory($migrationsDir)) {
            File::makeDirectory($migrationsDir, 0777);
        }

        $this->migrationsDir = $migrationsDir;

        return [$migrationsDir, sprintf("- Writing migrations to: %s\n", $migrationsDir)];
    }

    /** @return bool */
    private function dbConnect()
    {
        return $this->db['status'] == Constant::STATUS_200;
    }

    /**
     * @param string $dbName
     * @return array<string>
     */
    protected function getTables(string $dbName): array
    {
        $rows = $this->conn->select('SHOW TABLES')->toArray();
        $key = 'tables_in_' . $dbName;

        return tcollect($rows)->pluck($key)->all();
    }

    /**
     * Build migration PHP for a single table using Blueprint methods.
     */
    protected function generateTableSchema(string $dbName, string $table): string
    {
        // Columns
        $columns = $this->conn->select("SHOW COLUMNS FROM `{$table}`");

        // Indexes (non-unique and unique)
        $indexes = $this->conn->select("SHOW INDEX FROM `{$table}`")->toArray();

        // Foreign keys
        $fks = $this->getForeignKeys($dbName, $table);

        // Build a map of single-column indexes so we can chain ->unique() / ->index() inline
        $singleIndexMap = [];
        $byIndexName = [];
        foreach ($indexes as $ix) {
            $idxName   = $ix->Key_name ?? $ix->key_name ?? null;
            $colName   = $ix->Column_name ?? $ix->column_name ?? null;
            $nonUnique = (int) ($ix->Non_unique ?? $ix->non_unique ?? 1);
            if (!$idxName || !$colName) { continue; }
            if (Str::lower((string) $idxName) === 'primary') { continue; }
            if (!isset($byIndexName[$idxName])) {
                $byIndexName[$idxName] = ['cols' => [], 'unique' => ($nonUnique === 0)];
            }
            $byIndexName[$idxName]['cols'][] = $colName;
            // If any row shows unique, keep it unique (Non_unique = 0)
            if ($nonUnique === 0) {
                $byIndexName[$idxName]['unique'] = true;
            }
        }
        foreach ($byIndexName as $name => $idx) {
            if (count($idx['cols']) === 1) {
                $c = $idx['cols'][0];
                // Prefer unique if detected and store name for chaining
                $singleIndexMap[$c] = [
                    'kind' => $idx['unique'] ? 'uni' : 'mul',
                    'name' => $name,
                ];
            }
        }

        $hasCreatedAt = false;
        $hasUpdatedAt = false;

        $lines = [];
        $primaryAuto = $this->detectPrimaryAutoIncrement($columns->toArray());

        // Prefer concise id() if table has single BIGINT auto-increment PK or any auto PK; falls back to detailed mapping
        if ($primaryAuto) {
            // Use id() to ensure primary + unsigned + auto_increment
            $lines[] = "\$table->id();";
        }

        foreach ($columns as $col) {

            $field  = $col?->Field ?? $col?->field;
            $type   = Str::lower($col?->Type ?? $col?->type);
            $null   = Str::lower((string) $col?->Null ?? $col?->null) === 'yes';
            // Key type should come from Key column not Null
            $key    = Str::lower((string) ($col?->Key ?? $col?->key ?? ''));
            $extra  = Str::lower((string) ($col?->Extra ?? $col?->extra ?? ''));
            $default   = $col?->Default ?? $col?->default;

            // Skip the primary auto column since id() already added it
            if ($primaryAuto && Str::lower($field) === 'id') {
                continue;
            }

            // timestamps grouping
            if (in_array($field, ['created_at','updated_at'])) {
                if ($field === 'created_at') $hasCreatedAt = true;
                if ($field === 'updated_at') $hasUpdatedAt = true;
                continue; // we'll add as $table->timestamps() later if both exist
            }

            // Map type (pass index hint from singleIndexMap when available)
            $indexInfo = $singleIndexMap[$field] ?? (
                $key === 'uni' ? ['kind' => 'uni', 'name' => null] : (
                ($key === 'mul') ? ['kind' => 'mul', 'name' => null] : ['kind' => '', 'name' => null])
            );
            $lines[] = $this->toBlueprintLine($field, $type, $null, $default, $extra, (string)$indexInfo['kind'], $indexInfo['name']);
        }

        if ($hasCreatedAt && $hasUpdatedAt) {
            $lines[] = "\$table->timestamps();";
        } else {
            if ($hasCreatedAt) {
                $lines[] = $this->toBlueprintLine('created_at', 'timestamp', true, null, '', '');
            }
            if ($hasUpdatedAt) {
                $lines[] = $this->toBlueprintLine('updated_at', 'timestamp', true, null, '', '');
            }
        }

        // Indexes (multi-column only). Single-column handled inline via toBlueprintLine.
        $indexStatements = $this->buildIndexLines($indexes, $primaryAuto);
        foreach ($indexStatements as $stmt) {
            $lines[] = $stmt;
        }

        // Foreign keys (single-column only)
        foreach ($fks as $fk) {
            $col = $fk['column'];
            $name = $fk['name'] ?? null;
            $refTable = $fk['referenced_table'];
            $refColumn = $fk['referenced_column'];
            $onDelete = $fk['delete_rule'] ?? null;
            $onUpdate = $fk['update_rule'] ?? null;

            // Use foreign() style, expecting the column already defined above
            $fkLine = "\$table->foreign('{$col}')"
                . "->references('{$refColumn}')"
                . "->on('{$refTable}')";
            if (!empty($onDelete)) {
                $fkLine .= "->onDelete('" . str_replace(' ', '_', Str::lower($onDelete)) . "')";
            }
            if (!empty($onUpdate)) {
                $fkLine .= "->onUpdate('" . str_replace(' ', '_', Str::lower($onUpdate)) . "')";
            }
            $fkLine .= ';';
            $lines[] = $fkLine;
        }

        // Keep body indentation aligned with template (12 spaces)
        $body = implode("\n            ", array_filter($lines));

        return $this->createDummyText($table, $body);
    }

    /** Build a single Blueprint line for a column. */
    protected function toBlueprintLine(string $field, string $type, bool $nullable, $default, string $extra, string $key, ?string $indexName = null): string
    {
        $unsigned = str_contains($type, 'unsigned');
        [$baseType, $length, $scale] = $this->parseType($type);

        $method = null;
        $args = [];

        // Robust enum handling: if raw type starts with enum(...), force enum mapping
        if (preg_match('/^enum\s*\(/i', trim($type))) {
            $method = 'enum';
            $args[] = "'{$field}'";
            $args[] = $this->extractEnumValues($type);
        } else {
            switch ($baseType) {
            case 'bigint':
                $method = 'bigInteger';
                $args[] = "'{$field}'";
                break;
            case 'int':
            case 'mediumint':
            case 'smallint':
                $method = 'integer';
                $args[] = "'{$field}'";
                break;
            case 'tinyint':
                // tinyint(1) => boolean
                if ((int)($length ?? 0) === 1) {
                    $method = 'boolean';
                    $args[] = "'{$field}'";
                } else {
                    $method = 'tinyInteger';
                    $args[] = "'{$field}'";
                    if ($length) { $args[] = (string) (int) $length; }
                }
                break;
            case 'varchar':
                $method = 'string';
                $args[] = "'{$field}'";
                $maxLen = $this->defaultStringLength();
                if ($length !== null && (int)$length !== (int)$maxLen) { $args[] = (string) (int) $length; }
                break;
            case 'char':
                $method = 'char';
                $args[] = "'{$field}'";
                $maxLen = $this->defaultStringLength();
                if ($length !== null && (int)$length !== (int)$maxLen) { $args[] = (string) (int) $length; }
                break;
            case 'text':
                $method = 'text';
                $args[] = "'{$field}'";
                break;
            case 'mediumtext':
                $method = 'mediumText';
                $args[] = "'{$field}'";
                break;
            case 'longtext':
                $method = 'longText';
                $args[] = "'{$field}'";
                break;
            case 'json':
                $method = 'json';
                $args[] = "'{$field}'";
                break;
            case 'decimal':
                $method = 'decimal';
                $args[] = "'{$field}'";
                $args[] = (string) ((int) ($length ?: 8));
                $args[] = (string) ((int) ($scale ?: 2));
                break;
            case 'double':
                $method = 'double';
                $args[] = "'{$field}'";
                $args[] = (string) ((int) ($length ?: 8));
                $args[] = (string) ((int) ($scale ?: 2));
                break;
            case 'float':
                $method = 'float';
                $args[] = "'{$field}'";
                if ($length !== null && $scale !== null) {
                    $args[] = (string) (int) $length;
                    $args[] = (string) (int) $scale;
                }
                break;
            case 'binary':
                $method = 'binary';
                $args[] = "'{$field}'";
                $maxLen = $this->defaultStringLength();
                if ($length !== null && (int)$length !== (int)$maxLen) { $args[] = (string) (int) $length; }
                break;
            case 'blob':
                $method = 'blob';
                $args[] = "'{$field}'";
                break;
            case 'tinyblob':
                $method = 'tinyBlob';
                $args[] = "'{$field}'";
                break;
            case 'mediumblob':
                $method = 'mediumBlob';
                $args[] = "'{$field}'";
                break;
            case 'longblob':
                $method = 'longBlob';
                $args[] = "'{$field}'";
                break;
            case 'datetime':
                $method = 'dateTime';
                $args[] = "'{$field}'";
                break;
            case 'timestamp':
                $method = 'timestamp';
                $args[] = "'{$field}'";
                break;
            case 'date':
                $method = 'date';
                $args[] = "'{$field}'";
                break;
            case 'time':
                $method = 'time';
                $args[] = "'{$field}'";
                break;
            case 'year':
                $method = 'year';
                $args[] = "'{$field}'";
                break;
            case 'enum':
                $method = 'enum';
                $args[] = "'{$field}'";
                $args[] = $this->extractEnumValues($type);
                break;
            default:
                // Fallback to string
                $method = 'string';
                $args[] = "'{$field}'";
                if ($length) { $args[] = (string) (int) $length; }
                break;
        }
        }

        $line = "\$table->{$method}(" . implode(', ', $args) . ")";

        // unsigned for numeric types
        if (in_array($baseType, ['bigint','int','smallint','mediumint','tinyint','decimal','double','float']) && $unsigned) {
            $line .= "->unsigned()";
        }

        // nullable
        if ($nullable) {
            $line .= "->nullable()";
        }

        // default
        if ($default !== null) {
            $defaultStr = is_string($default) ? Str::lower(trim((string) $default)) : $default;

            // If default is NULL (string), omit ->default('NULL') for any type and ensure nullable()
            if (is_string($defaultStr) && $defaultStr === 'null') {
                if (strpos($line, '->nullable()') === false) {
                    $line .= '->nullable()';
                }
                // no default() call
            } else {
                $line .= "->default(" . $this->phpifyDefault($default, $baseType) . ")";
            }
        }

        // reflect single-column index inline (from map or Key)
        if ($key === 'uni') {
            $line .= $indexName ? "->unique('{$indexName}')" : "->unique()";
        } elseif ($key === 'mul') {
            $line .= $indexName ? "->index('{$indexName}')" : "->index()";
        }

        // auto_increment is covered when we used id(); otherwise, ignored here
        return $line . ';';
    }

    /** Parse MySQL type string like "int(11) unsigned" into [base,length,scale]. */
    protected function parseType(string $type): array
    {
        $base = $type;
        $length = null;
        $scale = null;

        if (preg_match('/^([a-z]+)\\(([^)]+)\\)/i', $type, $m)) {
            $base = Str::lower($m[1]);
            $parts = explode(',', $m[2]);
            $length = isset($parts[0]) ? (int) trim($parts[0]) : null;
            if (isset($parts[1])) {
                $scale = (int) trim($parts[1]);
            }
        } else {
            // remove attributes like ' unsigned', ' zerofill'
            $base = trim(explode(' ', $type)[0]);
        }

        return [$base, $length, $scale];
    }

    /** Build index lines for multi-column indexes only. */
    protected function buildIndexLines(array $indexes, bool $primaryAuto): array
    {
        $lines = [];

        // Group by index name to detect multi-column indexes
        $byIndexName = [];
        foreach ($indexes as $idx) {
            $idxName   = $idx->Key_name ?? $idx->key_name ?? null;
            $col       = $idx->Column_name ?? $idx->column_name ?? null;
            $nonUnique = (int) ($idx->Non_unique ?? $idx->non_unique ?? 1);
            if (!$idxName || !$col) { continue; }
            if (Str::lower((string) $idxName) === 'primary') { continue; }
            if (!isset($byIndexName[$idxName])) {
                $byIndexName[$idxName] = ['cols' => [], 'unique' => ($nonUnique === 0)];
            }
            $byIndexName[$idxName]['cols'][] = $col;
            if ($nonUnique === 0) { $byIndexName[$idxName]['unique'] = true; }
        }

        foreach ($byIndexName as $name => $data) {
            // Only handle multi-column indexes here; single-column handled inline
            if (count($data['cols']) <= 1) { continue; }
            $colsList = "['" . implode("','", $data['cols']) . "']";
            if ($data['unique']) {
                $lines[] = "\$table->unique({$colsList}, '{$name}')" . ';';
            } else {
                $lines[] = "\$table->index({$colsList}, '{$name}')" . ';';
            }
        }

        return $lines;
    }

    /** Detect if table has a single auto-increment primary key named 'id'. */
    protected function detectPrimaryAutoIncrement(array $columns): bool
    {
        $pkCols = array_values(array_filter($columns, fn($c) => Str::lower((string)($c->Key ?? '')) === 'pri'));
        if (count($pkCols) !== 1) {
            return false;
        }
        $pk = $pkCols[0];
        $isAuto = str_contains(Str::lower((string)$pk->Extra), 'auto_increment');
        return $isAuto && Str::lower($pk->Field) === 'id';
    }

    /** Extract enum values from a type like "enum('a','b')" and return PHP array syntax. */
    protected function extractEnumValues(string $type): string
    {
        $s = trim($type);
        // Normalize curly quotes
        $s = strtr($s, [
            '‘' => "'", '’' => "'", '‚' => "'", '‛' => "'",
            '“' => '"', '”' => '"', '„' => '"', '‟' => '"',
        ]);

        // Find first '(' and last ')' to capture inside regardless of spaces
        $posL = strpos($s, '(');
        $posR = strrpos($s, ')');
        if ($posL !== false && $posR !== false && $posR > $posL && preg_match('/^\s*enum\b/i', $s)) {
            $vals = substr($s, $posL + 1, $posR - $posL - 1);
            $vals = trim($vals);
            return '[' . $vals . ']';
        }

        // Fallback regex
        if (preg_match("/enum\s*\((.*?)\)/i", $s, $m2)) {
            $vals = trim($m2[1]);
            return '[' . $vals . ']';
        }
        return '[]';
    }

    /** Convert MySQL default to PHP literal for ->default(). */
    protected function phpifyDefault($default, string $baseType): string
    {
        if ($default === null) return 'null';

        // Treat enum/set and text-ish types as strings; numeric types can be raw
        $stringish = ['varchar','char','text','longtext','mediumtext','tinytext','json','enum','set'];
        if (is_numeric($default) && !in_array($baseType, $stringish, true)) {
            return (string) $default;
        }

        // escape single quotes
        $val = str_replace("'", "", (string) $default);
        return "'{$val}'";
    }

    /**
     * Query foreign keys for a given table.
     * @return array<int,array{column:string, referenced_table:string, referenced_column:string, update_rule:?string, delete_rule:?string}>
     */
    protected function getForeignKeys(string $dbName, string $table): array
    {
        $sql = "SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME,
                       rc.UPDATE_RULE, rc.DELETE_RULE
                FROM information_schema.KEY_COLUMN_USAGE kcu
                LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                      ON rc.CONSTRAINT_SCHEMA = kcu.TABLE_SCHEMA
                     AND rc.CONSTRAINT_NAME  = kcu.CONSTRAINT_NAME
                WHERE kcu.TABLE_SCHEMA = :db
                  AND kcu.TABLE_NAME   = :tbl
                  AND kcu.REFERENCED_TABLE_NAME IS NOT NULL";

        $stmt = $this->db['pdo']->prepare($sql);
        $stmt->execute([':db' => $dbName, ':tbl' => $table]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $colelction = new Collection($rows);

        return $colelction->map(function ($r) {
            return [
                'name'              => $r['CONSTRAINT_NAME'] ?? $r[Str::lower('CONSTRAINT_NAME')],
                'column'            => $r['COLUMN_NAME'] ?? $r[Str::lower('COLUMN_NAME')],
                'referenced_table'  => $r['REFERENCED_TABLE_NAME'] ?? $r[Str::lower('REFERENCED_TABLE_NAME')],
                'referenced_column' => $r['REFERENCED_COLUMN_NAME'] ?? $r[Str::lower('REFERENCED_COLUMN_NAME')],
                'update_rule'       => $r['UPDATE_RULE'] ?? $r[Str::lower('UPDATE_RULE')],
                'delete_rule'       => $r['DELETE_RULE'] ?? $r[Str::lower('DELETE_RULE')],
            ];
        })->toArray();
    }

    /** Write migration file for a table. */
    protected function writeMigration(string $table, string $php, string $migrationsDir): string
    {
        $snake = Str::snake($table);
        $fileName = Constant::formatMigrationTableName($snake);
        $path = rtrim($migrationsDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
        File::put($path, $php);
        return $path;
    }
    
    /**
     * Export migrations purely from SQL content (no DB calls).
     *
     * @param string $sql
     * @param array<string>|null $onlyTables
     * @param array<string>|null $exceptTables
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    protected function exportFromSql(string $sql, $onlyTables = null, $exceptTables = null)
    {
        [$migrationsDir, $baseMessage] = $this->ensureMigrationsDir();
        if (!$migrationsDir) {
            return $this->makeResponse(null);
        }

        $blocks = $this->extractCreateTableBlocks($sql); // [ [table, body], ... ]

        if ($onlyTables) {
            $onlyTables = array_map('strval', $onlyTables);
            $blocks = array_values(array_filter($blocks, fn($b) => in_array($b['table'], $onlyTables, true)));
        }

        if ($exceptTables) {
            $exceptTables = array_map('strval', $exceptTables);
            $blocks = array_values(array_filter($blocks, fn($b) => !in_array($b['table'], $exceptTables, true)));
        }

        if (empty($blocks)) {
            $this->error = Constant::STATUS_200;
            $this->message = $baseMessage . "No tables found to export from SQL file.";
            return $this->makeResponse($migrationsDir);
        }

        $generated = [];
        foreach ($blocks as $b) {
            $table = $b['table'];
            $body  = $b['body'];
            $php   = $this->generateTableSchemaFromCreate($table, $body);
            $file  = $this->writeMigration($table, $php, $migrationsDir);
            $generated[] = $this->migrationsDir . basename($file);
        }

        $this->error = Constant::STATUS_200;
        $this->message = $baseMessage . "Generated " . count($generated) 
                        . " migration(s):\n- " . implode("\n- ", $generated);
        return $this->makeResponse($migrationsDir);
    }

    /**
     * Extract CREATE TABLE blocks: table name + inner body (between parentheses).
     * Supports typical MySQL dumps.
     *
     * @return array<int,array{table:string, body:string}>
     */
    protected function extractCreateTableBlocks(string $sql): array
    {
        $blocks = [];
        $re = '/CREATE\s+TABLE\s+`?([a-zA-Z0-9_]+)`?\s*\((.*?)\)\s*(ENGINE|;)/si';
        if (preg_match_all($re, $sql, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $blocks[] = [
                    'table' => $match[1],
                    'body'  => trim($match[2]),
                ];
            }
        }
        return $blocks;
    }

    /**
     * Generate a Schema::create() migration body from a single CREATE TABLE body.
     * Minimal parser for common MySQL syntax as found in dumps.
     */
    protected function generateTableSchemaFromCreate(string $table, string $createBody): string
    {
        $lines = [];
        $hasCreatedAt = false;
        $hasUpdatedAt = false;

        // Tokenize top-level definitions by commas NOT inside quotes/backticks or parentheses
        $body = strtr($createBody, [
            '‘' => "'", '’' => "'", '‚' => "'", '‛' => "'",
            '“' => '"', '”' => '"', '„' => '"', '‟' => '"',
        ]);

        $rawLines = [];
        $buf = '';
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;
        $escape = false;
        $depth = 0;
        $len = strlen($body);

        for ($i = 0; $i < $len; $i++) {
            $ch = $body[$i];

            if ($escape) { $buf .= $ch; $escape = false; continue; }

            // escape handling only inside ' or "
            if (($inSingle || $inDouble) && $ch === '\\') {
                $escape = true;
                $buf .= $ch;
                continue;
            }

            if (!$inDouble && !$inBacktick && $ch === "'") { $inSingle = !$inSingle; $buf .= $ch; continue; }
            if (!$inSingle && !$inBacktick && $ch === '"') { $inDouble = !$inDouble; $buf .= $ch; continue; }
            if (!$inSingle && !$inDouble && $ch === '`') { $inBacktick = !$inBacktick; $buf .= $ch; continue; }

            if (!$inSingle && !$inDouble && !$inBacktick) {
                if ($ch === '(') { $depth++; $buf .= $ch; continue; }
                if ($ch === ')') { if ($depth > 0) $depth--; $buf .= $ch; continue; }
                if ($ch === ',' && $depth === 0) {
                    $t = trim($buf);
                    if ($t !== '') $rawLines[] = $t;
                    $buf = '';
                    continue;
                }
            }

            $buf .= $ch;
        }

        $t = trim($buf);
        if ($t !== '') $rawLines[] = $t;

        $columns = [];
        $indexes = []; // ['type' => 'primary'|'unique'|'index', 'columns' => [..]]
        $fks     = []; // ['name','column','referenced_table','referenced_column','delete_rule','update_rule']
        $primaryAuto = false;

        foreach ($rawLines as $ln) {
            // Column definition: `field` type ... 
            // Fix type capture to allow commas inside parentheses (e.g., enum('0','1'), decimal(15,2))
            if (preg_match('/^`(?P<field>[^`]+)`\s+(?P<type>[a-zA-Z0-9_]+(?:\([^)]+\))?(?:\s+unsigned)?)\s*(?P<rest>.*)$/i', $ln, $cm)) {
                $field = $cm['field'];
                $type  = Str::lower(trim($cm['type']));
                $rest  = Str::lower(trim($cm['rest']));

                $nullable = !preg_match('/\bnot\s+null\b/i', $rest);
                $default  = null;
                if (preg_match('/\bdefault\s+((?:\'(?:\\\\\'|[^\'])*\')|(?:"(?:\\\\"|[^"])*")|[\w\(\)\-\.]+)/i', $cm['rest'], $dm)) {
                    $default = trim($dm[1], " ");
                    // strip surrounding quotes for phpifyDefault to re-handle
                    if ((str_starts_with($default, "'") && str_ends_with($default, "'")) ||
                        (str_starts_with($default, '"') && str_ends_with($default, '"'))) {
                        $default = substr($default, 1, -1);
                    }
                }

                $extra = '';
                if (preg_match('/\bauto_increment\b/i', $cm['rest'])) {
                    $extra = 'auto_increment';
                }

                // detect timestamps
                if ($field === 'created_at') { $hasCreatedAt = true; }
                if ($field === 'updated_at') { $hasUpdatedAt = true; }

                // detect id() shorthand: int/bigint + auto_increment + primary key on id
                if (Str::lower($field) === 'id' && preg_match('/\bauto_increment\b/i', $cm['rest'])) {
                    $primaryAuto = true;
                    // id() will be added first below; skip explicit 'id' column here
                    continue;
                }

                $columns[] = [$field, $type, $nullable, $default, $extra, ''];
                continue;
            }

            // PRIMARY KEY (`id`)
            if (preg_match('/^primary\s+key\s+\((?P<cols>[^\)]+)\)/i', $ln, $pm)) {
                $cols = array_map(fn($c) => trim($c, " `"), explode(',', $pm['cols']));
                $indexes[] = ['type' => 'primary', 'columns' => $cols];
                continue;
            }

            // UNIQUE KEY `name` (`col`)
            if (preg_match('/^unique\s+key\s+`[^`]+`\s+\((?P<cols>[^\)]+)\)/i', $ln, $um)) {
                $cols = array_map(fn($c) => trim($c, " `"), explode(',', $um['cols']));
                if (count($cols) === 1) {
                    $indexes[] = ['type' => 'unique', 'columns' => $cols];
                }
                continue;
            }

            // KEY `name` (`col`)
            if (preg_match('/^key\s+`[^`]+`\s+\((?P<cols>[^\)]+)\)/i', $ln, $km)) {
                $cols = array_map(fn($c) => trim($c, " `"), explode(',', $km['cols']));
                if (count($cols) === 1) {
                    $indexes[] = ['type' => 'index', 'columns' => $cols];
                }
                continue;
            }

            // CONSTRAINT `fk_name` FOREIGN KEY (`col`) REFERENCES `tbl`(`ref`) [ON DELETE x] [ON UPDATE y]
            if (preg_match('/^constraint\s+`(?P<name>[^`]+)`\s+foreign\s+key\s+\(`(?P<col>[^`]+)`\)\s+references\s+`(?P<rtbl>[^`]+)`\s*\(`(?P<rcol>[^`]+)`\)(?P<actions>.*)$/i', $ln, $fm)) {
                $onDelete = null; $onUpdate = null;
                if (preg_match('/on\s+delete\s+(cascade|restrict|set\s+null|no\s+action)/i', $fm['actions'], $dm)) { $onDelete = $dm[1]; }
                if (preg_match('/on\s+update\s+(cascade|restrict|set\s+null|no\s+action)/i', $fm['actions'], $um)) { $onUpdate = $um[1]; }

                $fks[] = [
                    'name' => $fm['name'],
                    'column' => $fm['col'],
                    'referenced_table' => $fm['rtbl'],
                    'referenced_column' => $fm['rcol'],
                    'delete_rule' => $onDelete,
                    'update_rule' => $onUpdate,
                ];
                continue;
            }
        }

        // Build single-column index map for inline chaining
        $singleIndexMap = [];
        foreach ($indexes as $idx) {
            if (($idx['type'] ?? '') === 'primary') { continue; }
            if (isset($idx['columns']) && count($idx['columns']) === 1) {
                $c = $idx['columns'][0];
                $singleIndexMap[$c] = (($idx['type'] ?? '') === 'unique') ? 'uni' : 'mul';
            }
        }

        // Build blueprint lines
        $bodyLines = [];

        if ($primaryAuto) {
            $bodyLines[] = "\$table->id();";
        }

        foreach ($columns as [$field, $type, $nullable, $default, $extra, $key]) {
            if ($primaryAuto && Str::lower($field) === 'id') {
                continue;
            }
            // timestamps are handled together later
            if (in_array($field, ['created_at', 'updated_at'], true)) {
                continue;
            }
            $hint = $singleIndexMap[$field] ?? '';
            $bodyLines[] = $this->toBlueprintLine($field, $type, (bool)$nullable, $default, (string)$extra, (string)$hint);
        }

        if ($hasCreatedAt && $hasUpdatedAt) {
            $bodyLines[] = "\$table->timestamps();";
        } else {
            if ($hasCreatedAt) {
                $bodyLines[] = $this->toBlueprintLine('created_at', 'timestamp', true, null, '', '');
            }
            if ($hasUpdatedAt) {
                $bodyLines[] = $this->toBlueprintLine('updated_at', 'timestamp', true, null, '', '');
            }
        }

        // Indexes (single-column) already inlined; process only multi-column here
        foreach ($indexes as $idx) {
            if ($idx['type'] === 'primary') { continue; }
            if (count($idx['columns']) <= 1) { continue; }
            $colsList = "['" . implode("','", $idx['columns']) . "']";
            if (($idx['type'] ?? '') === 'unique') {
                $bodyLines[] = "\$table->unique({$colsList});";
            } else {
                $bodyLines[] = "\$table->index({$colsList});";
            }
        }

        // FKs (single-column)
        foreach ($fks as $fk) {
            $col = $fk['column'];
            $refTable = $fk['referenced_table'];
            $refColumn = $fk['referenced_column'];
            $onDelete = $fk['delete_rule'] ?? null;
            $onUpdate = $fk['update_rule'] ?? null;

            // Use foreign() style, expecting the column already defined above
            $fkLine = "\$table->foreign('{$col}')"
                . "->references('{$refColumn}')"
                . "->on('{$refTable}')";
            if (!empty($onDelete)) {
                $fkLine .= "->onDelete('" . str_replace(' ', '_', Str::lower($onDelete)) . "')";
            }
            if (!empty($onUpdate)) {
                $fkLine .= "->onUpdate('" . str_replace(' ', '_', Str::lower($onUpdate)) . "')";
            }
            $fkLine .= ';';
            $bodyLines[] = $fkLine;
        }

        // Keep body indentation aligned with template (12 spaces in your dum)
        $body = implode("\n            ", array_filter($bodyLines));

        return $this->createDummyText($table, $body);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @param [type] $body
     * @return mixed
     */
    private function createDummyText($table, $body)
    {
        // real path
        $realPath   = Str::replace('\\', '/', rtrim(realpath(__DIR__ . '/../'), "/\\"));

        $templatePath = "{$realPath}/{$this->type}";

        $template = File::get($templatePath);
        $php = Str::replace(['{{TABLE}}', '{{BODY}}'], [$table, $body], $template);

        return $php;
    }

}