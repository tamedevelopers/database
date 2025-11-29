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

class PostgresConnector implements ConnectorInterface
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
            $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

            $pdo = new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null, self::$options);

            // Set client encoding if provided
            if (self::checkIssetEmpty($config, 'charset')) {
                $charset = $config['charset'];
                // PostgreSQL accepts SET NAMES / client_encoding
                $pdo->exec("set names '{$charset}'");
            }

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
                'message'   => "{$e->getMessage()} [pgsql]",
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
        $table  = $query->tableName();

        // Normalize table (remove quotes/backticks if any)
        $table = str_replace(['`', '"'], '', $table);

        $sql = "
            SELECT kcu.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name
             AND tc.table_schema = kcu.table_schema
            WHERE tc.constraint_type = 'PRIMARY KEY'
              AND tc.table_name = :table
            ORDER BY kcu.ordinal_position
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':table' => $table]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $columnName = $row['column_name'] ?? 'id';

        return [$columnName, $lastId];
    }

    /**
     * Compile an insert ignore statement into SQL for PostgreSQL.
     * Converts to: INSERT ... ON CONFLICT DO NOTHING
     *
     * @param  string  $sql
     * @return string
     */
    public function compileInsertOrIgnore(string $sql)
    {
        $trimmed = rtrim($sql, "; \t\n\r\0\x0B");
        // Avoid double-appending
        if (stripos($trimmed, 'on conflict do nothing') !== false) {
            return $trimmed;
        }
        return $trimmed . ' on conflict do nothing';
    }

    /**
     * PostgreSQL has no UPDATE IGNORE; return SQL unchanged.
     *
     * @param  string  $sql
     * @return string
     */
    public function compileUpdateOrIgnore(string $sql)
    {
        return $sql;
    }
}
