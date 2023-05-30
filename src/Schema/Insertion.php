<?php

declare(strict_types=1);

namespace builder\Database\Schema;

use PDO;
use PDOException;
use builder\Database\Query\Builder;
use builder\Database\Pagination\Pagination;
use builder\Database\Traits\InsertionTrait;
use builder\Database\Collections\Collection;

abstract class Insertion extends Builder {

    use InsertionTrait, 
        Pagination;

    /**
     * Table names's on index arrays
     * Optimize multiple table
     * 
     * @param string|array $table
     * 
     * @return object\builder\Database\optimize
     */
    public function optimize(string|array $table = [])
    {
        $this->closeQuery();

        $this->modelQuery = false;

        // micro start time
        $this->startTimer();

        // add to global table property
        $this->table = $table;

        if(is_string($this->table)){
            $this->table = [$this->table];
        }

        // filter array
        $this->table = $this->console->arrayWalkerTrim($this->table);
        array_walk($this->table, function (&$value, $key){
            $value = "`{$value}`";
        });

        // convert to string
        $this->table = implode(', ', $this->table);

        // save to temp query data
        $this->setQueryProperty();

        // analize
        $analize = $this->analizeTable();
        if($analize['status'] !== self::ERROR_200){
            return $analize;
        }

        // repair
        $repair = $this->repairTable();
        if($repair['status'] !== self::ERROR_200){
            return $repair;
        }

        return (object) [
            'status'    => self::ERROR_200,
            'analize'   => $analize,
            'repair'    => $repair,
        ];
    }

    /**
     * Table names
     * Analize table
     * 
     * @param string $table
     * 
     * @return object\builder\Database\analize
     */
    public function analize(?string $table)
    {
        $this->closeQuery();

        $this->modelQuery = false;

        // micro start time
        $this->startTimer();

        // add to global table property
        $this->table = $table;

        // filter array
        $this->table = trim((string) "`{$table}`");

        // save to temp query data
        $this->setQueryProperty();
        
        return (object) $this->analizeTable();
    }

    /**
     * Table names
     * Repair table
     * 
     * @param string $table
     * 
     * @return object\builder\Database\repair
     */
    public function repair(?string $table)
    {
        $this->closeQuery();

        $this->modelQuery = false;

        // micro start time
        $this->startTimer();

        // add to global table property
        $this->table = $table;

        // filter array
        $this->table = trim((string) "`{$table}`");

        // save to temp query data
        $this->setQueryProperty();

        return (object) $this->repairTable();
    }

    /**
     * Check if table exists
     * 
     * @param string $table_name
     * 
     * @return bool\builder\Database\tableExists
     */
    public function tableExists(?string $table_name = null)
    {
        try{
            // check if DB connection has been established 
            if($this->dbConnection()['status'] != self::ERROR_200){
                return false;
            }

            $this->query("SELECT 1 FROM `{$table_name}` LIMIT 1")->execute();

            $this->close();

            return true;
        }catch (PDOException $e){
            return false;
        }
    }

    /**
     * Check if data exists in table
     *
     * @return bool\builder\Database\exists
     */
    public function exists()
    {
        if($this->modelQuery){
            // query build
            $this->query("SELECT EXISTS(SELECT 1 FROM `{$this->table}` {$this->tempQuery} LIMIT 1) as `exists`");

            // bind query for where clause
            $this->bindWhereQuery();

            // save to temp query data
            $this->setQueryProperty();

            try {
                // try execute
                $this->execute();

                $count = $this->getDataAndCloseConnection( 
                    $this->fetch(PDO::FETCH_COLUMN)
                );

                return $count >= self::ONE ? true : false;
            } catch (\PDOException $e) {
                return false;
            }
        }
    }

    /**
     * Get result data as an arrays of objects
     * @param int|string $per_page
     * 
     * @return object\builder\Database\Collections\Collection
     */
    public function get(int|string $per_page = 0)
    {
        $this->function = __FUNCTION__;
        return new Collection($this->getCollector($per_page), $this);
    }

    /**
     * Get result data as an arrays of objects
     * @param int|string $per_page
     *
     * @return object\builder\Database\Collections\Collection
     */
    public function paginate(int|string $per_page = 10)
    {
        $this->function = __FUNCTION__;
        return new Collection($this->getPagination($per_page), $this);
    }

    /**
     * Get first query
     *
     * @return object|null\builder\Database\Collections\Collection
     */
    public function first()
    {
        $data = $this->firstCollectionQuery(false);
        if($data){
            $this->function = __FUNCTION__;
            return new Collection($data, $this);
        }
    }

    /**
     * Get first query or abort with response code
     *
     * @return mixed\builder\Database\Collections\Collection
     */
    public function firstOrFail()
    {
        $data = $this->firstCollectionQuery();
        if($data){
            $this->function = __FUNCTION__;
            return new Collection($data, $this);
        }
    }

    /**
     * Get first query
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
     * @return object\builder\Database\Collections\Collection
     */
    public function firstOrCreate(array $conditions, ?array $data = [])
    {
        $this->function = __FUNCTION__;
        return new Collection(
            $this->firstOrCreateCollectionQuery($conditions, $data), 
            $this
        );
    }

    /**
     * Insert new records
     * 
     * @param array $param
     * 
     * @return object\builder\Database\Collections\Collection
     */ 
    public function insert(?array $param = [])
    {
        if($this->modelQuery){

            // save to temp memory
            $this->saveTempInsertQuery($param);
            
            return $this->insertInsertionQuery($param);
        }
    }

    /**
     * Insert query data
     * 
     * @param array $param
     * 
     * @return mixed\builder\Database\Collections\Collection
     */ 
    public function insertOrIgnore(?array $param = [])
    {
        if($this->modelQuery){

            // save to temp memory
            $this->saveTempInsertQuery($param);

            return $this->insertInsertionQuery($param, true);
        }
    }

    /**
     * Update query data
     * 
     * @param array $param
     * 
     * @return int
     */ 
    public function update(?array $param = [])
    {
        if($this->modelQuery){

            // save to temp memory
            $this->saveTempUpdateQuery($param);

            return $this->updateInsertionQuery($param);
        }
    }

    /**
     * Update or ignore query data
     * 
     * @param array $param
     * 
     * @return int
     */ 
    public function updateOrIgnore(?array $param = [])
    {
        if($this->modelQuery){

            // save to temp memory
            $this->saveTempUpdateQuery($param);

            return $this->updateInsertionQuery($param, true);
        }
    }

    /**
     * Increment and Update query data
     * 
     * @param string $column
     * @param int|array $count
     * @param array $param
     * 
     * @return int
     */ 
    public function increment(?string $column, $count = 1, $param = [])
    {
        if($this->modelQuery){

            // operator
            $temp  = $this->console->configIncrementOperator($column, $count, $param);

            // save to temp memory
            $this->saveTempUpdateQuery($temp['param']);

            // save temp increment to memory
            $this->saveTempIncrementQuery($temp);

            return $this->incrementInsertionQuery($temp);
        }
    }

    /**
     * Decrement and Update query data
     * 
     * @param string $column
     * @param int|array $count
     * @param array $param
     * 
     * @return int
     */ 
    public function decrement(?string $column, $count = 1, $param = [])
    {
        if($this->modelQuery){

            // operator
            $temp = $this->console->configIncrementOperator($column, $count, $param);

            // save to temp memory
            $this->saveTempUpdateQuery($temp['param']);

            // save temp decrement to memory
            $this->saveTempIncrementQuery($temp, false);

            return $this->incrementInsertionQuery($temp);
        }
    }

    /**
     * Delete query data
     * If message return number >=1\ Then data has been deleted
     * If return 0\ No data was deleted
     * 
     * @return int
     */ 
    public function delete()
    {
        if($this->modelQuery){
            return $this->deleteCollectionQuery();
        }
    }

    /**
     * Count results
     * @param bool $closeQuery
     * - [optional] Close Database Query After Count
     *
     * @return int
     */
    public function count(?bool $closeQuery = true)
    {
        try {
            // get query data
            $count = $this->allowCount()
                        ->compileQuery()
                        ->execute()
                        ->fetch(PDO::FETCH_COLUMN);
            
            // get data and close connection
            if($closeQuery){
                $count = $this->getDataAndCloseConnection( $count );
            }

            return $count;
        } catch (PDOException $e) {
            return $this->errorTemp($e);
        }
    }

    /**
     * Close all queries and restore back to default
     *
     * @return void
     */
    public function close()
    {
        $this->closeQuery();
    }

}

