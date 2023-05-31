<?php

declare(strict_types=1);

namespace builder\Database\Collections\Traits;

use builder\Database\DB;
use builder\Database\Collections\CollectionMapper;

/**
 * @property mixed $pagination
 * @property mixed $database
 * @property array $proxies
 * @property bool $isPaginate
 * @property bool $isDBInstance
 */
trait CollectionTrait{

    /**
     * Convert arrays into instance of Collection
     * 
     * @return mixed
     */
    protected function wrapArrayIntoNewCollections()
    {
        // check if valid array data
        if (!$this->isProxyAllowed && is_array($this->items) && count($this->items) > 0) {
            return array_map(function ($item, $key){
                return new CollectionMapper($item, $key, $this);
            }, $this->items, array_keys($this->items));
        }

        return $this->items;
    }

    /**
     * Check Proxies Type
     * Determine and get ORM Database Method/Function request
     * 
     * @return bool
     */
    protected function checkProxiesType()
    {
        // check database instance
        $this->checkInstanceOfDatabase();
        
        if($this->isDBInstance){
            // if in first or insert proxies
            $function = strtolower((string) $this->database->function);
            if(in_array($function, self::$proxies)){
                return true;
            } elseif($function === 'paginate'){
                // instance of DB Paginate request
                $this->isPaginate = true;
            }
        }
        
        return false;
    }
    
    /**
     * Get Instance of ORM Database
     *
     * @return void
     */
    protected function checkInstanceOfDatabase()
    {
        if ($this->database instanceof DB){
            $this->isDBInstance = true;
        } else{
            $this->isDBInstance = false;
        }
    }

}