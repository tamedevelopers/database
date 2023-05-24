<?php

declare(strict_types=1);

namespace builder\Database\Collections\Traits;

use Exception;
use builder\Database\Collections\CollectionMapper;
use stdClass;

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
     * Instance of Database Paginate Method
     *
     * @var mixed
     */
    static protected $is_paginate;

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
        self::$is_paginate = in_array(self::$instance, self::$proxies['paginate']);
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
        // check if valid array data
        if (is_array($items) && count($items) > 0) {
            return array_map(function ($item, $key){
                return new CollectionMapper($item, $key, self::$is_paginate, self::$pagination);
            }, $items, array_keys($items));
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
            return  $this->convertOnInit($items);
        }

        return $items;
    }

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
        if($this->unescapeIsObjectWithoutArray){
            return 0;
        } 

        return  $this->isArray() 
                ? count($this->items) 
                : 0;
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
     * Check if items is an array
     * 
     * @return bool
     */ 
    private function isArray()
    {
        return is_array($this->items) ? true : false;
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

}