<?php

declare(strict_types=1);

namespace builder\Database\Traits;


trait DBImportTrait{
    
    /**
     * Check if data is Readable
     * 
     * @param array|null $readFile
     * @return boolean\isReadable
    */
    protected function isReadable(?array $readFile = null)
    {
        if(!is_array($readFile) || count($readFile) === 0){
            return false;
        }
        return true;
    }

    /**
     * Check if sql string is a comment
     * 
     * @param string|null $string
     * @return boolean\isComment
    */
    protected function isComment(?string $string = null)
    {
        // if first two is --
        // or it's empty
        if(substr($string, 0, 2) === '--' || $string === ''){
            return true;
        }
        return false;
    }

    /**
     * Check if sql string is a Query
     * 
     * @param string|null $string
     * @return boolean\isQuery
    */
    protected function isQuery(?string $string = null)
    {
        // check is last char is `;`
        if(substr(trim($string), -1, 1) == ';'){
            return true;
        }
        return false;
    }

}