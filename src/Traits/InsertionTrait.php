<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use PDO;
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
        // if true `INSERT IGNORE INTO` else then `INSERT INTO`
        $indexQuery = $tryOrFail ? "INSERT IGNORE INTO" : "INSERT INTO";

        // timestamp create
        if($this->timeStampsQuery()){
            $this->tempInsertQuery['columns']  .= ", created_at, updated_at";
            $this->tempInsertQuery['values']   .= ", :created_at, :updated_at";
            $param = array_merge($param, [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // create queries
        $this->query = "{$indexQuery} 
                        `{$this->table}` 
                        ({$this->tempInsertQuery['columns']}) 
                        values({$this->tempInsertQuery['values']})";

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
            
            // get results while closing connection
            return $this->getDataAndCloseConnection( $result );
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
        // if true `UPDATE IGNORE` else then `UPDATE`
        $indexQuery = $tryOrFail ? "UPDATE IGNORE" : "UPDATE";

        // timestamp create
        if($this->timeStampsQuery()){
            $this->timeStampsQuery = ", updated_at = NOW()";
        }

        // create queries
        $this->query = "{$indexQuery} 
                        `{$this->table}` 
                        SET {$this->tempUpdateQuery}{$this->timeStampsQuery}
                        {$this->tempQuery}";
        
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

            // get results while closing connection
            return $this->getDataAndCloseConnection( 
                $this->stmt->rowCount()
            );
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
        // timestamp create
        if($this->timeStampsQuery()){
            $this->timeStampsQuery = ", updated_at = NOW()";
        }

        // set query
        $this->query = "UPDATE 
                        `{$this->table}` 
                        SET {$this->tempIncrementQuery} 
                        {$this->tempUpdateQuery}{$this->timeStampsQuery} {$this->tempQuery}";

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

            // get results while closing connection
            return $this->getDataAndCloseConnection( 
                $this->stmt->rowCount()
            );
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
        }
    }

    /**
     * For the ->get() Method
     * Try\Catch
     * 
     * @param int|string $per_page
     *
     * @return object
     */
    protected function getCollector(int|string $per_page = 0)
    {
        try {
            // convert to int
            $per_page = (int) $per_page;
            if($per_page > 0){
                // query builder
                $this->limit($per_page)->compileQuery()->execute();
            } else{
                // query builder
                $this->compileQuery()->execute();
            }

            // get results while closing connection
            return $this->getDataAndCloseConnection(
                $this->fetchAll()
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
                        ->fetch(PDO::FETCH_OBJ);

        // Return the existing record if found
        if ($record) {
            // get results while closing connection
            return $this->getDataAndCloseConnection( 
                $record
            );
        }

        // merge conditions and data
        $create = $this->insertOrIgnore(
            array_merge($data, $conditions)
        );
        
        // if creation of records is okay
        if($create){
            $create = $create->toObject();
        }

        // get results while closing connection
        return $this->getDataAndCloseConnection( 
            $create
        );
    }
    
    /**
     * First or fail Query Try
     * @param bool $firstOrFail
     * 
     * @return object
     */ 
    protected function firstCollectionQuery(?bool $firstOrFail = true)
    {
        // headers
        $headers = new Manager();

        try {
            $this->limit(1)->compileQuery()->execute();
            
            $result = $this->fetch(PDO::FETCH_OBJ);

            // first or fail
            if($firstOrFail){
                if(!$result){
                    // exit with header code
                    $headers->setHeaders();
                }
            }

            // get results while closing connection
            return $this->getDataAndCloseConnection( 
                $result
            );
        } catch (\PDOException $e) {
            // first or fail
            if($firstOrFail){
                $headers->setHeaders();
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
        $this->query("DELETE FROM `{$this->table}` {$this->tempQuery}");

        // bind query for where clause
        $this->bindWhereQuery();

        // save to temp query data
        $this->setQueryProperty();

        try {
            // try execute
            $this->execute();
            
            // get results while closing connection
            return $this->getDataAndCloseConnection( 
                $this->stmt->rowCount()
            );
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
        }
    }

}