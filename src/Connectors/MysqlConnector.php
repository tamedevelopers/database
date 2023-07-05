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
use builder\Database\Schema\Traits\BuilderTrait;

class MysqlConnector implements ConnectorInterface{

    use ConnectorTrait, BuilderTrait;

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    private static $options = [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return array
     */
    public static function connect(array $config)
    {
        try {
            // Set DSN
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

            // Create new PDO
            $pdo  = new PDO($dsn, $config['username'], $config['password'], self::$options);
            
            // set charset
            if (isset($config['charset'])) {
                $pdo->exec("set names {$config['charset']}");
                $pdo->exec("set collation_connection = '{$config['charset']}_general_ci'");
            }
    
            // set database to use
            if (isset($config['database'])) {
                $pdo->exec("use {$config['database']}");
            }
    
            // set timezone if available
            if (isset($options['timezone'])) {
                $pdo->exec("set time_zone = '{$config['timezone']}'");
            }
            
            $connection = [
                'pdo'       => $pdo,
                'config'    => $config,
                'status'    => Constant::STATUS_200, 
                'message'   => 'Connection successful', 
            ];
        } catch(PDOException $e){
            $connection = [
                'pdo'       => null,
                'config'    => $config,
                'status'    => Constant::STATUS_400, 
                'message'   => $e->getMessage(), 
            ];
        }

        return $connection;
    }

    /**
     * Describe a table and Get Column Name and Last Insert ID
     *
     * @param  \builder\Database\Schema\Builder  $query
     * @return array
     */
    public function describeColumn(Builder $query)
    {
        $pdo        = $query->connection->pdo;
        $lastId     = $pdo->lastInsertId();
        $statement  = $pdo->prepare("describe `{$query->tableName()}`");
        $statement->execute();

        $columnName = 'id';
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $column) {
            if ($column['Key'] === 'PRI') {
                $columnName = $column['Field'];
                break;
            }
        }

        return [$columnName, $lastId];
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param  string  $sql
     * @return string
     */
    public function compileInsertOrIgnore(string $sql)
    {
        return Str::replaceFirst('insert', 'insert ignore', $sql);
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param  string  $sql
     * @return string
     */
    public function compileUpdateOrIgnore(string $sql)
    {
        return Str::replaceFirst('update', 'update ignore', $sql);
    }

}