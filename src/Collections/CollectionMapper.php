<?php

declare(strict_types=1);

namespace builder\Database\Collections;

use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use builder\Database\Collections\CollectionProperty;
use builder\Database\Collections\Traits\RelatedTrait;

class CollectionMapper extends CollectionProperty implements IteratorAggregate, ArrayAccess
{
    use RelatedTrait;
    
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Array index key
     * @var  mixed
     */
    protected $key;
    
    /**
     * Create a new collection.
     *
     * @param  mixed $items
     * @param  mixed $key
     */
    public function __construct($items = [], mixed $key = 0)
    {
        $this->key    = ((int) $key + 1);
        $this->items  = $this->convertMapperOnInit($items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Get Pagination Numbers
     *
     * @return string
     */
    public function numbers()
    {
        if(self::$is_paginate){
            $pagination = $this->getPagination();
            return ($pagination->offset + $this->key);
        }

        return $this->key;
    }

}