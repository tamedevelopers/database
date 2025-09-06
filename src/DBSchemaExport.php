<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Env;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Server;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Collections\Collection;

/**
 * Convert an existing database into Schema-based migration files.
 *
 * One migration file is generated per table using Schema::create and Blueprint APIs.
 */
class DBSchemaExport
{
    /**
     * Instance of Database Object
     *
     * @var mixed
     */
    public $db;

    /**
     * Instance of Database Object
     *
     * @var \Tamedevelopers\Database\Connectors\Connector
     */
    public $conn;

    /**
     * Error status
     *
     * @var int
     */
    protected $error;

    /**
     * Message body
     *
     * @var mixed
     */
    protected $message;

    /**
     * @param string|null $connection  Connection name as in config/database.php
     */
    public function __construct($connection = null)
    {
        $this->error    = Constant::STATUS_404;
        $this->conn     = DB::connection($connection);
        $this->db       = $this->conn->dbConnection();
    }

    /**
     * Run the export process.
     *
     * @param array|null $onlyTables  List of tables to include (null = all)
     * @param array|null $exceptTables  List of tables to exclude
     * 
     * @return \Tamedevelopers\Support\Collections\Collection  
     * [status, path, message]
     */
    public function run($onlyTables = null, $exceptTables = null)
    {
        if (!$this->dbConnect()) {
            $this->message = $this->db['message'];
            return $this->makeResponse(null);
        }

        [$migrationsDir, $baseMessage] = $this->ensureMigrationsDir();
        if (!$migrationsDir) {
            return $this->makeResponse(null);
        }

        try {
            $dbName = $this->db['config']['database'];
            $tables = $this->getTables($dbName);

            if ($onlyTables) {
                $onlyTables = array_map('strval', $onlyTables);
                $tables = array_values(array_intersect($tables, $onlyTables));
            }

            if ($exceptTables) {
                $exceptTables = array_map('strval', $exceptTables);
                $tables = array_values(array_diff($tables, $exceptTables));
            }

            if (empty($tables)) {
                $this->error = Constant::STATUS_200;
                $this->message = $baseMessage . "No tables found to export.";
                return $this->makeResponse($migrationsDir);
            }

            $generated = [];
            foreach ($tables as $table) {
                $schema = $this->generateTableSchema($dbName, $table);
                $file   = $this->writeMigration($table, $schema, $migrationsDir);
                $generated[] = basename($file);
            }

            $this->error = Constant::STATUS_200;
            $this->message = $baseMessage . "Generated " . count($generated) . " migration(s):\n- " . implode("\n- ", $generated);
            return $this->makeResponse($migrationsDir);
        } catch (\Throwable $e) {
            $this->error = Constant::STATUS_400;
            $this->message = $e->getMessage();
            return $this->makeResponse(null);
        }
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
            $key    = Str::lower((string) $col?->Null ?? $col?->null);
            $extra  = Str::lower((string) $col?->Extra ?? $col?->extra);
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

            // Map type
            $lines[] = $this->toBlueprintLine($field, $type, $null, $default, $extra, $key);
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

        // Indexes (single-column only)
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

            // Try to use foreignId when possible, else use foreign()->references()->on()
            $name = $fk['name'] ?? null;
            $fkLine = "\$table->foreignId('{$col}')->constrained('{$refTable}', '{$refColumn}', '{$name}')";
            if (!empty($onDelete)) {
                $fkLine .= "->onDelete('" . Str::upper($onDelete) . "')";
            }
            if (!empty($onUpdate)) {
                $fkLine .= "->onUpdate('" . Str::upper($onUpdate) . "')";
            }
            $fkLine .= ';';
            $lines[] = $fkLine;
        }

        $body = implode("\n                ", array_filter($lines));

        $php = <<<PHP
<?php

use Tamedevelopers\Database\Migrations\Schema;
use Tamedevelopers\Database\Migrations\Blueprint;
use Tamedevelopers\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return mixed
     */
    public function up()
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            {$body}
        });
    }

    /**
     * Drop database table
     *
     * @param bool \$force [optional] Default is false
     * @return mixed
     */
    public function drop(\$force = false)
    {
        return Schema::dropTable('{$table}', \$force);
    }
};
PHP;
        return $php;
    }

    /** Build a single Blueprint line for a column. */
    protected function toBlueprintLine(string $field, string $type, bool $nullable, $default, string $extra, string $key): string
    {
        $unsigned = str_contains($type, 'unsigned');
        [$baseType, $length, $scale] = $this->parseType($type);

        $method = null;
        $args = [];

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
                if ($length) { $args[] = (string) (int) $length; }
                break;
            case 'char':
                $method = 'char';
                $args[] = "'{$field}'";
                $args[] = (string) ((int) ($length ?: 255));
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
                $args[] = (string) ((int) ($length ?: 255));
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
            $line .= "->default(" . $this->phpifyDefault($default, $baseType) . ")";
        }

        // key (non-unique index handled later); unique can be reflected here if single-column
        if ($key === 'uni') {
            $line .= "->unique()";
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

    /** Build index lines for single-column indexes. */
    protected function buildIndexLines(array $indexes, bool $primaryAuto): array
    {
        $lines = [];
        // Group by column
        $byCol = [];
        foreach ($indexes as $idx) {
            $col = $idx->Column_name ?? $idx->column_name ?? null;
            if (!$col) { continue; }
            $byCol[$col][] = $idx;
        }

        foreach ($byCol as $col => $rows) {
            // skip primary auto handled by id()
            $isPrimary = false;
            foreach ($rows as $r) {
                $isPrimary = $isPrimary || (Str::lower((string)($r->Key_name ?? $r->key_name ?? '')) === 'primary');
            }
            if ($primaryAuto && $isPrimary && Str::lower($col) === 'id') {
                continue;
            }

            // determine uniqueness for single-column cases
            $unique = false;
            foreach ($rows as $r) {
                $non = (int) ($r->Non_unique ?? $r->non_unique ?? 1);
                if ($non === 0) { $unique = true; break; }
            }

            if ($unique) {
                $lines[] = "\$table->unique()" . ';'; // attaches to last column; but we already generated column lines before, so do separate unique using name
                // Better: emit explicit unique index name
                $lines[count($lines)-1] = "\$table->unique('{$col}')" . ';';
            } else {
                $lines[] = "\$table->index('{$col}')" . ';';
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
        if (preg_match("/enum\\((.*)\\)/i", $type, $m)) {
            $vals = $m[1];
            return '[' . $vals . ']'; // already quoted in SHOW COLUMNS
        }
        return '[]';
    }

    /** Convert MySQL default to PHP literal for ->default(). */
    protected function phpifyDefault($default, string $baseType): string
    {
        if ($default === null) return 'null';

        // CURRENT_TIMESTAMP and similar should be passed as strings here (Schema will quote it)
        if (is_numeric($default) && !in_array($baseType, ['varchar','char','text','longtext','mediumtext','tinytext','json'])) {
            return (string) $default;
        }

        // escape single quotes
        $val = str_replace("'", "\\'", (string) $default);
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
        $filename = date('Y_m_d') . '_create_' . $snake . '_table.php';
        $path = rtrim($migrationsDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
        File::put($path, $php);
        return $path;
    }

    /**
     * Create API Response
     * @param string|null $path
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    protected function makeResponse($path = null)
    {
        $message = is_array($this->message) ? implode("\n", $this->message) : (string) $this->message;
        return new Collection([
            'status'  => $this->error,
            'path'    => $path ?? '',
            'message' => $message,
        ]);
    }
}