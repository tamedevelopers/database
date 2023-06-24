<?php

declare(strict_types=1);

namespace builder\Database\Connectors;

use PDO;
use PDOException;
use builder\Database\Constant;
use builder\Database\Capsule\Str;
use builder\Database\Schema\Builder;
use builder\Database\Connectors\ConnectorInterface;
use builder\Database\Connectors\Traits\ConnectorTrait;


class PostgresConnector 
{
    use ConnectorTrait;

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return array
     */
    public function connect(array $config)
    {
        // 
    }

    
    /**
     * Describe a table and Get Column Name and Last Insert ID
     *
     * @param  \builder\Database\Schema\Builder  $query
     * @return array
     */
    public function describeColumn(Builder $query)
    {
        // 
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param  string  $sql
     * @return string
     */
    public function compileInsertOrIgnore(string $sql)
    {
        // 
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param  string  $sql
     * @return string
     */
    public function compileUpdateOrIgnore(string $sql)
    {
        // 
    }

}
