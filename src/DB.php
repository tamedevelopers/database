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

        // configuring pagination settings 
        if ( ! defined('APP_ORM_DOT_ENV') ) {
            $this->configurePagination($options);
        } else{
            // if set to allow global use of ENV Autoloader Settings
            if(is_bool(APP_ORM_DOT_ENV['allow'])){
                $this->configurePagination(APP_ORM_DOT_ENV);
            }else{
                $this->configurePagination($options);
            }
        }
        
    }
}