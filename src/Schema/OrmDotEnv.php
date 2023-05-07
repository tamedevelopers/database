<?php

declare(strict_types=1);

namespace builder\Database\Schema;

use Exception;
use Dotenv\Dotenv;
use builder\Database\Constants;
use builder\Database\Capsule\Manager;
use builder\Database\Capsule\AppManager;
use builder\Database\Traits\ServerTrait;
use builder\Database\Traits\ReusableTrait;

class OrmDotEnv extends Constants{
    
    use ServerTrait, ReusableTrait;

    static private $immutable;
    static private $object;
    static public $path;

    /**
     * Define custom Server root path
     * 
     * @param string $path
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
     * Turn off error reporting and log errors to a file
     * 
     * @param string $logFile The name of the file to log errors to
     * 
     * @return void
     */
    static public function error_logger() 
    {
        // Directory path
        $dir = self::$path . "storage/logs/";

        // create custom file name
        $filename = "{$dir}orm.log";

        // if \storage folder not found
        if(!is_dir(self::$path. "storage")){
            @mkdir(self::$path. "storage", 0777);
        }

        // if \storage\logs\ folder not found
        if(!is_dir($dir)){
            @mkdir($dir, 0777);
        }

        // If the log file doesn't exist, create it
        if(!file_exists($filename)) {
            touch($filename);
            chmod($filename, 0777);
        }

        // Determine the log message format
        $log_format = "[%s] %s in %s on line %d\n";

        $append     = true;
        $max_size   = 1024*1024;

        // Define the error level mapping
        $error_levels = array(
            E_ERROR             => 'Fatal Error',
            E_USER_ERROR        => 'User Error',
            E_PARSE             => 'Parse Error',
            E_WARNING           => 'Warning',
            E_USER_WARNING      => 'User Warning',
            E_NOTICE            => 'Notice',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Strict Standards',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
        );

        // if true
        if(self::is_production()){
            // Define the error handler function
            $error_handler = function($errno, $errstr, $errfile, $errline) use ($filename, $append, $max_size, $log_format, $error_levels) {
                // Construct the log message
                $error_level = isset($error_levels[$errno]) ? $error_levels[$errno] : 'Unknown Error';
                $log_message = sprintf($log_format, date('Y-m-d H:i:s'), $error_level . ': ' . $errstr, $errfile, $errline);
    
                // Write the log message to the file
                if ($append && file_exists($filename)) {
                    $current_size = filesize($filename);
                    if ($current_size > $max_size) {
                        file_put_contents($filename, "{$log_message}");
                    } else {
                        file_put_contents($filename, "{$log_message}", FILE_APPEND);
                    }
                } else {
                    file_put_contents($filename, $log_message);
                }
    
                // Let PHP handle the error in the normal way
                return false;
            };
    
            // Set the error handler function
            set_error_handler($error_handler);
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
     * Determines if the application is running in production environment.
     *
     * @return bool Returns true if the application is running in production environment, false otherwise.
     */
    static private function is_production() {
        // check using default setting
        if(Manager::setEnvBool(app_config('APP_DEBUG')) && app_config('APP_ENV') == 'local'){
            return true;
        }
        
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['SERVER_ADDR'] !== '127.0.0.1';
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