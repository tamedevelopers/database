<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Connectors;

use PDO;
use Tamedevelopers\Database\Schema\Builder;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Support\Capsule\FileCache;
use Tamedevelopers\Database\Schema\Pagination\Paginator;
use Tamedevelopers\Database\Connectors\ConnectionBuilder;
use Tamedevelopers\Database\Schema\Traits\ExpressionTrait;
use Tamedevelopers\Database\Connectors\Traits\ConnectorTrait;


class Connector {
    
    use ConnectorTrait, 
        ExpressionTrait;
    
    /**
     * @var mixed
     */
    private $connection;

    /**
     * @var string|null
     */
    private $name;

    
    /**
     * Constructors
     * 
     * @param string|null $name\Database connection name
     * @param mixed $connection \Connection instance
     * 
     */
    public function __construct($name = null, mixed $connection = null)
    {
        $this->setConnectionName($name);
        $this->setConnection($connection);
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
     * @param string|null $table
     * 
     * @return bool
     */
    public function tableExists($table)
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
        if(defined('PAGINATION_CONFIG') && Manager::isEnvBool(PAGINATION_CONFIG['allow']) === true){
            $paginator->configPagination(PAGINATION_CONFIG);
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
        $builder = new Builder;
        $builder->manager = new Manager;
        $builder->dbManager = new DatabaseManager;

        // create instance of self
        $instance = new self(
            $this->name,
            $this->dbConnection(),
        );

        // setup table name
        $builder->from = $this->compileTableWithPrefix($table, $this->getConfig());

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
        $conn = DatabaseManager::getConnection($this->name);
        
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
        return DatabaseManager::getConnection($this->name);
    }

    /**
     * Get Table Name
     * @param string $table
     * @param array $data
     * @return string
     */
    private static function compileTableWithPrefix($table = null, ?array $data = null)
    {
        // check prefixes
        if(isset($data['prefix_indexes']) && $data['prefix_indexes']){
            if(isset($data['prefix'])){
                $table = "{$data['prefix']}{$table}";
            }
        }

        return $table;
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
     * Set connection connection name
     *
     * @param string|null $name
     * @return void
     */
    private function setConnectionName($name = null)
    {
        $this->name = empty($name) 
                    ? config("database.default") 
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
            $key = DatabaseManager::getCacheKey($this->name);
            if (!FileCache::exists($key)) {
                DatabaseManager::connection($this->name);
            }
        } 
    }

}
