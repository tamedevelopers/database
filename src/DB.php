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

use builder\Database\Query\MySqlExec;
use builder\Database\Schema\Insertion;

class DB extends Insertion{
    
    /**
     * Extending Settings
     * 
     * @param  array $options
     */
    public function __construct(?array $options = []) {
        parent::__construct($options);

        // configuring pagination settings 
        if ( ! defined('PAGINATION_CONFIG') ) {
            $this->configPagination($options);
        } else{
            // if set to allow global use of ENV Autoloader Settings
            if(is_bool(PAGINATION_CONFIG['allow']) && PAGINATION_CONFIG['allow'] === true){
                $this->configPagination(PAGINATION_CONFIG);
            }else{
                $this->configPagination($options);
            }
        }

        // open default logger
        (new MySqlExec)->autoStartDebugger();
    }
}