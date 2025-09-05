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
use Tamedevelopers\Database\Traits\ExceptionTrait;

/**
 * @method static \Tamedevelopers\Database\Connectors\Connector connection(string|null $name, mixed $default = null)
 * @method static \Tamedevelopers\Database\Connectors\Connector reconnect(string|null $name = null)
 * @method static \Tamedevelopers\Database\DatabaseManager disconnect(string|null $name = null)
 * @method static string getDefaultConnection()
 * @method static \Tamedevelopers\Database\DB from(string $table)
 * @method static \Tamedevelopers\Database\DB table(string $table)
 * @method static \Tamedevelopers\Database\DB query(string $query)
 * @method static \Tamedevelopers\Database\DB select(string $query)
 * @method static \Tamedevelopers\Database\DB selectOne(string $query)
 * @method static \Tamedevelopers\Database\DB tableExists(mixed $table)
 * @method static \Tamedevelopers\Database\DB statement(string $query, array $bindings = [])
 *
 * @method \Tamedevelopers\Database\Schema\Builder from(string $table)
 * @method \Tamedevelopers\Database\Schema\Builder get(int $limit = null)
 * @method \Tamedevelopers\Database\Schema\Builder paginate($perPage = 15, $pageParam = 'page')
 * @method \Tamedevelopers\Database\Schema\Builder first()
 * @method \Tamedevelopers\Database\Schema\Builder firstOrIgnore()
 * @method \Tamedevelopers\Database\Schema\Builder firstOrFail()
 * @method \Tamedevelopers\Database\Schema\Builder firstOrCreate(array $attributes = [], array $values = [])
 * @method \Tamedevelopers\Database\Schema\Builder find(int $value)
 * @method \Tamedevelopers\Database\Schema\Builder insert(array $values)
 * @method \Tamedevelopers\Database\Schema\Builder insertOrIgnore(array $values)
 * @method \Tamedevelopers\Database\Schema\Builder update(array $values)
 * @method \Tamedevelopers\Database\Schema\Builder updateOrIgnore(array $values)
 * @method \Tamedevelopers\Database\Schema\Builder delete()
 * @method \Tamedevelopers\Database\Schema\Builder destroy(mixed $value, string $column = 'id')
 * @method \Tamedevelopers\Database\Schema\Builder count()
 * @method \Tamedevelopers\Database\Schema\Builder exists()
 * @method \Tamedevelopers\Database\Schema\Builder tableExists(mixed $table)
 * @method \Tamedevelopers\Database\Schema\Builder min(Expression|string $column)
 * @method \Tamedevelopers\Database\Schema\Builder max(Expression|string $column)
 * @method \Tamedevelopers\Database\Schema\Builder sum(Expression|string $column)
 * @method \Tamedevelopers\Database\Schema\Builder avg(Expression|string $column)
 * 
 * @method \Tamedevelopers\Database\Schema\Expression raw(mixed $value)
 * @method \Tamedevelopers\Database\Schema\Builder totalQueryDuration()
 * @method \Tamedevelopers\Database\Schema\Builder tableName()
 * @method \Tamedevelopers\Database\Schema\Builder query(string $query)
 * 
 * @method \Tamedevelopers\Database\Connectors\Connector getPDO()
 * @method \Tamedevelopers\Database\Connectors\Connector dbConnection(string|null $mode)
 * @method \Tamedevelopers\Database\Connectors\Connector getConfig()
 * @method \Tamedevelopers\Database\Connectors\Connector getDatabaseName()
 * @method \Tamedevelopers\Database\Connectors\Connector getTablePrefix()
 * 
 * @see \Tamedevelopers\Database\Schema\Builder
 * @see \Tamedevelopers\Database\DatabaseManager
 * @see \Tamedevelopers\Database\Schema\Expression
 * @see \Tamedevelopers\Support\Collections\Collection
 * @see \Tamedevelopers\Database\Connectors\Connector
 * @see \Tamedevelopers\Database\Traits\DBSetupTrait
 * @see \Tamedevelopers\Database\Schema\Traits\MySqlProperties
 */
class DB extends DatabaseManager{
    
    use DBSetupTrait, ExpressionTrait, ExceptionTrait;

}