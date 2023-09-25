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
namespace Tamedevelopers\Database;

use Tamedevelopers\Database\DatabaseManager;
use Tamedevelopers\Database\Traits\DBSetupTrait;
use Tamedevelopers\Database\Schema\Traits\ExpressionTrait;


/**
 * @method static \Tamedevelopers\Database\Connectors\Connector connection(string|null $name, mixed $default = null)
 * @method static \Tamedevelopers\Database\Connectors\Connector reconnect(string|null $name = null)
 * @method static \Tamedevelopers\Database\DatabaseManager disconnect(string|null $name = null)
 * @method static string getDefaultConnection()
 * @method static array  getConnection(string|null $name)
 * @method static \Tamedevelopers\Database\Schema\Builder table(string $table)
 * @method static \Tamedevelopers\Database\Schema\Builder from(string $table)
 * @method static \Tamedevelopers\Database\Schema\Builder query()
 * @method static mixed selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static \Tamedevelopers\Database\Builder select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool exists()
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static void bindValues(\PDOStatement $statement, array $bindings)
 * 
 * @method static \Tamedevelopers\Database\Schema\Expression raw(mixed $value)
 * @method \Tamedevelopers\Database\Schema\Expression raw(mixed $value)
 * @method float totalQueryDuration()
 * @method array dbConnection()
 * @method \PDO|string of error message getPdo()
 * @method string|null getName()
 * @method array getConfig(string|null $option = null)
 * @method string getDatabaseName()
 * @method string getTablePrefix()
 * 
 * @see \Tamedevelopers\Database\Schema\Builder
 * @see \Tamedevelopers\Database\DatabaseManager
 * @see \Tamedevelopers\Database\Schema\Expression
 * @see \Tamedevelopers\Database\Connectors\Connector
 * @see \Tamedevelopers\Database\Schema\Traits\MySqlProperties
 */
class DB extends DatabaseManager{
    
    use DBSetupTrait, ExpressionTrait;
    
}