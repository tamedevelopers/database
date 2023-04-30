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
        $this->query = "INSERT INTO `{$this->table}` ({$this->tempInsertQuery['columns']}) values({$this->tempInsertQuery['values']})";

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
        $this->bind(":{$temp['count']}", $temp['count']);

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