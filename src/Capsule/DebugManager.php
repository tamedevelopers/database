<?php

declare(strict_types=1);

namespace builder\Database\Capsule;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use builder\Database\Traits\ReusableTrait;

class DebugManager{
    
    use ReusableTrait;

    private static $whoops;

    /**
     * Boot the DebugManager.
     * If the constant 'ORMDebugManager' is not defined, 
     * it defines it and starts the debugger automatically.
     * 
     * So that this is only called once in entire application life cycle
     */
    public static function boot()
    {
        if(!defined('ORMDebugManager')){
            self::autoStartDebugger();
            define('ORMDebugManager', self::$whoops);
        } 
    }

    /**
     * Autostart debugger for error logger
     * 
     * @return string
     */
    private static function autoStartDebugger()
    {
        // if DEBUG MODE IS ON
        if(Manager::AppDebug()){
            // header not sent
            if (!headers_sent()) {
                // register error handler
                self::$whoops = new Run();
                self::$whoops->pushHandler(new PrettyPageHandler());
                self::$whoops->register();
            }
        } 
    }
    
}