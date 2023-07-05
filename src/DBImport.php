<?php

declare(strict_types=1);

namespace builder\Database;

use PDOException;
use builder\Database\DB;
use builder\Database\Traits\ServerTrait;
use builder\Database\Traits\DBImportTrait;


class DBImport{

    use DBImportTrait, 
        ServerTrait;
    
    /**
     * Realpath to database file
     *
     * @var mixed
     */
    private $realpath;

    /**
     * Error status
     *
     * @var int
     */
    public $error;

    /**
     * Message body
     *
     * @var mixed
     */
    public $message;
    
    /**
     * Instance of Database Object
     *
     * @var \builder\Database\DB
     */
    private $db;

    
    /**
     * Construct Instance of Database
     */
    public function __construct() {
        $this->error = Constant::STATUS_404;
        $this->db = DB::connection()->dbConnection();
    }

    /**
     * Database Importation
     * @param string path_to_sql
     * 
     * @return object
     * [status, message]
     */
    public function import($path_to_sql = NULL)
    {
        $this->realpath = self::formatWithBaseDirectory($path_to_sql);
        
        /**
         * If SQL file does'nt exists
         */
        if(!file_exists($this->realpath) || is_dir($this->realpath)){
            $this->message = sprintf("Failed to open stream: `%s` does'nt exist.", $this->realpath);
        } else{

            // read a file into an array
            $readFile = file($this->realpath);

            // is readable
            if(!$this->isReadable($readFile)){
                $this->message = sprintf("Failed to read file or empty data. `%s`", $path_to_sql);
            } else{

                // check if connection test is okay
                if($this->DBConnect()){
                    try{
                        // connection driver
                        $pdo = $this->db['pdo'];

                        // get content
                        $sql = file_get_contents($this->realpath);

                        // Replace Creation of tables
                        $sql = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $sql);

                        // Replace Insert into
                        $sql = str_replace("INSERT INTO", "INSERT IGNORE INTO", $sql);

                        // Replace Creation of triggers
                        $sql = str_replace("CREATE TRIGGER", "CREATE TRIGGER IF NOT EXISTS", $sql);

                        // Replace delimiter
                        $sql = str_replace(['DELIMITER', '$$'], "", $sql);

                        // Check if table exists and remove ALTER TABLE queries
                        $matches = [];
                        preg_match_all('/ALTER TABLE `(\w+)`/i', $sql, $matches);
                        $tableNames = $matches[1];

                        // loop through to check if table exist already and ignore ALTER queries
                        foreach ($tableNames as $tableName) {
                            $tableExistsQuery = "SHOW TABLES LIKE '{$tableName}'";
                            $tableExists = $pdo->query($tableExistsQuery)->rowCount() > 0;
                        
                            if ($tableExists) {
                                $sql = preg_replace("/ALTER TABLE `{$tableName}`.*?;/is", "", $sql);
                            }
                        }

                        // execute query
                        $pdo->exec($sql);

                        $this->error    = Constant::STATUS_200;
                        $this->message  = "- Database has been imported successfully.";
                    } catch(PDOException $e){
                        $this->message  = "- Performing query: <strong style='color: #000'>{$e->getMessage()}</strong>";
                        $this->error    = Constant::STATUS_400;
                    }
                } else{
                    $this->message  = $this->db['message'];
                }
            }
        }
        
        /*
        | ----------------------------------------------------------------------------
        | Database importation use. Below are the status code
        | ----------------------------------------------------------------------------
        |   if ->status === 404 (Failed to read file or File does'nt exists
        |   if ->status === 400 (Query to database error
        |   if ->status === 200 (Success importing to database
        */ 
        
        return (object) [
            'status'    => $this->error, 
            'message'   => is_array($this->message) 
                            ? implode('\n<br>', $this->message)
                            : $this->message
        ];
    }
    
    /**
     * Check Database connection 
     * 
     * @return boolean
    */
    private function DBConnect()
    {
        // status
        if($this->db['status'] != Constant::STATUS_200){
            return false;
        }

        return true;
    }
    
}