<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Server;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Collections\Collection;
use Tamedevelopers\Database\Traits\DBSchemaExportTrait;

/**
 * Convert an existing database into Schema-based migration files.
 *
 * One migration file is generated per table using Schema::create and Blueprint APIs.
 */
class DBSchemaExport
{
    use DBSchemaExportTrait;

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
     * Path to sql file
     *
     * @var string|null
     */
    private $path;

    /**
     * Support for multiple Schema Frameworks Type
     *
     * @var string|null
     */
    private $type;

    /**
     * Internal migrations directory
     *
     * @var string|null
     */
    private $migrationsDir;

    /**
     * File Types supported by this class
     *
     * @var array
     */
    protected $frameWorkTypes = [
        'laravel' => 'Dummy/dummySchemeLaravel.dum',
        'default' => 'Dummy/dummyScheme.dum',
    ];

    /**
     * @param string|null $connection       Connection name as in config/database.php
     * @param string|null $path             If path is provided, it'll generate from the [.sql] file. Default is from the database connecton
     * @param string|null $type             Support for multiple Schema Frameworks Type
     */
    public function __construct($connection = null, $path = null, $type = null)
    {
        $type = Str::lower($type);

        // Check if filetype is valid and set it
        if(in_array($type, array_keys($this->frameWorkTypes))){
            $this->type = $this->frameWorkTypes[$type];
        } else{
            $this->type = $this->frameWorkTypes['default'];
        }

        $this->error    = Constant::STATUS_400;
        $this->path     = $path;
        
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

        // If a path to an SQL file was provided, attempt to import it first
        if (!empty($this->path)) {
            $normalized = Str::replace(Server::formatWithBaseDirectory(), '', (string) $this->path);
            $real = Server::formatWithBaseDirectory($normalized);

            if (!File::exists($real)) {
                $this->error = Constant::STATUS_400;
                $this->message = sprintf("Failed to open stream: [`%s`] doesn't exist.", $real);
                return $this->makeResponse(null);
            }

            // Use SQL file to generate migrations without touching the database
            $sql = File::get($real);
            return $this->exportFromSql($sql, $onlyTables, $exceptTables);
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
                $generated[] = $this->migrationsDir . basename($file);
            }

            $this->error = Constant::STATUS_200;
            $this->message = $baseMessage . "Generated " . count($generated) 
                            . " migration(s):\n- " . implode("\n- ", $generated);
            return $this->makeResponse($migrationsDir);
        } catch (\Throwable $e) {
            $this->error = Constant::STATUS_400;
            $this->message = $e->getMessage();
            return $this->makeResponse(null);
        }
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