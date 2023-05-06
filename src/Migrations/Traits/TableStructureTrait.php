<?php

declare(strict_types=1);

namespace builder\Database\MigrationTrait\Traits;

trait TableStructureTrait{
    
    protected $db;
    protected $charSet;
    protected $tableName;
    protected $collation;
    protected $primaryKeyValue;
    protected $columns            = [];
    protected $queryIndex         = [];
    protected $queryStructure     = [];
    protected $queryConstraints   = [];
    protected $queryTimeStamps    = [];

    /**
     * Creating Table Structure
     * Indexs|Primary|Constraints 
     * 
     * @return string
     */
    private function toMySQLQuery()
    {
        // query collections
        $this->createQueryCollections();

        // Creating table queries
        $this->createTableQuery();

        // alter primary key
        $this->alterPrimaryKey();

        // Add triggers
        $this->createTriggers();

        // alter constriants
        $this->alterConstraints();

        // implode to string
        $Query = implode('', $this->collectionQuery);

        // end commit
        $Query .= "COMMIT;";

        return $this->regixifyQuery($Query);
    }

    /**
     * Create Query Collections
     * 
     * @return void
     */
    private function createQueryCollections()
    {
        foreach($this->columns as $column){

            // if keys are set
            if(isset($column['primary']) || isset($column['unique']) || isset($column['index']))
            {
                switch ($column) {
                    case isset($column['primary']):
                        $this->primaryKeyValue  = $column;
                        $this->queryIndex[] = "PRIMARY KEY (`{$column['name']}`)";
                        break;
                    
                    case isset($column['unique']):
                        $this->queryIndex[] = "UNIQUE KEY `{$column['unique']}` (`{$column['name']}`)";
                        break;
                    
                    case isset($column['index']):
                        $this->queryIndex[] = "KEY `{$column['index']}` (`{$column['name']}`)";
                        break;
                }
            }

            // table query structure
            // exclude references
            if($column['type'] != 'foreign'){
                $this->queryStructure[] = $this->createColumnDefinition($column);
            }
            
            // for references
            else{
                $onDelete = strtoupper($column['onDelete']) ?? null;
                $onUpdate = strtoupper($column['onUpdate']) ?? null;

                $this->queryConstraints[] = "
                    ADD CONSTRAINT `{$column['generix']}` 
                    FOREIGN KEY (`{$column['name']}`) 
                    REFERENCES `{$column['on']}` (`{$column['references']}`) 
                    ON DELETE {$onDelete} 
                    ON UPDATE {$onUpdate}
                ";
            }

            // checkout for triggers
            if($column['type'] === 'timestamps'){
                $this->queryTimeStamps[] = $column;
            }
        }
    }

    /**
     * Add query into collections
     * 
     * @return void\addCollectionQuery
     */
    private function addCollectionQuery($query = '')
    {
        if(!empty($query)){
            $this->collectionQuery[] = $query;
        }
    }

    /**
     * Create table queries
     * 
     * @return void
     */
    private function createTableQuery()
    {
        // implode create table query with comma
        $this->queryStructure = implode(', ', $this->queryStructure);
        
        $this->addCollectionQuery(
            "
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
                    {$this->queryStructure}{$this->createIndexs()}
                ) ENGINE=InnoDB DEFAULT CHARSET={$this->charSet} COLLATE={$this->collation};
            "
        );
    }

    /**
     * Create indexs key queries
     * 
     * @return mixed
     */
    private function createIndexs()
    {
        if(is_array($this->queryIndex) && count($this->queryIndex) > 0){
            $queryIndex = implode(', ', $this->queryIndex);
            return ", {$queryIndex}";
        }
    }

    /**
     * Alter primary key queries
     * 
     * @return void
     */
    private function alterPrimaryKey()
    {
        if(is_array($this->primaryKeyValue)){
            // check for auto increment
            $autoIncrement = $this->primaryKeyValue['auto_increment'];
            if($autoIncrement){
                $increment = " AUTO_INCREMENT, AUTO_INCREMENT=1";
            }

            $this->addCollectionQuery(
                "
                    --
                    -- AUTO_INCREMENT for table `{$this->tableName}`
                    --
                    ALTER TABLE `{$this->tableName}`
                    MODIFY {$this->createColumnDefinition($this->primaryKeyValue)}{$increment};
                "
            );
        }
    }

    /**
     * Alter constraints queries
     * 
     * @return void
     */
    private function alterConstraints()
    {
        if(is_array($this->queryConstraints) && count($this->queryConstraints) > 0){
            $this->queryConstraints = implode(', ', $this->queryConstraints);
            $this->addCollectionQuery(
                "
                    --
                    -- Constraints for table `{$this->tableName}`
                    --
                    ALTER TABLE `{$this->tableName}`
                    {$this->queryConstraints};
                "
            );
        }
    }

    /**
     * Create Triggers queries
     * 
     * @return void
     */
    private function createTriggers()
    {
        if(is_array($this->queryTimeStamps) && count($this->queryTimeStamps) > 0){
            foreach($this->queryTimeStamps as $triggers){
                if($triggers['name'] === 'created_at'){
                    $this->addCollectionQuery(
                        "
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
                        "
                    );
                }else{
                    $this->addCollectionQuery(
                        "
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
                        "
                    );
                }
            }
        }
    }

    /**
     * Format Query with Regix
     * 
     * @return string
     */
    private function regixifyQuery(?string $formatted)
    {
        // Replace the comma with comma + newline
        $formatted = preg_replace("/,\s*/m", ",\n", $formatted);

        // clean string from begining and ending
        $formatted = preg_replace("/^[ \t]+|[ \t]+$/m", "", $formatted);

        // replace back-slash
        $formatted = str_replace('\\', '', $formatted);

        return $formatted;
    }

}