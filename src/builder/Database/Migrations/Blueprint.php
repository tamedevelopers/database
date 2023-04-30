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

class Blueprint extends Constants{
    
    use SchemaTrait, 
        SchemaCollectionTrait, 
        TableStructureTrait, 
        FilePathTrait,
        ManagerTrait;

    /**
     * Creating Managers
     * @param string $tableName 
     * 
     * @return void
     */
    public function __construct(?string $tableName = null) 
    {
        $this->db = new DB();
        $this->tableName = $tableName;
        $this->charSet   = $_ENV['DB_CHARSET'] ?? '';
        $this->collation = $_ENV['DB_COLLATION'] ?? '';
    }

    /**
     * Creating Table Structure
     * Indexs|Primary|Constraints 
     * 
     * @return string\MySQLTemplate
     */
    private function MySQLTemplate()
    {
        $checkPrimary = array_column($this->columns, 'primary');
        if(count($checkPrimary) > 1){
            throw new \Exception('Primary Key can not be more than one in a table');
        }

        return $this->toMySQLQuery();
    }

    /**
     * Creating Database Table
     * 
     * @return array\handle
     */
    private function handle() 
    {
        // Handle query
        try{
            // check if table already exist
            if($this->db->tableExist($this->tableName)){
                $message = "Migration runned 
                <span style='background: #ee0707; {$this->style}'>
                Failed
                </span> Table already exist on `{$this->traceable($this->tableName)}` <br>\n";
            }else{
                $this->status_runned = true;
                $message = "Migration runned 
                                <span style='background: #027b02; {$this->style}'>
                                    Successfully
                                </span> on 
                                `{$this->traceable($this->tableName)}` <br>\n";
            }

            // execute query
            if($this->status_runned){
                $this->db->query( $this->MySQLTemplate() )->execute();
            }

            return [
                'response'  => self::ERROR_200,
                'message'   => $message,
            ];
        } catch (PDOException $e){
            return ['response' => self::ERROR_404, 'message' => $e->getMessage()];
            exit();
        }
    }

    public function __destruct()
    {
        // Blueprint handle
        $handle = $this->handle();

        if($handle['response'] !== self::ERROR_200){
            echo preg_replace(
                '/^[ \t]+|[ \t]+$/m', '', 
                sprintf("<<\\Error code>> %s
                    <br><br>
                    <<\\PDO::ERROR>> %s <br>\n
                ", $handle['response'], $handle['message'])
            );
            return;
        }
        echo "{$handle['message']}";
    }

}