<?php

declare(strict_types=1);

namespace builder\Database\Schema;

use stdClass;
use PDOException;
use builder\Database\Query\Builder;
use builder\Database\Trait\InsertionTrait;
use builder\Database\Pagination\Trait\PaginateTrait;

abstract class Insertion extends Builder {

    use InsertionTrait, 
        PaginateTrait;

    /**
     * Constructor
     * @param array $options\Database options settings
     * 
     * @return void
     */
	public function __construct(?array $options = [])
    {
        parent::__construct();

        // init configuration
        $this->initConfiguration($options);
        
        // start db
        $this->startDatabase();
	}

    /**
     * Table names's on index arrays
     * Optimize multiple table
     * 
     * @param array $table
     * 
     * @return object\builder\Database\optimize
     */
    public function optimize(?array $table = [])
    {
        $this->closeQuery();

        $this->modelQuery = false;

        // micro start time
        $this->startTimer();

        // add to global table property
        $this->table = $table;

        // filter array
        $this->table = $this->console::arrayWalkerTrim($this->table);
        array_walk($this->table, function (&$value, $key){
            $value = "`{$value}`";
        });

        // convert to string
        $this->table = implode(', ', $this->table);

        // save to temp query data
        $this->setQueryProperty();

        // analize
        $analize = $this->analizeTable();
        if($analize['response'] !== self::ERROR_200){
            return $analize;
        }

        // repair
        $repair = $this->repairTable();
        if($repair['response'] !== self::ERROR_200){
            return $repair;
        }

        return (object) [
            'response'  => self::ERROR_200,
            'analize'   => $analize,
            'repair'    => $repair,
        ];
    }

    /**
     * Check if table exist
     * 
     * @param string $table_name
     * 
     * @return bool\builder\Database\tableExist
     */
    public function tableExist(?string $table_name = null)
    {
        try{
            $this->raw("SELECT 1 FROM `{$table_name}` LIMIT 1")->execute();
            
            $this->close();
            
            return true;
        }catch (PDOException $e){
            return false;
        }
    }

    /**
     * Check if data exist in table
     *
     * @return bool\builder\Database\exists
     */
    public function exists()
    {
        if($this->modelQuery){
            // query build
            $this->query = "SELECT EXISTS(SELECT 1 FROM `{$this->table}` {$this->tempQuery} LIMIT 1) as `exists`";

            // set query
            $this->query($this->query);

            // bind query for where clause
            $this->bindWhereQuery();

            // save to temp query data
            $this->setQueryProperty();

            try {
                // try execute
                $this->execute();

                $data = $this->getQueryResult( 
                    $this->tryFetchAll(false)[0] ?? [] 
                );

                return isset($data['exists']) && $data['exists'] >= self::ONE
                        ? true
                        : false;
            } catch (\PDOException $e) {
                return false;
            }
        }
    }

    /**
     * Get result data as an array of arrays
     *
     * @return array
     */
    public function getArr()
    {
        return $this->fetchCollector(false);
    }

    /**
     * Get result data as an arrays of objects
     *
     * @return object|array
     */
    public function get()
    {
        return $this->fetchCollector();
    }

    /**
     * Get first query
     *
     * @return object
     */
    public function first()
    {
        return $this->firstCollectionQuery(false);
    }

    /**
     * Get first query or abort with response code
     *
     * @return array|object|null|void
     */
    public function firstOrFail()
    {
        return $this->firstCollectionQuery();
    }

    
    /**
     * Get result data as an arrays of objects
     * @param int $per_page
     *
     * @return object|array
     */
    public function paginate($per_page = 10)
    {
        return (object) $this->getPagination($per_page);
    }

    /**
     * Insert query data
     * 
     * @param array $param
     * 
     * @return object
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
     * @return object
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

            $this->query = "UPDATE `{$this->table}` SET {$this->tempUpdateQuery} {$this->tempQuery}";

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

            $this->query = "UPDATE IGNORE `{$this->table}` SET {$this->tempUpdateQuery} {$this->tempQuery}";

            return $this->updateInsertionQuery($param, false);
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
            $temp  = $this->console::configIncrementOperator($column, $count, $param);

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
            $temp = $this->console::configIncrementOperator($column, $count, $param);

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
     *
     * @return int
     */
    public function count()
    {
        try {
            // convert query
            $this->allowCount()
                    ->compileQuery()
                    ->execute();
            
            $data = $this->getQueryResult( 
                $this->tryFetchAll(false)[0] ?? [] 
            );

            return isset($data['count(*)']) && $data['count(*)'] >= self::ONE
                    ? $data['count(*)'] 
                    : 0;
        } catch (PDOException $e) {
            $this->dump_final = false;
            $this->dump( 
                $this->errorTemp($e)['message'] 
            );
        }
    }

     /**
     * Close all queries and restore back to default
     *
     * @return void\close
     */
    public function close()
    {
        $this->closeQuery();
    }

}

