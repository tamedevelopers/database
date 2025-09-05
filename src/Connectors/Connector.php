<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Connectors;

use PDO;
use Exception;
use Tamedevelopers\Support\Server;
use Tamedevelopers\Database\Schema\Builder;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Support\Capsule\FileCache;
use Tamedevelopers\Database\Traits\ExceptionTrait;
use Tamedevelopers\Database\Schema\Pagination\Paginator;
use Tamedevelopers\Database\Connectors\ConnectionBuilder;
use Tamedevelopers\Database\Schema\Traits\ExpressionTrait;
use Tamedevelopers\Database\Connectors\Traits\ConnectorTrait;


class Connector extends DatabaseManager{
    
    use ConnectorTrait, 
        ExceptionTrait,
        ExpressionTrait;
    
    /**
     * @var mixed
     */
    private $connection;

    /**
     * @var array|null
     */
    private $default;

    /**
     * @var string|null
     */
    private $name;

    /**
     * Save instances of all connection
     * @var mixed
     */
    private static $connections = [];
    
    /**
     * Constructors
     * 
     * @param string|null $name - Driver name
     * @param mixed $connection - Connection instance
     * @param array $data - Default data
     */
    public function __construct($name = null, $connection = null, $data = [])
    {
        $this->setConnectionName($name);
        $this->setConnection($connection);
        $this->setDefaultData($data);
    }

    /**
     * Add to connection instance
     * 
     * @param string|null $name - Driver name
     * @param mixed $connection - Connection instance
     * @param array $data - Default data
     * 
     * @return \Tamedevelopers\Database\Connectors\Connector
     */
    static public function addConnection($name = null, $connection = null, $data = [])
    {
        $driver = static::driverValidator($name);

        // driver name
        $driverName = $driver['name'];

        // connector object
        self::$connections[$driverName] = new self(
            connection: $connection,
            name: $driverName,
            data: $data,
        );

        return self::$connections[$driverName];
    }

    /**
     * Remove from connection instance
     * 
     * @param string|null $name - Driver name
     * 
     * @return void
     */
    static public function removeFromConnection($name = null)
    {
        $driver = static::driverValidator($name);

        // driver name
        $driverName = $driver['name'];

        unset(self::$connections[$driverName]);
    }

    /**
     * Table name
     * This is being used on all instance of one query
     * 
     * @param string $table
     * 
     * @return \Tamedevelopers\Database\Schema\Builder
     */
    public function table(string $table)
    {
        $this->isModelDriverCreated();

        return $this->buidTable($table);
    }

    /**
     * Check if table exists
     * 
     * @param mixed $table
     * 
     * @return bool
     */
    public function tableExists(...$table)
    {
        return $this->table('')->tableExists($table);
    }

    /**
     * Direct Query Expression
     * 
     * @param string $query
     * @return \Tamedevelopers\Database\Schema\Builder
     */ 
    public function query(string $query)
    {
        return $this->table('')->query($query);
    }

    /**
     * Set the table which the query is targeting.
     *
     * @param  string  $table
     * @param  string|null  $as
     * 
     * @return \Tamedevelopers\Database\Schema\Builder
     */
    public function from($table, $as = null)
    {
        return $this->table('')->from($table, $as);
    }

    /**
     * Run a SELECT query and return all results.
     *
     * @param string $query
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public function select(string $query)
    {
        return $this->query($query)->get();
    }
    
    /**
     * Run a SELECT query and return a single result.
     *
     * @param string $query
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    public function selectOne(string $query)
    {
        return $this->query($query)->first();
    }

    /**
     * Configuring pagination settings 
     * @param array $options
     * [optional]
     * 
     * @return void
     */
    public function configPagination(array $options = []) 
    {
        // create a new instance of Paginator
        $paginator = new Paginator();

        // Only if the Global Constant is not yet defined
        // If set to allow global use of ENV Autoloader Settings
        if(defined('TAME_PAGI_CONFIG') && Manager::isEnvBool(TAME_PAGI_CONFIG['allow']) === true){
            $paginator->configPagination(TAME_PAGI_CONFIG);
        } else{
            $paginator->configPagination($options);
        }
    }
    
    /**
     * Build Table Instance
     * @param string $table
     * 
     * @return $this
     */
    private function buidTable($table = null)
    {
        // begin build
        $builder = new Builder;
        $builder->manager = new Manager;
        $builder->dbManager = new DatabaseManager;

        // get saved connection from $connections array
        $instance = self::$connections[$this->name] ?? null;

        // There's no connecton instance set
        if(empty($instance)){
            try {
                throw new Exception("
                    There's no active connection! Unknown connection [{$this->name}]. \n\n
                    Use DB::connection(\$connName), to instatiate connection.
                ");
            } catch (\Throwable $th) {
                $this->errorException($th);
            }
        }

        // set connection
        $instance->connection = $this->dbConnection();

        // setup table name
        $builder->from = $table;

        // building of table name is only called once
        // so we will build the instance of Connection data into the
        // Builder class property
        $builder->connection = new ConnectionBuilder(
            $instance, 
            $instance->name, 
            $instance->connection
        );

        // build driver instance
        $builder->connection->driver = self::createConnector(
            $instance->connection['config']['driver']
        );

        return $builder;
    }

    /**
     * Get the prefix of the currently selected database driver
     *
     * @return string|null 
     */
    public function getTablePrefix()
    {
        return $this->getDataByMode('prefix');
    }

    /**
     * Get Connection data
     * 
     * @param string|null $mode
     * 
     * @return mixed
     */
    public function dbConnection($mode = null)
    {
        // get connection data
        // merge data to default if provided, before we try to connect
        $conn = self::getConnectionFromDatabaseFile($this->name, $this->default);

        // connection data
        $connData = self::createConnector($conn['driver'])->connect($conn);

        // merge data
        $data = array_merge($connData ?? [], [
            'name' => $this->name,
        ]);

        return $data[$mode] ?? $data;
    }

    /**
     * Get the PDO instance for the current database driver.
     *
     * @return PDO|null 
     * The PDO instance or null if the connection is not established.
     */
    public function getPDO()
    {
        return $this->dbConnection('pdo');
    }

    /**
     * Get the name of the currently selected database driver.
     *
     * @return string|null
     */
    public function getDatabaseName()
    {
        return $this->getDataByMode('database');
    }

    /**
     * Get the currently selected database config data
     *
     * @return mixed
     */
    public function getConfig()
    {
        return self::getConnectionFromDatabaseFile($this->name, $this->default);
    }

    /**
     * Get Connection data
     * 
     * @param string|null $name
     * @param array|null $default
     * 
     * @return array
     */
    private static function getConnectionFromDatabaseFile($name = null, $default = [])
    {
        $data = Server::config(
            static::getConnectionKey($name), 
            []
        );

        return array_merge($data, $default ?? []);
    }

    /**
     * Get currently selected driver data
     *
     * @param string|null $mode
     * @return mixed
     */
    private function getDataByMode($mode = null)
    {
        return $this->getConfig()[$mode] ?? null;
    }

    /**
     * Set Database Connection
     * @param string $connection
     * 
     * @return void
     */
    private function setConnection($connection = null)
    {
        if(!empty($connection)){
            $this->connection = $connection;
        }
    }

    /**
     * Set default connection data
     *
     * @param array $default
     * @return void
     */
    private function setDefaultData($default = [])
    {
        if(!empty($default)){
            $this->default = $default;
        }
    }

    /**
     * Set connection connection name
     *
     * @param string|null $name
     * @return void
     */
    private function setConnectionName($name = null)
    {
        $this->name = empty($name) 
                    ? Server::config("database.default") 
                    : $name;
    }

    /**
     * Check if model driver has been created or create one
     * This will only check once and for Model Extended Class Only
     * 
     * @return string
     */
    private function isModelDriverCreated()
    {
        if(self::isModelExtended()){
            $this->setConnectionName();
            $key = static::getConnectionKey($this->name);
            if (!FileCache::exists($key)) {
                static::connection($this->name);
            }
        } 
    }

}
