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
namespace UltimateOrmDatabase;

use UltimateOrmDatabase\Schema\Model;

class DB extends Model{
    
    public function __construct(?array $options = []) {
        parent::__construct($options);
    }
}