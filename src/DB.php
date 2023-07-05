<?php

declare(strict_types=1);

/*
 * This file is part of ultimate-orm-database.
 *
 * (c) Tame Developers Inc.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace builder\Database;

use builder\Database\DatabaseManager;
use builder\Database\Traits\DBSetupTrait;
use builder\Database\Schema\Traits\ExpressionTrait;


/**
 * @method static \builder\Database\Connectors\Connector connection(string|null $name, mixed $default = null)
 * @method static \builder\Database\Connectors\Connector reconnect(string|null $name = null)
 * @method static \builder\Database\DatabaseManager disconnect(string|null $name = null)
 * @method static string getDefaultConnection()
 * @method static array  getConnection(string|null $name)
 * @method static \builder\Database\Schema\Builder table(string $table)
 * @method static \builder\Database\Schema\Builder from(string $table)
 * @method static \builder\Database\Schema\Builder query()
 * @method static mixed selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static \builder\Database\Builder select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool exists()
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static void bindValues(\PDOStatement $statement, array $bindings)
 * 
 * @method static \builder\Database\Schema\Expression raw(mixed $value)
 * @method \builder\Database\Schema\Expression raw(mixed $value)
 * @method float totalQueryDuration()
 * @method array dbConnection()
 * @method \PDO|string of error message getPdo()
 * @method string|null getName()
 * @method array getConfig(string|null $option = null)
 * @method string getDatabaseName()
 * @method string getTablePrefix()
 * 
 * @see \builder\Database\Schema\Builder
 * @see \builder\Database\DatabaseManager
 * @see \builder\Database\Schema\Expression
 * @see \builder\Database\Connectors\Connector
 * @see \builder\Database\Schema\Traits\MySqlProperties
 */
class DB extends DatabaseManager{
    
    use DBSetupTrait, ExpressionTrait;
    
}