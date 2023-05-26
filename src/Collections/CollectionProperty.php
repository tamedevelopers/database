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
     * Instance of Database fetch request method
     *
     * @var mixed
     */
    static protected $instance;

    /**
     * Get pagination items
     *
     * @var mixed\builder\Database\DB
     */
    static protected $pagination;

    /**
     * If Instance of Database Pagination Method is true
     * @var mixed
     */
    static protected $is_paginate = false;

    /**
     * The methods that can be proxied.
     *
     * @var array
     */
    static protected $proxies = [
        'get'       => ['get'],
        'first'     => ['first', 'firstorcreate', 'firstorfail'],
        'insert'    => ['insert', 'insertorignore'],
        'paginate'  => ['paginate'],
    ];

    /**
     * The methods that can be proxied.
     *
     * @var array
     */
    static protected $proxies_compact = [
        'get',
        'first',
        'firstorcreate',
        'firstorfail',
        'insert',
        'insertorignore',
        'paginate',
    ];

}