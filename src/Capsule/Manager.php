<?php

declare(strict_types=1);

namespace builder\Database\Capsule;

class Manager{
    
    /**
     * Remove all whitespace characters
     * @var string
     */
    public static $regex_whitespace = "/\s+/";

    /**
     * Remove leading or trailing spaces/tabs from each line
     * @var string
     */
    public static $regex_lead_and_end = "/^[ \t]+|[ \t]+$/m";
    
    /**
     * App Debug
     * 
     * @return bool
     */
    public static function AppDebug()
    {
        return self::isEnvBool(env('APP_DEBUG', true));
    }

    /**
     * Set env boolean value
     * @param string $value
     * 
     * @return mixed
     */
    public static function isEnvBool($value)
    {
        if(is_string($value)){
            return trim((string) strtolower($value)) === 'true'
                    ? true
                    : false;
        }

        return $value;
    }

    /**
     * Check if environment key is set
     * @param string $key 
     * 
     * @return bool
     */
    public static function isEnvSet($key)
    {
        return isset($_ENV[$key]) ? true : false;
    }

    /**
     * Set header response code
     * Mainly for firstOrFail()
     * 
     * @return void\builder\Database\Capsule\Manager\setHeaders
     */
    public static function setHeaders()
    {
        // Set HTTP response status code to 404
        http_response_code(404);

        // Flush output buffer
        flush();

        // Exit with response 404
        exit(1);
    }

    /**
     * Remove whitespace from string
     * 
     * @param string $string
     * 
     * @return string
     */ 
    public static function replaceWhiteSpace(?string $string = null)
    {
        return trim(preg_replace(
            self::$regex_whitespace, 
            " ", 
            $string
        ));
    }

    /**
     * Remove leading and ending space from string
     * 
     * @param string $string
     * 
     * @return string
     */ 
    public static function replaceLeadEndSpace(?string $string = null)
    {   
        return preg_replace(self::$regex_lead_and_end, " ", $string);
    }

}