<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use builder\Database\Capsule\Manager;

trait DBSetupTrait{
    
    /**
     * Check if Setup Initalization has been carried out already
     * @var bool
     */
    protected $initialized = false;

    /**
     * Extending Settings if Available
     * 
     * @param array $options
     */
    private function initializeSetup(?array $options = []) 
    {
        // init configuration
        $this->initConfiguration($options);
        
        // start db
        $this->startDatabase();

        /**
         * Configuring pagination settings 
         * Only if the Global Constant is not yet defined
         */
        if ( ! defined('PAGINATION_CONFIG') ) {
            $this->configPagination($options);
        } else{
            /**
             * If set to allow global use of ENV Autoloader Settings
             */
            if(Manager::setEnvBool(PAGINATION_CONFIG['allow']) === true){
                $this->configPagination(PAGINATION_CONFIG);
            }else{
                $this->configPagination($options);
            }
        }

        /**
         * Autostart to Handle Error Exceptions by default 
         */
        $this->autoStartDebugger();
    }

}