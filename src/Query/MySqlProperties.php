<?php

declare(strict_types=1);

namespace builder\Database\Traits;


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
    protected $dbh;

    /**
     * @var string|null
     */
    protected $special_key = '__**';

    /**
     * @var object|null|void
     */
    protected $stmt;

    /**
     * @var string|null
     */
    protected $query;

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
     * @var string
     */
    protected $groupBy;

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
}



