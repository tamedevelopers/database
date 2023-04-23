<?php

declare(strict_types=1);

namespace builder\Database\Migrations;

use PDOException;
use builder\Database\DB;
use builder\Database\Constants;
use builder\Database\Migrations\Trait\SchemaTrait;
use builder\Database\Migrations\Trait\FilePathTrait;
use builder\Database\Migrations\Trait\SchemaCollectionTrait;
use builder\Database\MigrationTrait\Trait\TableStructureTrait;

class Blueprint extends Constants{
    
    use SchemaTrait, 
        SchemaCollectionTrait, 
        TableStructureTrait, 
        FilePathTrait;

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
            $this->db->query( $this->MySQLTemplate() )
                    ->execute();
            
            return [
                'response'  => self::ERROR_200,
                'message'   => "Migration runned successfully on `{$this->traceable($this->tableName)}` <br>\n",
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