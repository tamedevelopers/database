<?php

declare(strict_types=1);

namespace builder\Database\Collections;

use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;

class CollectionMapper implements IteratorAggregate, ArrayAccess
{
    private $attributes;
    private $getQuery;
    private $key;
    static private $check_paginate = false;
    static private $pagination;

    /**
     * Create a new collection.
     *
     * @param  mixed $items
     */
    public function __construct($items = [], mixed $key = 0, ?bool $check_paginate = false, mixed $pagination = null)
    {
        $this->attributes  = $this->convertOnInit($items);
        $this->getQuery     = get_query();
        $this->key          = ($key + 1);

        // if pagination request is `true`
        if($check_paginate){
            self::$check_paginate  = $check_paginate;
            self::$pagination      = $pagination->pagination ?? null;
        }
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

     /**
     * Check if an item exists in the collection.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get Pagination Object
     * 
     * @return mixed
     */
    public function getPagination()
    {
        if(self::$check_paginate){
            $pagination = self::$pagination;
            return (object) [
                'limit'         => (int) $pagination->limit,
                'offset'        => (int) $pagination->offset,
                'page'          => (int) $pagination->page,
                'pageCount'     => (int) $pagination->pageCount,
                'perPage'       => (int) $pagination->perPage,
                'totalCount'    => (int) $pagination->totalCount,
            ];
        }
    }

    /**
     * Get Pagination Numbers
     *
     * @return string
     */
    public function numbers()
    {
        if(self::$check_paginate){
            $pagination = $this->getPagination();
            return ($pagination->offset + $this->key);
        }

        return $this->key;
    }

    /**
     * return items collection as an array
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->toArray();
    }

    /**
     * return items collection as an object
     *
     * @return object
     */
    public function getOriginal()
    {
        return $this->toObject();
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Determine if the collection is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->attributes);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return  is_array($this->attributes) 
                ? count($this->attributes)
                : 0;
    }

    /**
     * Convert data to array
     * 
     * @return array
     */ 
    public function toArray()
    {
        return json_decode( json_encode($this->attributes), true);
    }
    
    /**
     * Convert data to object
     * 
     * @return object
     */ 
    public function toObject()
    {
        return json_decode( json_encode($this->attributes), false);
    }
    
    /**
     * Convert data to json
     * 
     * @return string
     */ 
    public function toJson()
    {
        return json_encode( $this->attributes );
    }

    /**
     * Get database query
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->getQuery;
    }

    /**
     * Convert data to an array on Initializaiton
     * @param mixed $items
     * 
     * @return array
     */ 
    private function convertOnInit(mixed $items = null)
    {
        return json_decode( json_encode($items), true);
    }

    /**
     * Dynamically access collection items.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        // convert to array
        $unasignedItems = $this->toArray();
        return $unasignedItems[$key] ?? null;
    }

    /**
     * Dynamically set an item in the collection.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

}