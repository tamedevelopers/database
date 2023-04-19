<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Migration;

use PDOException;
use UltimateOrmDatabase\DB;
use UltimateOrmDatabase\Constants;
use UltimateOrmDatabase\Migration\Trait\SchemaTrait;
use UltimateOrmDatabase\Migration\Trait\FilePathTrait;
use UltimateOrmDatabase\Migration\Trait\SchemeCollectionTrait;

class Blueprint extends Constants{
    
    use SchemaTrait, SchemeCollectionTrait, FilePathTrait;

    private $db;
    private $charSet;
    private $tableName;
    private $collation;
    private $columns            = [];
    private $queryIndex         = [];
    private $queryStructure     = [];
    private $queryConstraints   = [];
    private $queryTimeStamps    = [];

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
     * @return string\tableStructureQuery
     */
    private function tableStructureQuery()
    {
        $checkPrimary = array_column($this->columns, 'primary');
        if(count($checkPrimary) > 1){
            throw new \Exception('Primary Key can not be more than one in a table');
        }

        // primary data
        $primary = $increment = null;

        foreach($this->columns as $column){

            // if keys are set
            if(isset($column['primary']) || isset($column['unique']) || isset($column['index']))
            {
                switch ($column) {
                    case isset($column['primary']):
                        $primary = $column;
                        $this->queryIndex[] = "ADD PRIMARY KEY (`{$column['name']}`)";
                        break;
                    
                    case isset($column['unique']):
                        $this->queryIndex[] = "ADD UNIQUE KEY `{$column['unique']}` (`{$column['name']}`)";
                        break;
                    
                    case isset($column['index']):
                        $this->queryIndex[] = "ADD KEY `{$column['index']}` (`{$column['name']}`)";
                        break;
                }
            }

            // table query structure
            // exclude references
            if($column['type'] != 'foreign'){
                $this->queryStructure[] = $this->createColumnDefinition($column);
            }else{
                $onDelete = strtoupper($column['onDelete']) ?? null;
                $onUpdate = strtoupper($column['onUpdate']) ?? null;

                $this->queryConstraints[] = "
                    ADD CONSTRAINT `{$column['generix']}` 
                    FOREIGN KEY (`{$column['name']}`) 
                    REFERENCES `{$column['references']}` (`{$column['on']}`) 
                    ON DELETE {$onDelete} 
                    ON UPDATE {$onUpdate}
                ";
            }

            // checkout for triggers
            if($column['type'] === 'timestamps'){
                $this->queryTimeStamps[] = $column;
            }
        }

        // implode create table query with comma
        $this->queryStructure = implode(', ', $this->queryStructure);

        // Creating table queries
        $Query = "

            SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
            START TRANSACTION;
            SET time_zone = '+00:00';

            --
            -- Database: `{$this->tableName}`
            --

            -- --------------------------------------------------------

            --
            -- Table structure for table `{$this->tableName}`
            --
            CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                {$this->queryStructure}
            ) ENGINE=InnoDB DEFAULT CHARSET={$this->charSet} COLLATE={$this->collation};
        ";
        
        // add indexes
        if(count($this->queryIndex) > 0){
            // implode indexes with comma
            $this->queryIndex = implode(', ', $this->queryIndex);
            $Query .= "
                --
                -- Indexes for table `{$this->tableName}`
                --
                ALTER TABLE `{$this->tableName}` 
                {$this->queryIndex};
            ";
        }
        
        // modify primary key
        if(is_array($primary)){
            // check for auto incr
            $autoIncrement = $primary['auto_increment'];
            if($autoIncrement){
                $increment = " AUTO_INCREMENT, AUTO_INCREMENT=1";
            }

            $Query .= "
                --
                -- AUTO_INCREMENT for table `{$this->tableName}`
                --
                ALTER TABLE `{$this->tableName}`
                MODIFY {$this->createColumnDefinition($primary)}{$increment};
            ";
        }

        // Add triggers
        if(count($this->queryTimeStamps) > 0){
            foreach($this->queryTimeStamps as $triggers){
                if($triggers['name'] === 'created_at'){
                    $Query .= "
                        --
                        -- Trigger to set created_at timestamp on insert
                        --
                        CREATE TRIGGER {$this->tableName}_created_at
                        BEFORE INSERT ON {$this->tableName}
                        FOR EACH ROW
                        BEGIN
                            IF (SELECT COUNT(*) FROM information_schema.columns 
                                WHERE table_name = '{$this->tableName}' 
                                AND column_name = 'created_at') > 0 THEN
                                SET NEW.created_at = IFNULL(NEW.created_at, NOW());
                                SET NEW.updated_at = NOW();
                            END IF;
                        END;
                    ";
                }else{
                    $Query .= "
                        --
                        -- Trigger to update updated_at timestamp on update
                        --
                        CREATE TRIGGER {$this->tableName}_updated_at
                        BEFORE UPDATE ON {$this->tableName}
                        FOR EACH ROW
                        BEGIN
                            IF (SELECT COUNT(*) FROM information_schema.columns 
                                WHERE table_name = '{$this->tableName}' 
                                AND column_name = 'updated_at') > 0 THEN
                                SET NEW.updated_at = NOW();
                            END IF;
                        END;
                    ";
                }
            }
        }

        // add constriants
        if(count($this->queryConstraints) > 0){
            // implode constraints with comma
            $this->queryConstraints = implode(', ', $this->queryConstraints);
            $Query .= "
                --
                -- Constraints for table `{$this->tableName}`
                --
                ALTER TABLE `{$this->tableName}`
                {$this->queryConstraints};
            ";
        }

        // end commit
        $Query .= "COMMIT;";

        // Replace the comma with comma + newline
        $Query = preg_replace("/,\s*/m", ",\n", $Query);

        // clean string from begining and ending
        $Query = preg_replace("/^[ \t]+|[ \t]+$/m", "", $Query);

        // clean forward slash
        $Query = str_replace('\\', '', $Query);

        return $Query;
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
            $this->db->query( $this->tableStructureQuery() )
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