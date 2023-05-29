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


use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use builder\Database\Collections\CollectionProperty;
use builder\Database\Collections\Traits\RelatedTrait;
use builder\Database\Collections\Traits\CollectionTrait;


class Collection extends CollectionProperty implements IteratorAggregate, ArrayAccess
{
    use CollectionTrait, RelatedTrait;

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];
    
    
    /**
     * Create a new collection.
     *
     * @param  array $items
     */
    public function __construct($items = [])
    {
        $this->unescapeIsObjectWithoutArray = self::checkProxiesType();
        $this->items = $this->convertOnInit($items);

        // if pagination request is `true`
        if(self::$is_paginate){
            $tempItems          = $this->items;
            $this->items        = $tempItems['data'] ?? [];
            self::$pagination   = $tempItems['pagination'] ?? false;
        }
    }
    
    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator() : Traversable
    {
        // On interation (foreach) 
        // Wrap items into instance of CollectionMapper
        return new ArrayIterator(
            $this->wrapArrayIntoCollectionMappers($this->items)
        );
    }

    /**
     * Get Pagination Links
     * @param array $options
     *
     * @return string\builder\Database\Pagination\links
     */
    public function links(?array $options = [])
    {
        if(self::$pagination){
            self::$pagination->links($options);
        }
    }

    /**
     * Format Pagination Data
     * @param array $options
     * 
     * @return string\builder\Database\Pagination\showing
     */
    public function showing(?array $options = [])
    {
        if(self::$pagination){
            self::$pagination->showing($options);
        }
    }

    /**
     * Get Pagination Numbers
     * @param mixed $key
     *
     * @return string
     */
    public function numbers(mixed $key = 0)
    {
        if(self::$is_paginate){
            $key        = (int) $key + 1;
            $pagination = $this->getPagination();
            return ($pagination->offset + $key);
        }

        return $key;
    }

}