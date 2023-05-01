<?php

declare(strict_types=1);

namespace builder\Database\Collections\Traits;

use Exception;
use builder\Database\Collections\CollectionMapper;


trait CollectionTrait{

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
     * Instance of Database Paginate request method
     *
     * @var mixed
     */
    static protected $check_paginate;

    /**
     * The methods that can be proxied.
     *
     * @var array
     */
    static protected $proxies = [
        'get'       => ['get'],
        'first'     => ['first', 'firstorfail'],
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
        'firstorfail',
        'insert',
        'insertorignore',
        'paginate',
    ];

    /**
     * Check if is object without array
     *
     * @var bool
     */
    protected $unescapeIsObjectWithoutArray;

    /**
     * Check Proxies Type
     * Check type of Database Method request
     *
     * @return bool
     */
    static protected function checkProxiesType()
    {
        // get Trace
        self::getTrace();

        // if in first or insert proxies
        if(in_array(self::$instance, self::$proxies['first']) || in_array(self::$instance, self::$proxies['insert'])){
            return true;
        }
        
        return false;
    }
    
    /**
     * Get Instance of Database Fetch Method
     *
     * @return bool
     */
    static protected function getTrace() 
    {
        // get Trace
        $getTrace = (new Exception)->getTrace();
        
        // instance functions
        $functions = array_map('strtolower', array_column($getTrace, 'function'));
        
        // get array interests
        $interest = array_intersect(self::$proxies_compact, $functions);

        // reset keys
        if(is_array($interest) && count($interest) > 0){
            $interest = array_values($interest);
        }
        
        // instance of DB fetch request
        self::$instance = $interest[0] ?? null;

        // instance of DB Paginate request
        self::$check_paginate = in_array(self::$instance, self::$proxies['paginate']);
    }

    /**
     * Convert arrays into instance of Collection Mappers
     *
     * @param  mixed  $items
     * 
     * @return array
     */
    protected function wrapArrayIntoCollectionMappers(mixed $items)
    {   
        // if pagination request is true\ The collect the Pagination `data`
        // Otherwise, get the `items` passed as param
        $items = self::$check_paginate
                    ? $items['data'] ?? []
                    : $items;

        if (is_array($items) && count($items) > 0) {
            return array_map(function ($item){
                return new CollectionMapper($item);
            }, $items);
        }

        return $items;
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed  $items
     * 
     * @return array
     */
    protected function getArrayItems($items)
    {
        // first or insert request
        if ($this->unescapeIsObjectWithoutArray) {
            return  !is_null($items)
                    ? $items
                    : null;
        }

        return $items;
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
        return $this->count() === 0 
                    ? true 
                    : false;
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        if(self::$check_paginate){
            $items = $this->items['data'] ?? [];
            return  is_array($items) 
                    ? count($items)
                    : 0;
        } else{
            return  is_array($this->items) 
                    ? count($this->items)
                    : 0;
        }
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
     * Get database query
     *
     * @return array
     */
    public function getQuery()
    {
        return get_query();
    }

}