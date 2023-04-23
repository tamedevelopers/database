<?php

declare(strict_types=1);

namespace builder\Database\MigrationTrait\Trait;

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
        
        // add indexes
        $this->addIndexs();
        
        // alter primary key
        $this->addPrimaryKey();

        // Add triggers
        $this->createTriggers();

        // create constriants
        $this->createConstraints();

        // implode to string
        $Query = implode('', $this->collectionQuery);

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
            }
            
            // for references
            else{
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
    }

    /**
     * Add query into collections
     * 
     * @return void\addCollectionQuery
     */
    private function addCollectionQuery($query = '')
    {
        if(!empty($query))
            $this->collectionQuery[] = $query;
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
                    {$this->queryStructure}
                ) ENGINE=InnoDB DEFAULT CHARSET={$this->charSet} COLLATE={$this->collation};
            "
        );
    }

    /**
     * Add indexs key queries
     * 
     * @return void
     */
    private function addIndexs()
    {
        if(is_array($this->queryIndex) && count($this->queryIndex) > 0){
            $this->queryIndex = implode(', ', $this->queryIndex);
            $this->addCollectionQuery(
                "
                    --
                    -- Indexes for table `{$this->tableName}`
                    --
                    ALTER TABLE `{$this->tableName}` 
                    {$this->queryIndex};
                "
            );
        }
    }

    /**
     * Add primary key queries
     * 
     * @return void
     */
    private function addPrimaryKey()
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
     * Create constraints queries
     * 
     * @return void
     */
    private function createConstraints()
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

}