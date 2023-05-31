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
     * @param  mixed $database Instance of ORM Database \builder\Database\DB
     * - [optional] Used on ORM Database Only
     * Meant for easy manupulation of collection instance
     * This doesn't have affect on using this the Collection class on other projects
     */
    public function __construct(mixed $items = [], mixed $database = null)
    {
        $this->database         = $database;
        $this->isProxyAllowed   = self::checkProxiesType();
        $this->items            = $this->convertOnInit($items);
        // if pagination request is `true`
        if($this->isPaginate){
            $this->pagination = $this->database;
        }
    }
    
    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator() : Traversable
    {
        // On interation of (foreach) 
        // Wrap items into instance of CollectionMapper
        if(!$this->isProxyAllowed){
            return new ArrayIterator(
                $this->wrapArrayIntoNewCollections()
            );
        } else{
            // disallow loop through Proxies items collections
            return new ArrayIterator([]);
        }
    }

    /**
     * Get Pagination Links
     * @param array $options
     *
     * @return string\builder\Database\Pagination\links
     */
    public function links(?array $options = [])
    {
        if($this->pagination){
            $this->pagination->links($options);
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
        if($this->pagination){
            $this->pagination->showing($options);
        }
    }

}