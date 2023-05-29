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
namespace builder\Database;

use builder\Database\Schema\Insertion;
use builder\Database\Traits\DBSetupTrait;

class DB extends Insertion{
    
    use DBSetupTrait;
    
    /**
     * Extending Constructor opitons if Available
     * - If User Extends the DB::Class and has their own __construct()
     * - They must call the parent::__construct();
     * - For Loaded Data to be returned
     * 
     * @param array $options
     */
    public function __construct(?array $options = []) 
    {
        if (!$this->initialized) {
            $this->initializeSetup($options);
            $this->initialized = true;
        }
    }

}