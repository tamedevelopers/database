<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Collections;

use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Tamedevelopers\Database\Schema\Builder;
use Tamedevelopers\Database\Collections\CollectionProperty;
use Tamedevelopers\Database\Collections\Traits\RelatedTrait;

class CollectionMapper extends CollectionProperty implements IteratorAggregate, ArrayAccess
{
    use RelatedTrait;

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
     * @param  \Tamedevelopers\Database\Collections\Collection $collection
     * - Instance of Collection
     */
    public function __construct(mixed $items = [], mixed $key = 0, $collection = null)
    {
        $this->convertOnInit($items);
        $this->key  = ((int) $key + 1);
        $this->isPaginate  = $collection->isPaginate;
        $this->builder  = $collection->builder;
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
     * Get Database Builder Instance
     *  
     * @return \Tamedevelopers\Database\Schema\Builder
     */
    public function builder()
    {
        return $this->builder;
    }

    /**
     * Get Pagination Numbers
     *
     * @return string
     */
    public function numbers()
    {
        if($this->isPaginate){
            return ($this->builder->pagination->offset + $this->key);
        }
        
        return $this->key;
    }

}