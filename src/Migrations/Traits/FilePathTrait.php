<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Traits;

use Exception;


trait FilePathTrait{

    private $session = 'orm_temp_migration_query';

    /**
     * Get Traceable File name
     * @param string $name 
     * 
     * @return string|null\traceable
     */
    public function traceableTableFileName(?string $table = null)
    {
        // exception trace
        $exception = (new Exception)->getTrace();

        // get traceable file name
        $fileName = $exception[3]['file'] ?? null;
        
        if(!is_null($fileName)){
            $fileName = basename($fileName, '.php');
            $table = $fileName;
        }else{
            // add table name to end of string
            if(!str_contains(strtolower($table), "table")){
                $table .= "_table";
            }
        }
        
        return $table;
    }

}