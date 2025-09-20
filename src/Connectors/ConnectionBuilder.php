<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Connectors;

use DateTime;

class ConnectionBuilder 
{
    /**
     * PDO Instance
     *
     * @var mixed
     */
    public $pdo;

    /**
     * Driver Instance
     *
     * @var mixed
     */
    public $driver;

    /**
     * Database name
     *
     * @var string
     */
    public $database;

    /**
     * Table prefix
     *
     * @var string
     */
    public $tablePrefix;

    /**
     * Database Configuration Data
     *
     * @var mixed
     */
    public $config;

    /**
     * PDO query statement
     *
     * @var mixed
     */
    public $statement;

    /**
     * @var mixed
     */
    public $timer;

    /**
     * Construct data received
     *
     * @param object $data
     * @param string $name
     * @param mixed $connection
     */
    public function __construct($data = null, $name = null, $connection = null)
    {
        if($data){
            $this->timer        = new DateTime();
            $this->pdo          = $connection['pdo'] ?? $connection['message'];
            $this->config       = array_merge($connection['config'], ['name' => $name]);
            $this->database     = $this->config['database'];
            $this->tablePrefix  = $this->getTablePrefixIfAllowed();
        }
    }

    /**
     * Get Table Prefix
     * @return string|null
     */
    private function getTablePrefixIfAllowed()
    {
        // if prefixes is set and is `true`
        if(isset($this->config['prefix_indexes']) && $this->config['prefix_indexes']){
            if(isset($this->config['prefix'])){
                return $this->config['prefix'];
            }
        }
    }

}