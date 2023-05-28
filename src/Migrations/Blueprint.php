<?php

declare(strict_types=1);

namespace builder\Database\Migrations;

use PDOException;
use builder\Database\DB;
use builder\Database\Constants;
use builder\Database\Migrations\Traits\SchemaTrait;
use builder\Database\Migrations\Traits\ManagerTrait;
use builder\Database\Migrations\Traits\FilePathTrait;
use builder\Database\Migrations\Traits\SchemaCollectionTrait;
use builder\Database\MigrationTrait\Traits\TableStructureTrait;
use builder\Database\Migrations\Traits\SchemaConfigurationTrait;

class Blueprint extends Constants{
    
    use SchemaTrait, 
        SchemaCollectionTrait,
        SchemaConfigurationTrait, 
        TableStructureTrait, 
        FilePathTrait,
        ManagerTrait;

    /**
     * Creating Managers
     * 
     * @param string $tableName 
     */
    public function __construct(?string $tableName = null) 
    {
        $this->db           = new DB();
        $this->tableName    = $tableName;
        $this->charSet      = $_ENV['DB_CHARSET'] ?? '';
        $this->collation    = $_ENV['DB_COLLATION'] ?? '';
    }

    /**
     * Creating Session Query
     * - To hold each Migration Request
     * @param mixed $query
     * 
     * @return void
     */
    private function tempMigrationQuery(mixed $query = null)
    {
        // Start the session has not already been started
        if (session_status() == PHP_SESSION_NONE) {
            @session_start();
        }

        $_SESSION[$this->session] = json_encode($query);
    }

    /**
     * Creating Table Structure
     * Indexs|Primary|Constraints 
     * 
     * @return array\MySQLTemplate
     */
    private function MySQLTemplate()
    {
        $checkPrimary = array_column($this->columns, 'primary');
        if(count($checkPrimary) > 1){
            return [
                'status'    => self::ERROR_404,
                'message'   => sprintf("Primary Key can not be more than one in `%s` @table", $this->tableName),
            ];
        }
        
        return [
            'status'    => self::ERROR_200,
            'message'   => $this->toMySQLQuery()
        ];
    }

    /**
     * Creating Database Table
     * 
     * @return array\handle
     */
    public function handle() 
    {
        // create traceable table
        $traceTable = $this->traceable($this->tableName);

        // handle error
        $handle = self::checkDBConnect();
        if(is_array($handle)){
            return $handle;
        } 

        // primary key error
        $mysqlHandle = $this->MySQLTemplate();
        if($mysqlHandle['status'] != self::ERROR_200){
            return $mysqlHandle;
        } 

        // style css
        $style = self::$style;

        // Handle query
        try{
            // check if table already exist
            if($this->db->tableExists($this->tableName)){
                $message = "Migration 
                                <span style='background: #ee0707; {$style}'>
                                    Failed
                                </span> Table exist on `{$traceTable}` <br>\n";
            }else{
                $this->status_runned = true;
                $message = "Migration runned 
                                <span style='background: #027b02; {$style}'>
                                    Successfully
                                </span> on
                                `{$traceTable}` <br>\n";
            }

            // execute query
            if($this->status_runned){
                $this->db->dbDriver()->exec($mysqlHandle['message']);
            }

            return [
                'status'    => self::ERROR_200,
                'message'   => $message,
            ];
        } catch (PDOException $e){
            return ['status' => self::ERROR_404, 'message' => $e->getMessage()];
        }
    }

    /**
     * Save query data into sessions
     * 
     * @return void
     */
    public function __destruct() 
    {
        $this->tempMigrationQuery($this->handle());
    }

    /**
     * Check database connection error
     * 
     * @return mixed
     */
    private function checkDBConnect()
    {
        // style css
        $style = self::$style;

        // if database connection is okay
        $dbConnection = $this->db->dbConnection();
        if($dbConnection['status'] !== self::ERROR_200){
            return [
                'status'    => self::ERROR_404,
                'message'   => "Connection Error 
                                    <span style='background: #ee0707; {$style}'>
                                        Database Connection Error
                                    </span>
                                    `{$dbConnection['message']}` <br>\n",
            ];
        }
    }

}