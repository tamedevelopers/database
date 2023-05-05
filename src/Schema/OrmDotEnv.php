<?php

declare(strict_types=1);

namespace builder\Database\Schema;

use Exception;
use Dotenv\Dotenv;
use builder\Database\Constants;
use builder\Database\Traits\ServerTrait;
use builder\Database\Capsule\AppManager;
use builder\Database\Traits\ReusableTrait;

class OrmDotEnv extends Constants{
    
    use ServerTrait, ReusableTrait;

    static private $immutable;
    static private $object;
    static public $path;

    /**
     * Define custom Server root path
     * @param string $path
     * 
     * @return void
     */
    public function __construct(?string $path = null) 
    {
        // if base path was presented
        if(!is_null($path) && !empty($path)){
            $this->base_dir = $path;
        }
        $this->getDirectory($this->base_dir);

        // add to global property
        self::$immutable = $this->base_dir;

        // add to global property
        self::$object = $this;

        // create public path
        self::$path = self::$immutable;
    }

    /**
     * Initialization of self class
     * @return void
     */
    static private function init() 
    {
        self::$object = new OrmDotEnv;
    }

    /**
     * Define custom Directory path to .env file
     * By default we use your server root folder
     * @param string $path Path to .env Folder\Not needed exept called statically
     * 
     * @return array\load
     */
    static public function load(?string $path = null)
    {
        // if immutable is null
        if(is_null(self::$immutable) || !(empty($path) && is_null($path))){
            
            // init entire class object
            self::init();

            if(!empty($path)){
                self::$object->getDirectory($path);
    
                // add to global property
                self::$immutable = self::$object->clean_path($path);
            }
        }

        try{
            $dotenv = Dotenv::createImmutable(self::$immutable);
            $dotenv->load();

            return [
                'response'  => self::ERROR_200,
                'message'    => ".env File Loaded Successfully",
                'path'      => self::$immutable,
            ];
        }catch(Exception $e){
            return [
                'response'  => self::ERROR_404,
                'message'    => $e->getMessage(),
                'path'      => self::$immutable,
            ];
        }
    }

    /**
     * Inherit the load() method and returns an error message 
     * if any or load environment variables
     * @param string $path Path to .env Folder\Not needed exept called statically
     * 
     * @return array|void|\loadOrFail
     */
    static public function loadOrFail(?string $path = null)
    {
        $getStatus = self::load($path);
        if($getStatus['response'] != self::ERROR_200){
            self::$object->dump(
                "{$getStatus['message']} \n" . 
                (new Exception)->getTraceAsString()
            );
            exit();
        }

        return $getStatus;
    }

    /**
     * Create env file or Ignore
     * @return void|\createOrIgnore
     */
    static public function createOrIgnore()
    {
        $path = self::$immutable . ".env";
        
        // if file doesn't exist and not a directory
        if(!file_exists($path) && !is_dir($path)){
            
            // Write the contents to the new file
            file_put_contents($path, self::envTxt());
        }
    }

    /**
     * Update Environment path .env file
     * @param string $key \Environment key you want to update
     * @param string|bool $value \Value allocated to the key
     * @param bool $allow_quote \Allow quotes around value
     * @param bool $allow_space \Allow space between key and value
     * 
     * @return bool\updateENV
     */
    static public function updateENV(?string $key = null, string|bool $value = null, ?bool $allow_quote = true, ?bool $allow_space = false)
    {
        $path = self::$immutable . '.env';
        if (file_exists($path)) {

            // if isset
            if(self::environmentIsset($key)){
                
                // Read the contents of the .env file
                $lines = file($path);

                // Loop through the lines to find the variable
                foreach ($lines as &$line) {
                    // Check if the line contains the variable
                    if (strpos($line, $key) === 0) {

                        // get space seperator value
                        $separator = $allow_space ? " = " : "=";

                        // check for boolean value
                        if(is_bool($value)){
                            // Update the value of the variable
                            $line = "{$key}=" . ($value ? 'true' : 'false') . PHP_EOL;
                        }else{
                            // check if quote is allowed
                            if($allow_quote){
                                // Update the value of the variable with quotes
                                $line = "{$key}{$separator}\"{$value}\"" . PHP_EOL;
                            }else{
                                // Update the value of the variable without quotes
                                $line = "{$key}{$separator}{$value}" . PHP_EOL;
                            }
                        }
                        break;
                    }
                }

                // Write the updated contents back to the .env file
                file_put_contents($path, implode('', $lines));

                return true;
            }
        }

        return false;
    }

    /**
     * Check if environment key is set
     * @param string $key 
     * 
     * @return bool|string\environmentIsset
     */
    static private function environmentIsset($key)
    {
        if(isset($_ENV[$key])){
            return true;
        }
        return false;
    }

    /**
     * Sample copy of env file
     * 
     * @return string
     */
    static private function envTxt()
    {
        return AppManager::envDummy();
    }

    /**
     * Generates an app KEY
     * 
     * @return string
     */
    static private function generateAppKey($length = 32)
    {
        return AppManager::generateAppKey($length);
    }

}