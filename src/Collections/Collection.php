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
use builder\Database\Collections\CollectionProperty;
use builder\Database\Collections\Traits\RelatedTrait;
use builder\Database\Collections\Traits\CollectionTrait;


class Collection extends CollectionProperty implements IteratorAggregate, ArrayAccess
{
    use CollectionTrait, RelatedTrait;

    /**
     * Create a new collection.
     *
     * @param  mixed $items
     * 
     * @param  mixed $instance
     * - [optional] Used on ORM Database Only
     * Meant for easy manupulation of collection instance
     * This doesn't have affect on using this the Collection class on other projects
     */
    public function __construct(mixed $items = [], mixed $instance = null)
    {
        $this->isBuilderOrPaginator($instance);
        $this->isProxies();
        $this->convertOnInit($items);
    }
    
    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator(
            $this->wrapArrayIntoNewCollections()
        );
    }

    /**
     * Get Pagination Links
     * @param array $options
     *
     * @return \builder\Database\Schema\Pagination\links()
     */
    public function links(?array $options = [])
    {
        if(isset($this->isPaginate)){
            $this->paginationBuilder();
            $this->builder->links($options);
        }
    }

    /**
     * Format Pagination Data
     * @param array $options
     * 
     * @return \builder\Database\Schema\Pagination\showing()
     */
    public function showing(?array $options = [])
    {
        if(isset($this->isPaginate)){
            $this->builder->showing($options);
        }
    }

    /**
     * With this helper we're able to build support
     * for multiple pagination on same page without conflicts
     * 
     * @return void
     */
    public function paginationBuilder()
    {
        if(isset($this->isPaginate)){
            $this->builder->pagination->pageParam = $this->builder->pageParam;
            $this->builder->pagination->perPageParam = $this->builder->perPageParam;
        }
    }

}