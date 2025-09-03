<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Connectors;

use PDO;
use PDOException;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Database\Schema\Builder;
use Tamedevelopers\Database\Connectors\ConnectorInterface;
use Tamedevelopers\Database\Connectors\Traits\ConnectorTrait;
use Tamedevelopers\Database\Schema\Traits\BuilderTrait;

class SQLiteConnector implements ConnectorInterface
{
    use ConnectorTrait, BuilderTrait;

    /**
     * The default PDO connection options.
     *
     * @var array
     */
    private static $options = [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_CASE => PDO::CASE_LOWER,
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
            // Expect database path in $config['database'] (file path or :memory:)
            $database = $config['database'] ?? ':memory:';
            $dsn = "sqlite:" . $database;

            $pdo = new PDO($dsn, null, null, self::$options);

            $connection = [
                'pdo'       => $pdo,
                'config'    => $config,
                'status'    => Constant::STATUS_200,
                'message'   => 'Connection successful',
            ];
        } catch (PDOException $e) {
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
     * @param  \Tamedevelopers\Database\Schema\Builder  $query
     * @return array
     */
    public function describeColumn(Builder $query)
    {
        $pdo    = $query->connection->pdo;
        $lastId = $pdo->lastInsertId();

        // PRAGMA table_info returns info including 'pk' column
        $stmt = $pdo->prepare("PRAGMA table_info(\"{$query->tableName()}\")");
        $stmt->execute();

        $columnName = 'id';
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $column = Str::convertArrayCase($column);
            if (($column['pk'] ?? 0) == 1) {
                $columnName = $column['name'] ?? 'id';
                break;
            }
        }

        return [$columnName, $lastId];
    }

    /**
     * SQLite supports INSERT OR IGNORE syntax.
     *
     * @param  string  $sql
     * @return string
     */
    public function compileInsertOrIgnore(string $sql)
    {
        return Str::replaceFirst('insert', 'insert or ignore', $sql);
    }

    /**
     * SQLite does not support UPDATE IGNORE; return unchanged.
     *
     * @param  string  $sql
     * @return string
     */
    public function compileUpdateOrIgnore(string $sql)
    {
        return $sql;
    }
}
