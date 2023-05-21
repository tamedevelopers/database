<?php

declare(strict_types=1);

namespace builder\Database\Traits;


trait ServerTrait{
    
    /**
    * server base dir
    */
    protected $base_dir;

    /**
     * Call not existing method
     * @param string $key ->setDir() | ->setDirectory()
     * @param string $value 
     * 
     * @return void
     */
    public function __call( $key, $value )
    {
        /**
        * base root directory path setting
        */
        if(in_array(strtolower($key), ['setdir', 'setdirectory'])){
            $this->base_dir = $value[0] ?? null;
        }
    }
    
    /**
     * get Directory
     * @param  string base directory path.
     * 
     * @return string|void|null 
     */
    public function getDirectory()
    {
        if(empty($this->base_dir)){
            // get default project root path
            $this->base_dir = $this->clean_path( $this->server_root() );
        }else{
            $this->base_dir = $this->clean_path($this->base_dir);
        }
        
        return $this->base_dir;
    }

    /**
     * Get Server root
     * 
     * @return string
     */
    private function server_root()
    {
        return $this->getServers('server');
    }

    /**
     * Get the base URL and domain information.
     *
     * @param string $mode 
     * - [optional] get direct info of data 
     * - server|domain|protocol
     * 
     * @return mixed
     * - An associative array containing\ server|domain|protocol
     */
    static public function getServers(?string $mode = null)
    {
        // Determine the protocol (http or https)
        $protocol   = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https://' : 'http://';

        // Construct the server root path
        $docRoot = $_SERVER['DOCUMENT_ROOT'];

        // Get the server name (hostname)
        $serverName = $_SERVER['SERVER_NAME'];

        // get Base directory path
        $basePath = self::getMatchedData('init.php');

        // if false, then get absolute path
        if($basePath === false){
            $basePath = self::getRootPathToProject(realpath('.'));
        }

        // Construct the server root URL
        $serverPath = rtrim("{$docRoot}{$basePath}", '/');

        // Construct the domain
        $domainPath = rtrim("{$protocol}{$serverName}{$basePath}", '/');

        // Data
        $data = [
            'server'    => $serverPath,
            'domain'    => $domainPath,
            'protocol'  => $protocol,
        ];

        return $data[$mode] ?? $data;
    }

    /**
     * Get the data of the string that matches the search string in the given backtrace.
     *
     * @param string $searchString The string to search for.
     * 
     * @return string|false 
     * -The matched data or null if no match is found.
     */
    static private function getMatchedData(?string $searchString)
    {
        // backtrace file column data
        $backtrace = array_column(debug_backtrace(), 'file');

        if(is_array($backtrace)){
            foreach ($backtrace as $trace) {
                if (strpos($trace, $searchString) !== false) {
                    return self::getRootPathToProject($trace, $searchString);
                }
            }
        }
        
        return false;
    }

    /**
     * Clean and get Root Path to Project
     *
     * @param string $path
     * @param string $string
     * 
     * @return string|null
     */
    static private function getRootPathToProject(?string $path = null, ?string $string = null)
    {
        // Construct the server root path
        $docRoot = $_SERVER['DOCUMENT_ROOT'];

        $traceData  = str_replace('\\', '/', (string) $path);
        $traceData  = str_replace([$docRoot, (string) $string], '', $traceData);

        return $traceData;
    }

    /**
     * Clean server url path
     * @param string $path 
     * 
     * @return string|null\clean_path
     */
    private function clean_path(?string $path = null)
    {
        $path   = str_replace('\\', '/', "{$path}/");
        $length = strlen($path);

        // Check if the string ends with two forward slashes
        if ($length >= 2 && substr($path, $length - 2) == '//') {
            // Remove one of the trailing forward slashes
            $cleanLink = rtrim($path, '/');
        } else {
            // No double forward slashes, use the original link
            $cleanLink = $path;
        }

        return $cleanLink;
    }

}