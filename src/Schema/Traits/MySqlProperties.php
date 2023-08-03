<?php

declare(strict_types=1);

namespace builder\Database\Schema\Traits;


trait MySqlProperties{
    
    /**
     * The current mysql
     *
     * @var mixed
     */
    public $connection;

    /**
     * Database Manager Instance
     *
     * @var mixed
     */
    public $dbManager;

    /**
     * @var \builder\Database\Capsule\Manager
     */
    public $manager;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $query;
    
    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings = [
        'select'    => [],
        'from'      => [],
        'join'      => [],
        'where'     => [],
        'groupBy'   => [],
        'having'    => [],
        'order'     => [],
    ];

    /**
     * @var mixed
     */
    public $aggregate;

    /**
     * @var array
     */
    public $columns;

    /**
     * Indicates if the query returns distinct results.
     *
     * Occasionally contains the columns that should be distinct.
     *
     * @var bool|array
     */
    public $distinct = false;

    /**
     * @var string
     */
    public $from;

    /**
     * @var array
     */
    public $wheres = [];

    /**
     * @var array
     */
    public $joins;

    /**
     * @var array
     */
    public $orders;

    /**
     * @var array
     */
    public $havings;

    /**
     * @var array
     */
    public $groups;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $offset;

    /**
     * The callbacks that should be invoked before the query is executed.
     *
     * @var array
     */
    public $beforeQueryCallbacks = [];

    /**
     * @var array
     */
    public $operators = [
        "=",
        "<",
        ">",
        ">=",
        "<=",
        "!=",
        "<>",
        "<=>",
        "&",
        "|",
        "<<",
        ">>",
        "like",
        "not like",
        "is",
        "is not",
    ];

    /**
     * @var float|int
     */
    public $runtime = 0.00;
}



