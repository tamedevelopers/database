<?php

declare(strict_types=1);

namespace builder\Database\Collections\Traits;


/**
 * @property bool $is_paginate
 * @property mixed $pagination
 * @property bool $unescapeIsObjectWithoutArray
 */
trait RelatedTrait{

    /**
     * Get Pagination Object
     * 
     * @return mixed
     */
    public function getPagination()
    {
        if(self::$is_paginate){
            if(self::$pagination){
                $pagination = self::$pagination->pagination;
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
        return  $this->items[$key] ?? null;
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
        if($this->unescapeIsObjectWithoutArray){
            return true;
        }
        return $this->count() === 0 ? true : false;
    }

    /**
     * Convert data to array
     * 
     * @return array
     */ 
    public function toArray()
    {
        return json_decode( json_encode($this->items), true);
    }
    
    /**
     * Convert data to object
     * 
     * @return object
     */ 
    public function toObject()
    {
        return json_decode( json_encode($this->items), false);
    }
    
    /**
     * Convert data to json
     * 
     * @return string
     */ 
    public function toJson()
    {
        return json_encode($this->items);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return  $this->isArray()
                ? count($this->items)
                : 0;
    }
    
    /**
     * Check if items is an array
     * 
     * @return bool
     */ 
    private function isArray()
    {
        return (is_array($this->items) || $this->items instanceof \Countable);
    }
    
    /**
     * Convert data to an array on Initializaiton
     * @param mixed $items
     * 
     * @return array
     */ 
    private function convertMapperOnInit(mixed $items = null)
    {
        if (self::$is_paginate) {
            return json_decode(json_encode($items), true);
        } elseif (is_array($items)) {
            return $items;
        } elseif ($this->isValidJson((string) $items)) {
            return json_decode($items, true);
        } 

        return $items;
    }
    
    /**
     * Convert data to an array on Initializaiton
     * @param mixed $items
     * 
     * @return array
     */ 
    private function convertOnInit(mixed $items = null)
    {
        // first or insert request
        if ($this->unescapeIsObjectWithoutArray) {
            return json_decode(json_encode($items), true);
        } elseif(is_array($items)){
            return $items;
        } elseif($this->isValidJson((string) $items)) {
            return json_decode($items, true);
        }

        return $items;
    }

    /**
     * Check if a string is valid JSON.
     *
     * @param string $data
     * @return bool
     */
    private function isValidJson(?string $data = null)
    {
        json_decode($data);
        return json_last_error() === JSON_ERROR_NONE;
    }

}