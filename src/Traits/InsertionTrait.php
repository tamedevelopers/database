<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use builder\Database\Capsule\Manager;

trait InsertionTrait{
    
    /**
     * Insert Query Try
     * 
     * @param array $param
     * @param bool $tryOrFail
     * 
     * @return object
     */ 
    protected function insertInsertionQuery(?array $param = [], ?bool $tryOrFail = false)
    {
        // for insert
        $tempQuery = "INSERT INTO";
        if($tryOrFail){
            $tempQuery = "INSERT IGNORE INTO";
        }

        $this->query = "{$tempQuery} `{$this->table}` ({$this->tempInsertQuery['columns']}) values({$this->tempInsertQuery['values']})";

        // set query
        $this->query($this->query);

        // bind query for param
        foreach($param as $key => $value){
            $this->bind(":$key", $this->whitelistInput($value));
        }

        // save to temp query data
        $this->setQueryProperty();

        try {
            // try execute
            $this->execute();
            
            // results
            $result = $this->table($this->table)
                            ->where('id', $this->lastInsertId())
                            ->first();

            // close query after execution
            $this->getQueryResult( $result );
            
            return $result;
        } catch (\PDOException $e) {
            if($tryOrFail){
                if ($e->errorInfo[1] === 1062) {
                    // Duplicate key error, ignore and return null
                    return null;
                }
            }
            return $this->errorTemp($e, true);
        }
    }

    /**
     * Update Query Try
     * 
     * @param array $param
     * @param bool $tryOrFail
     * 
     * @return int
     */ 
    protected function updateInsertionQuery(?array $param = [], ?bool $tryOrFail = false)
    {
        // set query
        $this->query($this->query);

        // bind query for param
        foreach($param as $key => $value){
            $this->bind(":$key", $this->whitelistInput($value));
        }

        // bind query for where clause
        $this->bindWhereQuery();

        // save to temp query data
        $this->setQueryProperty();

        try {
            // try execute
            $this->execute();

            // results
            $result = $this->stmt->rowCount();

            // close query after execution
            $this->getQueryResult( $result );

            return $result;
        } catch (\PDOException $e) {
            if($tryOrFail){
                if ($e->errorInfo[1] === 1062) {
                    // Duplicate key error, ignore and return false
                    return 0;
                }
            }
            return $this->errorTemp($e, true);
        }
    }

    /**
     * Incremenrt|Decrement Query Try
     * 
     * @param array $temp
     * 
     * @return int
     */ 
    protected function incrementInsertionQuery(?array $temp = [])
    {
        $this->query = "UPDATE `{$this->table}` SET {$this->tempIncrementQuery} {$this->tempUpdateQuery} {$this->tempQuery}";

        // set query
        $this->query($this->query);

        // bind increment data
        $this->bind(":{$temp['column']}", $temp['count']);

        // bind query for param
        foreach($temp['param'] as $key => $value){
            $this->bind(":$key", $this->whitelistInput($value));
        }

        // bind query for where clause
        $this->bindWhereQuery();

        // save to temp query data
        $this->setQueryProperty();

        try {
            // try execute
            $this->execute();

            // results
            $result = $this->stmt->rowCount();

            // close query after execution
            $this->getQueryResult( $result );

            return $result;
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
        }
    }

    /**
     * get Query Try
     *
     * @return object
     */
    protected function getCollector()
    {
        try {
            // query builder
            $this->compileQuery()->execute();

            return $this->getQueryResult(
                $this->tryFetchAll(true)
            );
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
        }
    }
    
    /**
     * First or Create
     * 
     * @param array $conditions
     * - Mandatory conditions options
     * - Must be assoc array with key and value types.
     * - Uses the ->where() clause to check if data matched
     * - If data not given and conditions failed, then it'll attempt to create a new records 
     * Using the conditional data merged with data if given
     * 
     * @param array $data
     * - [optional] Data to create with if condition not meant
     * 
     * @return mixed
     */ 
    protected function firstOrCreateCollectionQuery(array $conditions, ?array $data = [])
    {
        // if array is empty
        if(empty($conditions)){
            // return;
        }

        // create where clause conditions
        foreach($conditions as $key => $condition){
            $key = trim((string) $key);
            $this->where($key, $condition);
        }

        // get data
        $record = $this->limit(1)
                        ->compileQuery()
                        ->execute()
                        ->tryFetchAll()[0] ?? null;

        // Return the existing record if found
        if ($record) {
            // close query after execution
            $this->getQueryResult( $record );
            return $record;
        }

        // merge conditions and data
        $create = $this->insertOrIgnore(
            array_merge($data, $conditions)
        );
        
        // if creation of records is okay
        if($create){
            $create = $create->toObject();
        }

        // close query after execution
        $this->getQueryResult( $create );

        return $create;
    }
    
    /**
     * First or fail Query Try
     * @param bool $firstOrFail
     * 
     * @return object
     */ 
    protected function firstCollectionQuery(?bool $firstOrFail = true)
    {
        try {
            $this->limit(1)->compileQuery()->execute();

            $result = $this->tryFetchAll()[0] ?? null;

            // close query after execution
            $this->getQueryResult( $result );

            // first or fail
            if($firstOrFail){
                if(is_null($result)){
                    // exit with header code
                    (new Manager)::setHeaders();
                }
            }

            return $result;
        } catch (\PDOException $e) {
            // first or fail
            if($firstOrFail){
                (new Manager)::setHeaders();
            } else{
                return $this->errorTemp($e, true);
            }
        }
    }
    
    /**
     * Delete Query Try
     * @return int
     */ 
    protected function deleteCollectionQuery()
    {
        // query build
        $this->query = "DELETE FROM `{$this->table}` {$this->tempQuery}";

        // set query
        $this->query($this->query);

        // bind query for where clause
        $this->bindWhereQuery();

        // save to temp query data
        $this->setQueryProperty();

        try {
            // try execute
            $this->execute();

            // results
            $result = $this->stmt->rowCount();

            // close query after execution
            $this->getQueryResult( $result );

            return $result;
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
        }
    }

}