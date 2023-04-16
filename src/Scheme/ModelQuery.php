<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Schema;

use PDO;
use Exception;
use Throwable;
use PDOException;
use UltimateOrmDatabase\Constants;
use UltimateOrmDatabase\Trait\ModelTrait;
use UltimateOrmDatabase\Methods\ModelMethod;
use UltimateOrmDatabase\Trait\ReusableTrait;


class ModelQuery extends Constants {
    
    use ModelTrait, ReusableTrait;

    /**
     * @var array|null
     */
    protected $connection;

    /**
     * @var string|null
     */
    protected $table;

    /**
     * @var object|null|void
     */
    protected $stmt;

    /**
     * @var object|null|void
     */
    protected $dbh;

    /**
     * @var string|null
     */
    protected $query;

    /**
     * @var string|null
     */
    private $special_key = 'WHERE_COL_KEY';

    /**
     * @var string|null
     */
    protected $limit;

    /**
     * @var int|float|null
     */
    protected $limitCount;

    /**
     * @var int|float|null
     */
    protected $offset;

    /**
     * @var int|float|null
     */
    protected $offsetCount;

    /**
     * @var string|null
     */
    protected $orderBy;

    /**
     * @var string|null
     */
    protected $tempQuery;

    /**
     * @var string|null
     */
    protected $tempRawQuery;

    /**
     * @var string|null
     */
    protected $tempUpdateQuery;

    /**
     * @var string|null
     */
    protected $tempIncrementQuery;

    /**
     * @var string|null
     */
    protected $tempInsertQuery;

    /**
     * @var array
     */
    protected $joins    = [];

    /**
     * @var array
     */
    protected $where    = [];

    /**
     * @var string
     */
    protected $groupBy;

    /**
     * @var array
     */
    protected $selectColumns = [];

    /**
     * @var bool
     */
    public $selectQuery = false;

    /**
     * @var bool
     */
    public $PaginateQuery = false;

    /**
     * @var bool
     */
    public $countQuery = false;

    /**
     * @var bool
     */
    public $modelQuery  = false;

    /**
     * @var bool
     */
    public $rawQuery    = false;

    /**
     * @var int|void
     */
    public $start_time;

    /**
     * @var int|void
     */
    public $end_time;
    
    /**
     * Staring the Database
     * 
     * @return array\startDatabase
     */
    public function startDatabase()
    {
        try {
            // get all data
            $db = $this->getConfig('all');
            
            // Set DSN
            $dsn = "mysql:host={$db['DB_HOST']};port={$db['DB_PORT']};dbname={$db['DB_DATABASE']}";

            // Create new PDO
            $this->dbh  = new PDO($dsn, $db['DB_USERNAME'], $db['DB_PASSWORD'], [
                PDO::ATTR_PERSISTENT    => true,
                PDO::ATTR_ERRMODE	    => PDO::ERRMODE_EXCEPTION,
            ]);     
            
            // exec more options settings
            $this->dbh->exec("SET NAMES {$db['DB_CHARSET']}");
            $this->dbh->exec("SET COLLATION_CONNECTION = '{$db['DB_COLLATION']}'");
            $this->dbh->exec("USE {$db['DB_DATABASE']}");

            $this->connection    = [
                'status'    => self::ERROR_200, 
                'message'   => 'Connection successful', 
                'driver'    => $this->dbh
            ];

            return $this;
        } catch(PDOException $e){
            $this->connection    = [
                'status'    => self::ERROR_404, 
                'message'   => $e->getMessage(), 
                'driver'    => $this->dbh
            ];

            return $this;
        }
    }

    /**
     * Initilize and Set the Database Configuration on constructor
     * 
     * @param array $options
     * DB_HOST
     * DB_DATABASE
     * DB_USERNAME
     * DB_PASSWORD
     * 
     * @return void\initConfiguration
     */
    public function initConfiguration(?array $options = [])
    {
        ModelMethod::initConfiguration( $options );
    }

    /**
     * Table names's on index arrays
     * Optimize multiple table
     * 
     * @param array $table
     * 
     * @return object\optimize
     */
    public function optimize(?array $table = [])
    {
        $this->closeQuery();

        $this->modelQuery = false;

        // micro start time
        $this->start_time = microtime(true);

        // add to global table property
        $this->table = $table;

        // filter array
        $this->table = ModelMethod::arrayWalkerTrim($this->table);
        array_walk($this->table, function (&$value, $key){
            $value = "`{$value}`";
        });

        // convert to string
        $this->table = implode(', ', $this->table);

        // save to temp query data
        $this->setQueryProperty();

        // analize
        $analize = $this->analizeTable();
        if($analize['response'] !== self::ERROR_200){
            return $analize;
        }

        // repair
        $repair = $this->repairTable();
        if($repair['response'] !== self::ERROR_200){
            return $repair;
        }
        
        return [
            'response'  => self::ERROR_200,
            'analize'   => $analize,
            'repair'    => $repair,
        ];
    }

    /**
     * Table name
     * This is being used on all instance of one query
     * 
     * @param string $table
     * 
     * @return object\table
     */
    public function table(?string $table)
    {
        $this->closeQuery();

        // micro start time
        $this->start_time = microtime(true);

        $this->table = $table;

        $this->modelQuery   = true;
        $this->rawQuery     = false;

        return $this;
    }

    /**
     * Query raw DB
     *
     * @param string $query 
     * @return void|object\raw
     */ 
    public function raw($query = null)
    {
        $this->closeQuery();
        
        $this->modelQuery   = false;
        $this->rawQuery     = true;
        
        if($this->rawQuery){
            // micro start time
            $this->start_time = microtime(true);

            $this->tempRawQuery = "$query";
        }
        return $this;
    }

    /**
     * Prepare query
     * 
     * @param string $table
     * 
     * @return void\query
     */
    public function query($query)
    {
        try {
            $this->query    = str_replace("{$this->special_key} ", '', $query);
            $this->stmt     = $this->dbh->prepare($this->query);
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true, __FUNCTION__);
        }

        return $this;
    }

    /**
     * Bind query data
     * 
     * @param string $param
     * @param int|bool|null|string $value
     * @param  $type
     * 
     * @return void\bind
     */
    public function bind($param, $value, $type = null)
    {
        if(is_null($type))
        {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        // bind params
        try {
            $this->stmt->bindValue($param, $value, $type);
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true, __FUNCTION__);
        }

        return $this;
    }

    /**
     * Execute query statement
     * 
     * @return array|object|void|null\execute
     */
    public function execute()
    {
        return $this->stmt->execute();
    }

    /**
     * Get last insert ID
     *
     * @return int|string|null|void\lastInsertId
     */
    public function lastInsertId()
    {
        try {
            return $this->dbh->lastInsertId();
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true, __FUNCTION__);
        }
    }

    /**
     * Set order by
     * 
     * @param string $column
     * @param string|null $direction\Default is `ASC`
     * 
     * @return void|object\orderBy
     */ 
    public function orderBy($column, $direction = null)
    {
        // empty check
        if(empty($direction) || is_null($direction)){
            $direction = 'ASC';
        }
        
        // orderBy query
        $this->orderBy = "ORDER BY {$column} {$direction}";

        return $this;
    }

    /**
     * Set orderByRaw
     * 
     * @param string $query
     * 
     * @return void|object\orderByRaw
     */ 
    public function orderByRaw($query = null)
    {
        $this->orderBy = "ORDER BY {$query}";

        return $this;
    }

    /**
     * Get latest query
     * @param string $column\Order by column name
     * Default column has been set to 'id'
     *
     * @return object\latest
     */
    public function latest($column = 'id')
    {
        $this->orderBy($column, 'DESC');

        return $this;
    }

    /**
     * Get oldest query
     * @param string $column\Order by column name
     * Default column has been set to 'id'
     *
     * @return boolean|object\oldest
     */
    public function oldest($column = 'id')
    {
        $this->orderBy($column);

        return $this;
    }

    /**
     * Set random order
     * 
     * @return void|object\inRandomOrder
     */ 
    public function inRandomOrder()
    {
        $this->orderBy = "ORDER BY RAND()";

        return $this;
    }

    /**
     * Set random order
     * 
     * @return void|object\random
     */ 
    public function random()
    {
        $this->inRandomOrder();
        
        return $this;
    }

    /**
     * Set limits
     * 
     * @param string $limit\Default is set to `0`
     * 
     * @return void|object\limit
     */ 
    public function limit($limit = 1)
    {
        // limit
        $this->limitCount = $limit;

        $this->limit = "LIMIT {$this->limitCount}";

        // offset query check
        if( str_contains(strtoupper((string) $this->offset), "OFFSET")  ){
            $this->limit = "LIMIT {$this->offsetCount}, {$this->limitCount}";
        }

        return $this;
    }

    /**
     * Set offset
     * 
     * @param string $offset\Default is set to `0`
     * 
     * @return void|object\offset
     */ 
    public function offset($offset = 0)
    {
        // offset
        $this->offsetCount = $offset;

        // offset query
        $this->offset = "OFFSET {$this->offsetCount}";

        // limit query check
        if( str_contains(strtoupper((string) $this->limit), "LIMIT")  ){
            $this->limit = "LIMIT {$this->offsetCount}, {$this->limitCount}";
        }else{
            $this->limit = "LIMIT {$this->offsetCount}";
        }
        
        return $this;
    }

    /**
     * Define join
     * 
     * @param string $table
     * @param string $foreignColumn
     * @param string $operator
     * @param string $localColumn
     * 
     * @return void|object\join
     */ 
    public function join($table, $foreignColumn, $operator, $localColumn)
    {
        $this->joins[] = [
            'type'          => 'INNER',
            'table'         => $table,
            'foreignColumn' => $foreignColumn,
            'operator'      => $operator,
            'localColumn'   => $localColumn
        ];
        return $this;
    }

    /**
     * Define leftJoin
     * 
     * @param string $table
     * @param string $foreignColumn
     * @param string $operator
     * @param string $localColumn
     * 
     * @return void|object\leftJoin
     */ 
    public function leftJoin($table, $foreignColumn, $operator, $localColumn)
    {
        $this->joins[] = [
            'type'          => 'LEFT',
            'table'         => $table,
            'foreignColumn' => $foreignColumn,
            'operator'      => $operator,
            'localColumn'   => $localColumn
        ];
        return $this;
    }

    /**
     * PDO where clause. Expects three params (only two mandatory)
     * By default if you provide two param (seperator becomes =) equals to. And Value becomes 2nd param
     * If you provide three values then, operator must be the middle param
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * 
     * @return object\where
     */ 
    public function where($column, $operator = null, $value = null)
    {
        // operator
        $temp       = ModelMethod::configWhereClauseOperator($operator, $value);
        $value      = $temp['value'];
        $operator   = $temp['operator'];

        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$column}{$operator}:{$column}",
                'data'  => [
                    'column'    => $column,
                    'operator'  => $operator,
                    'value'     => $value,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column}{$operator}:{$column}",
                'data'  => [
                    'column'    => $column,
                    'operator'  => $operator,
                    'value'     => $value,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO orWhere clause. Expects three params (only two mandatory)
     * By default if you provide two param (operator becomes =) equals to. And Value becomes 2nd param
     * If you provide three values then, operator must be the middle param
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * 
     * @return object\orWhere
     */ 
    public function orWhere($column, $operator = null, $value = null)
    {
        // operator
        $temp       = ModelMethod::configWhereClauseOperator($operator, $value);
        $value      = $temp['value'];
        $operator   = $temp['operator'];

        // or Where query add
        $this->where[] = [
            'query' => " OR {$column}{$operator}:{$column}",
            'data'  => [
                'column'    => $column,
                'operator'  => $operator,
                'value'     => $value,
            ]
        ];
        
        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Column clause. Expects three params (only one or two mandatory)
     * By default if you provide two param (operator becomes =) equals to. And Value becomes 2nd param
     * If you provide three values then, operator must be the middle param
     * 
     * @param string|array $column
     * @param string $operator
     * @param string $column2
     * 
     * @return object\whereColumn
     */ 
    public function whereColumn($column, $operator = null, $column2 = null)
    {
        // operator
        $temp = (array) ModelMethod::configWhereColumnClauseOperator($column, $operator, $column2);

        // Create a placeholder for each value in the array
        $placeholders = implode(' AND ', array_map(function($value){
            return "{$value['column1']}{$value['operator']}{$value['column2']}";
        }, $temp));

        // Adding 'Special Key to Query' as Trackable strings to remove later on
        // As this will allow us bind this data separately
        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$this->special_key} {$placeholders}",
                'data'  => [
                    'column'    => null,
                    'operator'  => null,
                    'value'     => null,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$this->special_key} {$placeholders}",
                'data'  => [
                    'column'    => null,
                    'operator'  => null,
                    'value'     => null,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where column IS NULL
     * 
     * @param string $column
     * 
     * @return object\whereNull
     */ 
    public function whereNull($column)
    {
        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$column} IS NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} IS NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }
        
        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where column IS NOT NULL
     * 
     * @param string $column
     * 
     * @return object\whereNotNull
     */ 
    public function whereNotNull($column)
    {
        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$column} IS NOT NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} IS NOT NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Between columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object\whereBetween
     */ 
    public function whereBetween($column, ?array $param = [])
    {
        // set param
        $param = $param ?? [];

        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$column} BETWEEN :{$param[0]} AND :{$param[1]}",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} BETWEEN :{$param[0]} AND :{$param[1]} ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Not Between columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object\whereNotBetween
     */ 
    public function whereNotBetween($column, ?array $param = [])
    {
        // set param
        $param = $param ?? [];

        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$column} NOT BETWEEN :{$param[0]} AND :{$param[1]}",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} NOT BETWEEN :{$param[0]} AND :{$param[1]} ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Not In columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object\whereIn
     */ 
    public function whereIn($column, ?array $param = [])
    {
        // trim excess strings if any
        $param = ModelMethod::arrayWalkerTrim($param) ?? [];

        // Create a placeholder for each value in the array
        $placeholders = implode(', ', array_map(function($value){
            return ":$value";
        }, $param));

        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$column} IN ($placeholders)",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} IN ($placeholders) ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Not In columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object\whereNotIn
     */ 
    public function whereNotIn($column, ?array $param = [])
    {
        // trim excess strings if any
        $param = ModelMethod::arrayWalkerTrim($param) ?? [];

        // Create a placeholder for each value in the array
        $placeholders = implode(', ', array_map(function($value){
            return ":$value";
        }, $param));

        // if query already exists
        if(count($this->where) > 0){
            $this->where[] = [
                'query' => " AND {$column} NOT IN ($placeholders)",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} NOT IN ($placeholders) ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Group By clause.
     * 
     * @param string $column
     * @return object\groupBy
     */ 
    public function groupBy($column)
    {
        $this->groupBy = $column;

        // not empty
        if(!empty($this->groupBy)){
            $this->groupBy = "GROUP BY {$this->groupBy}";
        }

        return $this;
    }

    /**
     * SELECT by columns
     * @param array $columns
     * 
     * @return object\select
     */ 
    public function select(?array $columns = [])
    {
        $this->selectQuery = true;

        $this->selectColumns = $columns;

        return $this;
    }

    /**
     * Insert query data
     * 
     * @param array $param
     * 
     * @return object\insert
     */ 
    public function insert(?array $param = [])
    {
        if($this->modelQuery){

            // save to temp memory
            $this->saveTempInsertQuery($param);

            $this->query = "INSERT INTO `{$this->table}` ({$this->tempInsertQuery['columns']}) values({$this->tempInsertQuery['values']})";

            // set query
            $this->query($this->query);

            // bind query for param
            foreach($param as $key => $value){
                $this->bind(":$key", $value);
            }

            // save to temp query data
            $this->setQueryProperty();

            try {
                // try execute
                $this->execute();

                // get data
                $data = $this->table($this->table)->where('id', $this->lastInsertId())->limit(1)->first();

                // close query after execution
                $this->getQueryResult( $data );

                return [
                    'response'  => self::ERROR_200, 
                    'message'   => 'Inserted',
                    'time'      => (microtime(true) - $this->start_time), 
                    'data'      => $data
                ];
            } catch (\Throwable $th) {
                return $this->errorTemp($th, true, __FUNCTION__);
            }
        }
    }

    /**
     * Update query data
     * 
     * @param array $param
     * 
     * @return object\update
     */ 
    public function update(?array $param = [])
    {
        if($this->modelQuery){

            // save to temp memory
            $this->saveTempUpdateQuery($param);

            $this->query = "UPDATE `{$this->table}` SET {$this->tempUpdateQuery} {$this->tempQuery}";

            // set query
            $this->query($this->query);

            // bind query for param
            foreach($param as $key => $value){
                $this->bind(":$key", $value);
            }

            // bind query for where clause
            $this->bindWhereQuery();

            // save to temp query data
            $this->setQueryProperty();

            try {
                // try execute
                $this->execute();

                // get message
                $message = $this->stmt->rowCount();

                // close query after execution
                $this->getQueryResult( $message );

                return [
                    'response'  => self::ERROR_200, 
                    'message'   => $message,
                    'time'      => (microtime(true) - $this->start_time), 
                    'data'      => (object) $param
                ];
            } catch (\Throwable $th) {
                return $this->errorTemp($th, true, __FUNCTION__);
            }
        }
    }

    /**
     * Increment and Update query data
     * 
     * @param string $column
     * @param int|array $count
     * @param array $param
     * 
     * @return object\increment
     */ 
    public function increment(?string $column, $count = 1, $param = [])
    {
        if($this->modelQuery){

            // operator
            $temp  = ModelMethod::configIncrementOperator($column, $count, $param);

            // save to temp memory
            $this->saveTempUpdateQuery($temp['param']);

            // save temp increment to memory
            $this->saveTempIncrementQuery($temp);

            $this->query = "UPDATE `{$this->table}` SET {$this->tempIncrementQuery} {$this->tempUpdateQuery} {$this->tempQuery}";

            // set query
            $this->query($this->query);

            // bind increment data
            $this->bind(":{$temp['count']}", $temp['count']);

            // bind query for param
            foreach($temp['param'] as $key => $value){
                $this->bind(":$key", $value);
            }

            // bind query for where clause
            $this->bindWhereQuery();

            // save to temp query data
            $this->setQueryProperty();

            try {
                // try execute
                $this->execute();

                // get message
                $message = $this->stmt->rowCount();

                // close query after execution
                $this->getQueryResult( $message );

                return [
                    'response'  => self::ERROR_200, 
                    'message'   => $message,
                    'time'      => (microtime(true) - $this->start_time), 
                    'data'      => (object) $temp['param']
                ];
            } catch (\Throwable $th) {
                return $this->errorTemp($th, true, __FUNCTION__);
            }
        }
    }

    /**
     * Decrement and Update query data
     * 
     * @param string $column
     * @param int|array $count
     * @param array $param
     * 
     * @return object\decrement
     */ 
    public function decrement(?string $column, $count = 1, $param = [])
    {
        if($this->modelQuery){

            // operator
            $temp = ModelMethod::configIncrementOperator($column, $count, $param);

            // save to temp memory
            $this->saveTempUpdateQuery($temp['param']);

            // save temp increment to memory
            $this->saveTempIncrementQuery($temp, false);

            $this->query = "UPDATE `{$this->table}` SET {$this->tempIncrementQuery} {$this->tempUpdateQuery} {$this->tempQuery}";

            // set query
            $this->query($this->query);

            // bind increment data
            $this->bind(":{$temp['count']}", $temp['count']);

            // bind query for param
            foreach($temp['param'] as $key => $value){
                $this->bind(":$key", $value);
            }

            // bind query for where clause
            $this->bindWhereQuery();

            // save to temp query data
            $this->setQueryProperty();

            try {
                // try execute
                $this->execute();

                // get message
                $message = $this->stmt->rowCount();

                // close query after execution
                $this->getQueryResult( $message );

                return [
                    'response'  => self::ERROR_200, 
                    'message'   => $message,
                    'time'      => (microtime(true) - $this->start_time), 
                    'data'      => (object) $temp['param']
                ];
            } catch (\Throwable $th) {
                return $this->errorTemp($th, true, __FUNCTION__);
            }
        }
    }

    /**
     * Delete query data
     * If message return number >=1\ Then data has been deleted
     * If return 0\ No data was deleted
     * 
     * @return object\delete
     */ 
    public function delete()
    {
        if($this->modelQuery){

            // query build
            $this->query = "DELETE FROM `{$this->table}` {$this->tempQuery}";

            // set query
            $this->query($this->query);

            // bind query for where clause
            $this->bindWhereQuery();

            // save to temp query data
            $this->setQueryProperty();

            try {
                // try execute
                $this->execute();

                // get message
                $message = $this->stmt->rowCount();

                // close query after execution
                $this->getQueryResult( $message );

                return [
                    'response'  => $message === self::COUNT ? self::ERROR_400 : self::ERROR_200, 
                    'message'   => $message === self::COUNT ? "#{$message} Success but Nothing to delete" : "#{$message} Delete successful",
                    'time'      => (microtime(true) - $this->start_time), 
                    'data'      => $message, 
                ];
            } catch (\Throwable $th) {
                return $this->errorTemp($th, true, __FUNCTION__);
            }
        }
    }

    /**
     * Compile Raw or Normal Query data
     * 
     * @return void|object\compileQuery
     */ 
    public function compileQuery()
    {
        // raw query
        if($this->rawQuery){
            
            // query builder
            $this->compileQueryBuilder();
        }
        // pagination
        elseif($this->PaginateQuery){

            // reset count
            $this->countQuery = false;

            // query builder
            $this->compileQueryBuilder();
        }
        // other query
        elseif($this->modelQuery){
            // query builder
            $this->compileQueryBuilder();
        }
        
        return $this;
    }

    /**
     * Close all queries and restore back to default
     *
     * @return void\closeQuery
     */
    public function closeQuery()
    {
        $this->query                = null;
        $this->stmt                 = null;
        $this->tempQuery            = null;
        $this->tempRawQuery         = null;
        $this->tempUpdateQuery      = null;
        $this->tempIncrementQuery   = null;
        $this->tempInsertQuery      = null;
        $this->limit                = null;
        $this->limitCount           = null;
        $this->offset               = null;
        $this->offsetCount          = null;
        $this->groupBy              = null;
        $this->where                = [];
        $this->joins                = [];
        $this->selectColumns        = [];
        $this->selectQuery          = false;
        $this->modelQuery           = false;
        $this->rawQuery             = false;
        $this->PaginateQuery        = false;
        $this->countQuery           = false;
    }

    /**
     * Allow pagination
     *
     * @return object\allowPaginate
     */
    protected function allowPaginate()
    {
        $this->PaginateQuery    = true;

        return $this;
    }

    /**
     * Allow query count(*)
     *
     * @return object\allowCount
     */
    protected function allowCount()
    {
        $this->countQuery = true;

        return $this;
    }

    /**
     * Get last insert ID
     * @param bool $type true or false
     * If true then it return an OBJECT data
     * Else returns and ARRAY data
     *
     * @return array|object|void\tryFetchAll
     */
    protected function tryFetchAll($type = true)
    {
        try {
            $getType = $type ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;

            return $this->stmt->fetchAll($getType);
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true, __FUNCTION__);
        }
    }

    /**
     * Get Database Constant configuration data
     * @param string $key\* DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD
     * 
     * @return string|null\getConfig
     */
    protected function getConfig($key = 'DB_HOST')
    {
        return ModelMethod::getConfig( $key );
    }

    /**
     * Create error temp
     * 
     * @param object $e\Instance of Throwable or PDOException class
     * @param bool $prepareQueryError
     * @param string $method
     * 
     * @return string|void\errorTemp
     */ 
    protected function errorTemp(Throwable|PDOException $e, $prepareQueryError = false, $method = 'default')
    {
        $dbError        = "";
        $exception      = (new Exception);
        $queryString    = $this->stmt->queryString ?? 'Unknown Error\\';

        // if for prepared query error only
        if($prepareQueryError){
            if($this->connection['status'] != self::ERROR_200){
                $queryString = 'Database Connection Error\\';
                $dbError = "<<\\Table name `{$this->connection['message']}`>>";
            }else{
                $queryString = $this->stmt->queryString ?? 'Unknown Error\\';
            }
        }

        return [
            'response' => self::ERROR_404, 
            'message' => preg_replace(
                '/^[ \t]+|[ \t]+$/m', '', 
                "   $dbError
                    {$exception->getTraceAsString()}
                    <<\\Query>> {$queryString}
                    <br><br>
                    <<\\PDO::ERROR>> {$e->getMessage()}
                "
            )
        ];
    }

    /**
     * Analize tables
     * 
     * @return object\analizeTable
     */
    private function analizeTable()
    {
        // set query
        $this->query("ANALYZE TABLE {$this->table}");

        try {
            $this->execute();

            // message
            $result = ModelMethod::convertOptimizeErrorTemp( $this->tryFetchAll(false) );

            // if an error
            if($result['error']){
                return [
                    'response'  => self::ERROR_404, 
                    'message'   => ModelMethod::replaceLeadEndSpace($result['message']),
                    'time'      => (microtime(true) - $this->start_time),
                ];
            }

            return [
                'response'  => self::ERROR_200, 
                'message'   => ModelMethod::replaceLeadEndSpace($result['message']),
                'time'      => (microtime(true) - $this->start_time),
            ];
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true, __FUNCTION__);
        }
        
        return $this;
    }

    /**
     * Repair tables
     * @return object\optimize
     */
    private function repairTable()
    {
        // set query
        $this->query("REPAIR TABLE {$this->table}");

        try {
            $this->execute();
            
            // message
            $result = ModelMethod::convertOptimizeErrorTemp( $this->tryFetchAll(false) );

            // if an error
            if($result['error']){
                return [
                    'response'  => self::ERROR_404, 
                    'message'   => ModelMethod::replaceLeadEndSpace($result['message']),
                    'time'      => (microtime(true) - $this->start_time),
                ];
            }

            return [
                'response'  => self::ERROR_200, 
                'message'   => ModelMethod::replaceLeadEndSpace($result['message']),
                'time'      => (microtime(true) - $this->start_time),
            ];
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true, __FUNCTION__);
        }
        
        return $this;
    }

    /**
     * Save data from each clause into a temp variable 
     * 
     * @return string|void\restructureQueryString
     */ 
    private function restructureQueryString()
    {
        // save into property
        $joins = ModelMethod::formatJoinQuery($this->joins);
        $limit = ModelMethod::getLimitQuery($this->limit);
        
        // if raw query
        if($this->rawQuery){
            
            // if query is count(*) only | perform SELECT By columns query 
            if($this->countQuery || $this->selectQuery){
                if(!is_null($this->table)){
                    $query = "SELECT 
                                {$this->formatSelectQuery()} 
                                FROM ({$this->rawCountQueryFinder()}) 
                                `{$this->table}`";
                }else{
                    $query = $this->rawCountQueryFinder();
                }
            }else{
                if(!is_null($this->table)){
                    $query = "SELECT * 
                                FROM ({$this->rawCountQueryFinder()}) 
                                `{$this->table}`";
                }else{
                    $query = $this->rawCountQueryFinder();
                }
            }

            return ModelMethod::replaceWhiteSpace( 
                "{$query} 
                {$joins} 
                {$this->tempQuery} 
                {$this->groupBy} 
                {$this->orderBy} 
                {$limit}"
            );
        }
        // if query is count(*) only | perform SELECT By columns query 
        else if($this->countQuery || $this->selectQuery){
            $query = "SELECT 
                        {$this->formatSelectQuery()} 
                        FROM `{$this->table}`";
        }else{
            $query = "SELECT * 
                        FROM `{$this->table}`";
        }

        return ModelMethod::replaceWhiteSpace(
            "{$query}
            {$joins} 
            {$this->tempQuery} 
            {$this->groupBy} 
            {$this->orderBy} 
            {$limit}"
        );
    }

    /**
     * Compile query build
     * 
     * @return void\compileQueryBuilder
     */ 
    private function compileQueryBuilder()
    {
        // set query
        $this->query( $this->restructureQueryString() );

        // bind query
        $this->bindWhereQuery();

        // save to temp query data
        $this->setQueryProperty();
    }

    /**
     * Format array of selected columns passed by the users
     * 
     * @return string|void\formatSelectQuery
     */ 
    private function formatSelectQuery()
    {
        if(is_array($this->selectColumns) && count($this->selectColumns) === 0){
            if($this->countQuery){
                return "count(*)";
            }
            return "*";
        }else{
            // trim excess strings if any
            $this->selectColumns = ModelMethod::arrayWalkerTrim($this->selectColumns);

            return implode(', ', $this->selectColumns);
        }
    }

    /**
     * Format raw data when trying to count as result
     * 
     * @return string\rawCountQueryFinder
     */ 
    private function rawCountQueryFinder()
    {
        if($this->countQuery && !$this->PaginateQuery){
            if(!is_null($this->tempRawQuery)){
                $this->tempRawQuery =  str_replace("SELECT *", "SELECT count(*)", $this->tempRawQuery);
            }
        }

        return $this->tempRawQuery;
    }

    /**
     * Save data from each clause into a temp variable 
     * using the implode, to convert array data into a string and add to all instance
     * 
     * @param array $query
     * 
     * @return object\saveTempQuery
     */ 
    private function saveTempQuery(?array $query = [])
    {
        $this->tempQuery = ModelMethod::saveTempQuery($query);

        return $this;
    }

    /**
     * Save data from each clause into a temp variable 
     * using the implode, to convert array data into a string and add to all instance
     * 
     * @param array $param
     * 
     * @return object\saveTempUpdateQuery
     */ 
    private function saveTempUpdateQuery(?array $param = [])
    {
        $this->tempUpdateQuery = ModelMethod::saveTempUpdateQuery($param);
        
        return $this;
    }

    /**
     * Save data from increment queries
     * 
     * @param array $data
     * @param bool $type
     * true for increment|false for decrement
     * 
     * @return object
     */ 
    private function saveTempIncrementQuery($data = [], $type = true)
    {
        $this->tempIncrementQuery = ModelMethod::saveTempIncrementQuery($data, $type);
        
        return $this;
    }

    /**
     * Create insert value pairs
     * 
     * @param array $param
     * @return object
     */ 
    private function saveTempInsertQuery(?array $param = [])
    {
        $this->tempInsertQuery = ModelMethod::saveTempInsertQuery($param);
        
        return $this;
    }

    /**
     * Bind Where Querie
     * 
     * @param array $query
     * 
     * @return object\bindWhereQuery
     */ 
    private function bindWhereQuery()
    {
        foreach($this->where as $key => $value){
            // if Query is neither NULL/IS NULL/WHERE_COL_KEY
            // then we bind the data received

            // get query string and convert to UPPERCASE
            $query = strtoupper($value['query']);

            if( !( str_contains($query, "IS NOT NULL") 
                    || str_contains($query, "IS NULL") 
                    || str_contains($query, "{$this->special_key}") 
                ) ){

                // If Query is
                // BETWEEN/NOT BETWEEN/NOT IN/IN >>bind
                if((str_contains($query, "BETWEEN") || str_contains($query, "IN ("))){
                    
                    // values will be an index array
                    // bind each value
                    foreach($value['data']['value'] as $array_value){
                        $this->bind(":{$array_value}", $array_value);
                    }
                }else{
                    // normal >>bind
                    $this->bind(":{$value['data']['column']}", $value['data']['value']);
                }
            }else{
                // unset non usable keys from where clause
                unset($this->where["{$key}"]);
            }
        }
        
        return $this;
    }

}

