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
namespace builder\Database\Collections;


class CollectionProperty
{
    /**
     * The items contained in the collection.
     *
     * @var mixed
     */
    protected $items = [];

    /**
     * Check if is object without array
     *
     * @var bool
     */
    protected $isProxyAllowed = false;

    /**
     * If Instance of Database Pagination Method is true
     * @var mixed
     */
    protected $isPaginate = false;

    /**
     * If Instance of \builder\Database\DB is true
     * @var bool
     */
    protected $isDBInstance = false;

    /**
     * Get pagination items
     *
     * @var mixed\builder\Database\DB
     */
    protected $pagination;
    
    /**
     * Instance of ORM Database Class
     *
     * @var mixed\builder\Database\DB
     */
    protected $database;

    /**
     * The methods that can be proxied.
     *
     * @var array
     */
    static protected $proxies = [
        'first',
        'firstorcreate',
        'firstorfail',
        'insert',
        'insertorignore',
    ];

}