<?php

declare(strict_types=1);

namespace builder\Database;

use PDOException;
use builder\Database\DB;
use builder\Database\Traits\DBImportTrait;


class DBImport extends DB{

    use DBImportTrait;
    
    private $db_connection;
    private $error;
    private $message;
    private $realpath;
    private $template;
    private $array = [];
    
    /**
     * Construct Instance of Database
     */
    public function __construct() {
        parent::__construct();

        $this->db_connection = $this->getConnection();
    }

    /**
     * Database Importation
     * @param string path_to_sql
     * 
     * @return 
     */
    public function DatabaseImport($path_to_sql = NULL)
    {
        $this->realpath = (string) $path_to_sql;
        
        /**
         * If SQL file does'nt exists
         */
        if(!file_exists($this->realpath) || is_dir($this->realpath))
        {
            return [
                'response'  => self::ERROR_404, 
                'message'   => "Failed to open stream: `{$path_to_sql}` does'nt exist."
            ];

        } else{

            // read a file into an array
            $readFile = file($this->realpath);

            // is readable
            if(!$this->isReadable($readFile))
            {
                return [
                    'response'  => self::ERROR_404, 
                    'message'   => "Failed to read file or empty data."
                ];
            } else{

                // Begin our final importation
                foreach($readFile as $key => $query)
                {
                    // skip if its a comment
                    if($this->isComment($query))
                        continue;
                    
                    //Add to the current segment
                    $this->template .= $query;
                    
                    // Check if it's a query
                    if($this->isQuery($query))
                    {
                        // check if connection test is okay
                        if($this->DBConnect()){
                            try{
                                //Query the database
                                $this->query($this->template)->execute();

                            } catch(PDOException $e){

                                // get error msg
                                $errorMsg = $e->getMessage();

                                if($errorMsg != '0'){
                                    if(
                                        strpos($errorMsg, "Multiple primary key defined") === false 
                                        && strpos($errorMsg, "Duplicate entry") === false){

                                        $this->message = "- Performing query: <strong style='color: #000'>{$errorMsg}</strong>";
                                        $this->array[] = $this->message;
                                    }
                                    $this->error = self::ERROR_400;
                                }
                            }
                        }else{
                            $this->message  = $this->db_connection['message'];
                            $this->array[]  = $this->message;
                            $this->error    = $this->db_connection['status'];
                            break;
                        }
                        
                        // Set the template to an empty string
                        $this->template = '';
                    }
                }
            }
        }
        
        // successful and no errors
        if(count($this->array) === 0 && $this->db_connection['status'] == self::ERROR_200){
            $this->error    = self::ERROR_200;
            $this->message  = "- Database has been imported successfully.";
            $this->array[0] = $this->message;
        }
        
        /*
        | ----------------------------------------------------------------------------
        | Database importation use. Below are the response code
        | ----------------------------------------------------------------------------
        |   if ->response === 404 (Failed to read file or File does'nt exists
        |   if ->response === 400 (Query to database error
        |   if ->response === 200 (Success importing to database
        */ 
        
        return [
            'response'  => $this->error, 
            'message'   => $this->array
        ];
    }
    
    /**
     * Check Database connection 
     * 
     * @return boolean\DBConnect
    */
    private function DBConnect()
    {
        // status
        if($this->db_connection['status'] != self::ERROR_200){
            return false;
        }

        return true;
    }
    
}