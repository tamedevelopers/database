<?php

declare(strict_types=1);

namespace builder\Database\Collections\Traits;

use builder\Database\Capsule\Str;
use builder\Database\Schema\Builder;
use builder\Database\Schema\Pagination\Paginator;
use builder\Database\Collections\CollectionMapper;

/**
 * @property bool $isProxyAllowed
 * @property bool $isPaginate
 * @property bool $isBuilder
 * @property array $proxies
 * @property mixed $builder
 */
trait CollectionTrait{

    /**
     * Get Pagination Data
     * 
     * @return mixed
     */
    public function getPagination()
    {
        if(isset($this->isPaginate)){
            $pagination = $this->builder->pagination;
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
     * Convert arrays into instance of Collection
     * 
     * @return mixed
     */
    protected function wrapArrayIntoNewCollections()
    {
        // check if valid array data
        if (!$this->isProxyAllowed && is_array($this->items) && !empty($this->items)) {
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
     * @return void
     */
    protected function isProxies()
    {
        if(self::$isBuilder){
            if(in_array(Str::lower($this->builder->method), self::$proxies)){
                $this->isProxyAllowed = true;
            }
        }

        if($this->isProxyAllowed){
            $this->builder = null;
        }
    }
    
    /**
     * Get Instance of ORM Builder or Paginator
     * @param  mixed $expression
     * @return void
     */
    protected function isBuilderOrPaginator($expression = null)
    {
        $this->builder = $expression;
        if ($expression instanceof Builder){
            self::$isBuilder = true;
        } else{
            self::$isBuilder = false;
        }
        if ($expression instanceof Paginator){
            $this->isPaginate = true;
        }
    }

}