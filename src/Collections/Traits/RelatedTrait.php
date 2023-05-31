<?php

declare(strict_types=1);

namespace builder\Database\Collections\Traits;


/**
 * @property mixed $pagination
 * @property mixed $database
 * @property bool $isPaginate
 * @property bool $isProxyAllowed
 * @property bool $isDBInstance
 */
trait RelatedTrait{

    /**
     * Get Pagination Object
     * 
     * @return mixed
     */
    public function getPagination()
    {
        if($this->pagination){
            $pagination = $this->pagination->pagination;
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
     * Dump SQL Query
     *
     * @return void
     */
    public function toSql()
    {
        if($this->isDBInstance){
            dump( $this->database->dbQuery()->stmt->queryString );
        }
    }

    /**
     * Dumb and Die\DbQuery
     *
     * @return void
     */
    public function dd()
    {
        if($this->isDBInstance){
            dd( $this->database->dbQuery() );
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
        if($this->isProxyAllowed){
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
    private function convertOnInit(mixed $items = null)
    {
        // For ORM Database Proxies and Paginate Data
        // Convert to an array
        if($this->isDBInstance){
            return json_decode(json_encode($items), true);
        } elseif($this->isValidJson($items)) {
            return json_decode($items, true);
        } elseif($this->isNotValidArray($items)){
            return json_decode(json_encode($items), true);
        } 

        return $items;
    }

    /**
     * Check if data is not a valid array
     *
     * @param mixed $data
     * @return bool
     */
    private function isNotValidArray(mixed $data = null)
    {
        if (!is_array($data)) {
            return true;
        }

        // array filter
        $filteredArray = array_filter($data, 'is_array');
    
        return count($filteredArray) === count($data);
    }

    /**
     * Check if a string is valid JSON.
     *
     * @param mixed $data
     * @return bool
     */
    private function isValidJson(mixed $data = null)
    {
        if(is_string($data)){
            json_decode($data);
            return json_last_error() === JSON_ERROR_NONE;
        }

        return false;
    }

}