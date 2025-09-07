<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations;

use PDOException;
use Tamedevelopers\Database\DB;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Process\HttpRequest;
use Tamedevelopers\Database\Migrations\Traits\SchemaTrait;
use Tamedevelopers\Database\Migrations\Traits\ManagerTrait;
use Tamedevelopers\Database\Migrations\Traits\FilePathTrait;
use Tamedevelopers\Database\Migrations\Traits\SchemaCollectionTrait;
use Tamedevelopers\Database\MigrationTrait\Traits\TableStructureTrait;
use Tamedevelopers\Database\Migrations\Traits\SchemaConfigurationTrait;

class Blueprint{
    
    use SchemaTrait, 
        SchemaCollectionTrait,
        SchemaConfigurationTrait, 
        TableStructureTrait, 
        FilePathTrait,
        ManagerTrait;

    /**
     * Creating Managers
     * 
     * @param string|null $tableName 
     */
    public function __construct($tableName = null) 
    {
        $this->db           = DB::connection();
        $this->tableName    = $tableName;
        $this->charSet      = $_ENV['DB_CHARSET'] ?? '';
        $this->collation    = $_ENV['DB_COLLATION'] ?? '';
    }

    /**
     * Creating Table Structure
     * Indexs|Primary|Constraints 
     * 
     * @return array
     */
    private function MySQLTemplate()
    {
        $checkPrimary = array_column($this->columns, 'primary');
        
        // check for primary keys
        if(count($checkPrimary) > 1){
            $status = Constant::STATUS_404;
            $message = sprintf(
                        "Primary Key can not be more than one in `%s` @table", 
                        $this->tableName);
        } else{
            $status = Constant::STATUS_200;
            $message = $this->toMySQLQuery();
        }
        
        return [
            'status'    => $status,
            'message'   => $message
        ];
    }

    /**
     * Creating Database Table
     * 
     * @return array
     */
    public function handleBlueprint() 
    {
        // create traceable table
        $traceTable = $this->traceableTableFileName($this->tableName);

        // handle db conn error
        $conn = self::checkDBConnect();
        if($conn['status'] != Constant::STATUS_200){
            return $conn;
        }

        // primary key error
        $mysqlHandle = $this->MySQLTemplate();
        if($mysqlHandle['status'] != Constant::STATUS_200){
            return $mysqlHandle;
        } 

        // style css
        $style = self::$style;

        // browser break
        $isConsole = HttpRequest::runningInConsole();
        $messageTpl = [
            'console_error' => "Migration Failed: Table exist on <b>[%s]</b>.",
            'console_success' => "Migration run successfully on <b>[%s]</b>.",
            'browser_error' => "<span style='background: #ee0707; {$style}'>Migration Failed: Table exist on %s.</span><br>",
            'browser_success' => "<span style='background: #027b02; {$style}'>Migration run successfully on %s.</span><br>",
        ];

        // Handle query
        try{
            // check if table already exist
            if($this->db->tableExists($this->tableName)){
                $message = sprintf(
                    $isConsole ? $messageTpl['console_error'] : $messageTpl['browser_error'],
                    $this->tableName
                );
            } else{
                // execute query
                $this->db->getPDO()->exec($mysqlHandle['message']);
                $message = sprintf(
                    $isConsole ? $messageTpl['console_success'] : $messageTpl['browser_success'],
                    $this->tableName
                );
            }
            
            $status = Constant::STATUS_200;
        } catch (PDOException $e){
            $status = Constant::STATUS_400;
            $message = $e->getMessage() . " {$traceTable}";
        }

        return [
            'status'   => $status,
            'message'  => $message,
        ];
    }

    /**
     * Check database connection error
     * 
     * @return array
     */
    private function checkDBConnect()
    {
        $conn = $this->db->dbConnection();
        return [
            'status'   => $conn['status'],
            'message'  => $conn['message'],
        ];
    }

}