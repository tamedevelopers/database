<?php

declare(strict_types=1);

namespace builder\Database;

use Exception;
use Dotenv\Dotenv;
use builder\Database\Constant;
use builder\Database\Capsule\Manager;
use builder\Database\Capsule\AppManager;
use builder\Database\Traits\ServerTrait;
use builder\Database\Traits\ReusableTrait;

class Env{
    
    use ServerTrait, ReusableTrait;

    /**
     * Handler to hold Symbolic Path
     * When we ->setDirectory
     * - We copy the extended `$base_dir` untouched to this property 
     * - Since Base Directory value should not be changed
     * - So this is a symbolic link to $base_dir and can be changed at any time
     * 
     * @var mixed
     */
    private static $sym_path;

    /**
     * Instance of self class
     * @var mixed
     */
    private static $class;

    /**
     * Define custom Server root path
     * 
     * @param string $path
     * 
     * @return void
     */
    public function __construct(?string $path = null) 
    {
        // auto set the base dir property
        self::setDirectory($path);

        // add to global property
        self::$class = $this;

        // create public path
        self::$sym_path = self::$base_dir;
    }

    /**
     * Initialization of self class
     * @return void
     */
    private static function init() 
    {
        self::$class = new self();
    }

    /**
     * Define custom Directory path to .env file
     * By default we use your server root folder
     * 
     * @param string $path 
     * - [optional] Path to .env Folder Location
     * - Path to .env Folder\Not needed except called statically
     * 
     * @return array
     */
    public static function load(?string $path = null)
    {
        // if sym_path is null
        if(is_null(self::$sym_path) || !(empty($path) && is_null($path))){
            
            // init entire class object
            self::init();

            if(!empty($path)){
                self::$class->getDirectory($path);
    
                // add to global property
                self::$sym_path = self::$class->clean_path($path);
            }
        }

        try{
            $dotenv = Dotenv::createImmutable(self::$sym_path);
            $dotenv->load();
            return [
                'status'    => Constant::STATUS_200,
                'message'   => ".env File Loaded Successfully",
                'path'      => self::$sym_path,
            ];
        }catch(Exception $e){
            return [
                'status'    => Constant::STATUS_404,
                'message'   => sprintf("<<Error>> Folder Seems not to be readable or not exists. \n%s", $e->getMessage()),
                'path'      => self::$sym_path,
            ];
        }
    }

    /**
     * Inherit the load() method and returns an error message 
     * if any or load environment variables
     * @param string $path Path to .env Folder\Not needed exept called statically
     * 
     * @return array|void
     */
    public static function loadOrFail(?string $path = null)
    {
        $getStatus = self::load($path);
        if($getStatus['status'] != Constant::STATUS_200){
            self::$class->dump(
                "{$getStatus['message']} \n" . 
                (new Exception)->getTraceAsString()
            );
            exit(1);
        }

        return $getStatus;
    }

    /**
     * Create .env file or Ignore
     * 
     * @return void
     */
    public static function createOrIgnore()
    {
        // file to file
        $pathToFile = self::formatWithBaseDirectory('.env');
        
        // only attempt to create file if direcotry if valid
        if(is_dir(self::$sym_path)){
            // if file doesn't exist and not a directory
            if(!file_exists($pathToFile) && !is_dir($pathToFile)){
                
                // Write the contents to the new file
                file_put_contents($pathToFile, self::envTxt());
            }
        }
    }

    /**
     * Turn off error reporting and log errors to a file
     * 
     * @param string $logFile The name of the file to log errors to
     * 
     * @return void
     */
    public static function bootLogger() 
    {
        // Directory path
        $dir = self::formatWithBaseDirectory('storage/logs/');

        // create custom file name
        $filename = "{$dir}orm.log";

        self::createDir_AndFiles($dir, $filename);

        // Determine the log message format
        $log_format = "[%s] %s in %s on line %d\n";

        $append     = true;
        $max_size   = 1024*1024;

        // Define the error level mapping
        $error_levels = self::error_levels();

        // If APP_DEBUG = false
        // Turn off error reporting for the application
        if(!self::is_debug()){
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', 0);
        }

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

    /**
     * Update Environment path .env file
     * @param string $key \Environment key you want to update
     * @param string|bool $value \Value allocated to the key
     * @param bool $allow_quote \Allow quotes around value
     * @param bool $allow_space \Allow space between key and value
     * 
     * @return bool
     */
    public static function updateENV(?string $key = null, string|bool $value = null, ?bool $allow_quote = true, ?bool $allow_space = false)
    {
        $path = self::formatWithBaseDirectory('.env');

        if (file_exists($path)) {

            // if isset
            if(Manager::isEnvSet($key)){
                
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
     * Create needed directory and files
     *
     *  @param string $directory
     *  @param string $filename
     *  
     * @return void
     */
    private static function createDir_AndFiles(?string $directory = null, ?string  $filename = null)
    {
        // if \storage folder not found
        if(!is_dir(self::$sym_path. "storage")){
            @mkdir(self::$sym_path. "storage", 0777);
        }

        // if \storage\logs\ folder not found
        if(!is_dir($directory)){
            @mkdir($directory, 0777);
        }

        // If the log file doesn't exist, create it
        if(!file_exists($filename)) {
            touch($filename);
            chmod($filename, 0777);
        }
    }    

    /**
     * GET Error Levels
     *
     * @return array 
     */
    private static function error_levels()
    {
        return array(
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
    }    

    /**
     * GET Application debug
     *
     * @return bool 
     */
    private static function is_debug() 
    {
        return Manager::isEnvBool(env('APP_DEBUG', true));
    }    

    /**
     * Determines if the application is running in local environment.
     *
     * @return bool Returns true if the application is running in local environment, false otherwise.
     */
    private static function is_local()
    {
        // check using default setting
        if(env('APP_ENV') == 'local'){
            return true;
        }
        
        // check if running on localhost
        return !(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' && $_SERVER['SERVER_ADDR'] !== '127.0.0.1');
    }

    /**
     * Sample copy of env file
     * 
     * @return string
     */
    private static function envTxt()
    {
        return (new AppManager)->envDummy();
    }

}