<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;

use Exception;
use Throwable;
use Tamedevelopers\Support\Capsule\Manager;
use Tamedevelopers\Support\Capsule\DebugManager;

trait ExceptionTrait
{
    /**
     * Handle Errors
     * 
     * @param mixed $exception
     * - \Instance of Throwable or PDOException
     * 
     * @return mixed
     */ 
    protected function errorException(Throwable $exception)
    {
        if (Manager::AppDebug()) {
            // create debugger instance
            $debugger = new DebugManager();

            // boot debugger if it has not been booted
            $debugger->boot();

            // hand over the original exception to Whoops
            $debugger::$whoops->handleException($exception);
        }

        exit(1);
    }

    /**
     * Handle Errors
     * 
     * @param mixed $exception
     * - \Instance of Throwable or PDOException
     * 
     * @return mixed
     */ 
    protected static function staticErrorException(Throwable $exception)
    {
        return (new self())->errorException($exception);
    }

    /**
     * Handle the calls to non-existent instance methods.
     * @param string $name
     * @param mixed $args \arguments
     * 
     * @return $this
     */
    public function __call($method, $args) 
    {
        try {
            throw new Exception("Method [{$method}] does not exist in class '" . get_class(new self()) . "'.");
        } catch (\Throwable $th) {
            self::staticErrorException($th);
        }
    }

    /**
     * Handle the calls to non-existent static methods.
     * @param string $name
     * @param mixed $args \arguments
     * 
     * @return $this
     */
    public static function __callStatic($method, $args) 
    {
        //  Uncaught Error: Call to undefined method Tamedevelopers\Database\DB::tabled() in C:\xampp\htdocs\Github\database\tests\testLoop.php:18
        try {
            throw new Exception("Method [{$method}] does not exist in class '" . get_class(new self()) . "'.");
        } catch (\Throwable $th) {
            self::staticErrorException($th);
        }
    }
}
