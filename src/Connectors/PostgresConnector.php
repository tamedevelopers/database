<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Connectors;

use PDO;
use PDOException;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Database\Schema\Builder;
use Tamedevelopers\Database\Connectors\ConnectorInterface;
use Tamedevelopers\Database\Connectors\Traits\ConnectorTrait;


class PostgresConnector 
{
    use ConnectorTrait;

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
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
     * @param  \Tamedevelopers\Database\Schema\Builder  $query
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
