<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Trait;


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
        if(empty($this->base_dir) || is_null($this->base_dir)){
            // get default project root document path
            $this->base_dir = $this->clean_path( $this->server_root() );
        }else{
            $this->base_dir = $this->clean_path($this->base_dir);
        }
        
        // remove .env string if found along with directory path
        return str_replace('.env', '', $this->base_dir);
    }

    /**
     * Get Server root
     * 
     * @return string
     */
    private function server_root()
    {
        return rtrim(str_replace('src', '',  str_replace('\\', '/', realpath('.'))  ), '/');
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