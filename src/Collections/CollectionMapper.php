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
     * Array index key
     * @var  mixed
     */
    protected $key;
    
    /**
     * Create a new collection.
     *
     * @param  mixed $items
     * @param  mixed $key
     * @param  \builder\Database\Collections\Collection
     * - Instance of Collection
     */
    public function __construct(mixed $items = [], mixed $key = 0, Collection $collection = null)
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