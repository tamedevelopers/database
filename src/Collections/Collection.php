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
use builder\Database\Collections\Traits\CollectionTrait;


class Collection implements IteratorAggregate, ArrayAccess
{
    use CollectionTrait;

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
        $this->items = $this->getArrayItems($items);

        // if pagination request is `true`
        if(self::$check_paginate){
            self::$pagination_data  = $this->items['data'] ?? [];
            self::$pagination       = $this->items['pagination'] ?? false;
        }
    }
    
    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator() : Traversable
    {
        // Automatically wrap Mappers into an array
        $this->items = $this->wrapArrayIntoCollectionMappers($this->items);

        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
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
        unset($this->items[$offset]);
    }

    /**
     * Determine if the collection has a given key.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get Pagination Links
     * @param array $options
     *
     * @return string\builder\Database\Pagination\links
     */
    public function links(?array $options = [])
    {
        if(self::$check_paginate){
            if(self::$pagination){
                self::$pagination->links($options);
            }
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
        if(self::$check_paginate){
            if(self::$pagination){
                self::$pagination->showing($options);
            }
        }
    }

    /**
     * Check if an item exists in the collection.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->items[$key]);
    }

    /**
     * Dynamically access collection items.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        // Convert data to array
        $unasignedItems = $this->toArray();

        // check if `unescapeIsObjectWithoutArray` is true - Then return the data or null
        if($this->unescapeIsObjectWithoutArray){
            return $unasignedItems[$key] ?? null;
        } 
        // Then check if data count is === 1, Then return its's data or null
        elseif($this->count() === 1){
            return $unasignedItems[0][$key] ?? null;
        }
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
        $this->items[$key] = $value;
    }

}