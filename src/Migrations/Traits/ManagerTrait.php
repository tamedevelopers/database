<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Traits;

trait ManagerTrait{

    /**
     * Css style
     * @var string
     */
    protected static $style = "
        font-family: arial;color: #fff; padding: 3px 5px;font-size: 10px;border-radius: 4px;margin: 0 0 4px;display: inline-block;
    ";
    
    /**
     * Status
     * @var bool
     */
    protected $status_runned = false;

    /**
     * Start session if not started
     * 
     * @return void
     */
    private function sessionStart()
    {
        // header not sent
        if (!headers_sent()) {
            // Start the session has not already been started
            if (session_status() == PHP_SESSION_NONE) {
                @session_start();
            }
        }
    }
    
    /**
     * Creating Session Query
     * - To hold each Migration Request
     * @param mixed $query
     * 
     * @return void
     */
    private function tempMigrationQuery(mixed $query = null)
    {
        // header not sent
        if (!headers_sent()) {
            // Start the session has not already been started
            if (session_status() == PHP_SESSION_NONE) {
                @session_start();
            }
        }

        $_SESSION[$this->session] = json_encode($query);
    }

}