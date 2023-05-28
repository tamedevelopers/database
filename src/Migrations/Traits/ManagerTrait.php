<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Traits;

trait ManagerTrait{

    /**
     * Css style
     * @var string
     */
    static protected $style = "
        font-family: arial;color: #fff; padding: 3px 5px;font-size: 10px;border-radius: 4px;margin: 0 0 4px;display: inline-block;
    ";
    
    /**
     * Status
     * @var bool
     */
    protected $status_runned = false;

}