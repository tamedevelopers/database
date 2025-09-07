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

use Exception;
use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\Connectors\Connector;
use Tamedevelopers\Database\Traits\ExceptionTrait;
use Tamedevelopers\Database\Connectors\Traits\ConnectorTrait;


/**
 * @property string $table
 */
abstract class Model extends DB{

    use ConnectorTrait, ExceptionTrait;

    /**
     * The table associated with the model.
     * 
     * @var string|null
     * 
     * Used to define access level for users override only
     * Else we never used this in entire project apart from
     * Model Class Table Initialization alone
     */
    protected $table;
    
    /**
     * Create a new Eloquent model instance.
     */
    public function __construct()
    {
        // automatically connect to database when model is instantiated
        $this->connection();
    }
    
    /**
     * Handle the calls to non-existent instance methods.
     * @param string $name
     * @param mixed $args \arguments
     * 
     * @return $this
     */
    public function __call($method, $args) 
    {
        return self::modelException($method, $args, self::initTableWithConnector());
    }

    /**
     * Handle the calls to non-existent static methods.
     * @param string $name
     * @param mixed $args \arguments
     * 
     * @return $this
     */
    public static function __callStatic($method, $args) 
    {
        return self::modelException($method, $args, self::initTableWithConnector());
    }

    /**
     * Get Table name from model class
     * @param string $name
     * @param mixed $args \arguments
     * 
     * @return \Tamedevelopers\Database\Schema\Builder
     */
    private static function initTableWithConnector()
    {
        $instance = (new static);
        if(isset($instance->table)){
            $table = $instance->table;
        }

        // if empty then we assume it's not defined 
        // and convert model class name to table name
        // using pluralization method
        if(empty($table)){
            $table = self::tabelPluralization();
        }
        
        return (new Connector)->table($table);
    }

    /**
     * Handle the calls to non-existent methods.
     * @param string|null $method
     * @param mixed $args \arguments
     * @param mixed $class
     * 
     * @return mixed
     */
    public static function modelException($method = null, $args = null, $class = null) 
    {
        // instance of DB Class
        $instance = !$class ? new self() : $class;

        // unkown method
        if (!method_exists($instance, $method)) {
            try {
                throw new Exception("Method [{$method}] does not exist in class '" . get_class($instance) . "'.");
            } catch (\Throwable $th) {
                self::staticErrorException($th);
            }
        }

        return $instance->$method(...$args);
    }

}