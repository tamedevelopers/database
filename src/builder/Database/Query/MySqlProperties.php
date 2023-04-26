<?php

declare(strict_types=1);

namespace builder\Database\Trait;


trait MySqlProperties{


    /**
     * @var array|null
     */
    protected $connection;

    /**
     * @var object
     */
    protected $console;

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
     * @var mixed
     */
    public $attributes;

    /**
     * @var mixed
     */
    public $attribute;

    /**
     * @var string|null
     */
    protected $special_key = '__**';

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
     * @var array
     */
    protected $paramValues = [];

    /**
     * @var bool
     */
    protected $selectQuery = false;

    /**
     * @var bool
     */
    protected $PaginateQuery = false;

    /**
     * @var bool
     */
    protected $countQuery = false;

    /**
     * @var bool
     */
    protected $modelQuery  = false;

    /**
     * @var bool
     */
    protected $rawQuery    = false;

    /**
     * @var bool
     */
    protected $removeTags  = false;

    /**
     * @var array
     */
    protected $timer = [
        'start'   => 0.00,
        'end'     => 0.00,
        'runtime' => 0.00,
    ];

    /**
     * @var array
     */
    protected $runtime = 0.00;
    
    /**
     * @var mixed
     */
    protected $getQuery;

    /**
     * Convert data to array
     * @param mixed $data
     * 
     * @return array\builder\Database\toArray
     */ 
    public function toArray(mixed $data)
    {
        return json_decode( json_encode($data), TRUE);
    }
    
    /**
     * Convert data to object
     * @param mixed $data
     * 
     * @return mixed\builder\Database\toObject
     */ 
    public function toObject(mixed $data)
    {
        return json_decode( json_encode($data), FALSE);
    }
    
    /**
     * Convert data to json
     * @param mixed $data
     * 
     * @return mixed\builder\Database\toJson
     */ 
    public function toJson(mixed $data)
    {
        return json_encode( $data );
    }
}



