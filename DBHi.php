/**
     * Constructor manager class
     * For class that extends without calling the constructor
     */
	protected function im()
    {
        return new Manager();
	}

    /**
     * Table initialize
     * Check if table has been initialize
     * 
     * @return void
     */
	protected function isTableInitialized()
    {
        if(!$this->initialize){
            $this->boot();
        }
	}
    
    /**
     * Get last Database query sample
     * 
     * @return mixed\builder\Database\dbQuery
     */
    public function dbQuery()
    {
        if(is_null($this->dbQuery)){
            $this->saveDBQuery();
        }

        return (object) $this->dbQuery;
    }

    /**
     * Get Database connection status
     * @param string $type\reponse|message|driver
     * 
     * @return mixed\builder\Database\dbConnection
     */
    public function dbConnection(?string $type = null)
    {
        return $this->connection[$type] ?? $this->connection;
    }
    
    /**
     * Get Database `PDO` Driver
     * 
     * @return mixed\builder\Database\dbDriver
     */
    public function dbDriver()
    {
        return $this->pdo ?? false;
    }
    
    /**
     * Get last insert ID
     *
     * @return mixed\builder\Database\lastInsertId
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId() ?? null;
    }

    /**
     * Prepare query
     * z
     * @param string $query
     * 
     * @return void|object\builder\Database\query
     */
    public function query($query)
    {
        $this->isTableInitialized();
        try {
            $this->query = $this->im()->replaceWhiteSpace(
                str_replace("{$this->special_key} ", '', $query)
            );
            $this->stmt  = $this->pdo->prepare($this->query);
        } catch (\PDOException $e) {
            return $this->OrmErrorHandler($e);
        }

        return $this;
    }

    /**
     * Execute query statement
     * 
     * @return bool\builder\Database\execute
     */
    public function execute()
    {
        // $this->stmt->execute();

        // try {
        //     $this->stmt->execute();
        // } catch (\PDOException $e) {
        //     dd(
        //         $this->stmt,
        //         $e,
        //     );
        // }

        dd(
            // $this,
            $this->stmt->execute(),
        );

        return $this;
    }

    /**
     * Bind query data
     * 
     * @param string $param
     * @param mixed $value
     * @param  $type
     * 
     * @return void|object\builder\Database\bind
     */
    public function bind($param, $value, $type = null)
    {
        if(is_null($type)){
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        // bind params
        try {
            $this->stmt->bindValue($param, $value, $type);
        } catch (\PDOException $e) {
            return $this->OrmErrorHandler($e);
        }

        return $this;
    }

    /**
     * Fetch Request 
     * @param int $mode 
     * - [optional] PDO MySQL CONSTANTs
     * Default is PDO::FETCH_ASSOC
     * 
     * @return mixed
     */
    public function fetch(int $mode = PDO::FETCH_ASSOC)
    {
        return $this->stmt->fetch($mode);
    }

    /**
     * Fetch All Request
     * @param int $mode 
     * - [optional] PDO MySQL CONSTANTs
     * Default is PDO::FETCH_ASSOC
     *
     * @return mixed
     */
    public function fetchAll(int $mode = PDO::FETCH_ASSOC)
    {
        try {
            return $this->stmt->fetchAll($mode);
        } catch (\PDOException $e) {
            return $this->OrmErrorHandler($e);
        }
    }

    /**
     * Compile Raw or Normal Query data
     * 
     * @return object
     */ 
    public function compileQuery()
    {
        if($this->paginateQuery){

            // reset count
            $this->countQuery = false;

            // query builder
            $this->compileQueryBuilder(false);
        } else{
            // other query builder
            $this->compileQueryBuilder();
        }

        return $this;
    }

    /**
     * Close Database Connecton while returning the results
     * 
     * @return mixed
     */
    protected function getDataAndCloseConnection( $data )
    {
        // save to temp query data
        $this->saveDBQuery();

        // end final time
        $this->getExecutionTime();

        // close query on completion
        $this->closeQuery();
        
        return $data;
    } 
    
    /**
     * set query property
     * 
     * @return void
     */
    protected function saveDBQuery()
    {
        // save to temp queri data
        $this->dbQuery = [
            'stmt'          => $this->stmt,
            'query'         => $this->query,
            'raw'           => $this->rawQuery,
            'where'         => $this->where,
            'groupBy'       => $this->groupBy,
            'joins'         => $this->joins,
            'selectColumns' => $this->selectColumns,
            'paramValues'   => $this->paramValues,
            'runtime'       => $this->runtime,
        ];
    } 

    /**
     * Compile query build
     * 
     * @return void
     */ 
    protected function compileQueryBuilder()
    {
        // set query
        $this->query( $this->restructureQueryString() );

        // bind query
        $this->bindWhereQuery();

        // save to temp query data
        $this->saveDBQuery();
    }

    /**
     * Bind Where Querie
     * 
     * @param array $query
     * 
     * @return void
     */ 
    protected function bindWhereQuery()
    {
        foreach($this->where as $key => $value){
            // if Query is neither NULL/IS NULL/WHERE_COL_KEY
            // then we bind the data received

            // get query string and convert to UPPERCASE
            $query = strtoupper($value['query']);

            if( !( str_contains($query, "IS NOT NULL") 
                    || str_contains($query, "IS NULL") 
                    || str_contains($query, "{$this->special_key}") 
                ) ){

                // If Query is
                // BETWEEN/NOT BETWEEN/NOT IN/IN >>bind
                if((str_contains($query, "BETWEEN") || str_contains($query, "IN ("))){
                    
                    // values will be an index array
                    // bind each value
                    foreach($value['data']['value'] as $array_value){
                        $this->bind(":{$array_value}", $array_value);
                    }
                }else{
                    // normal >>bind
                    $this->bind(":{$value['data']['column']}", $value['data']['value']);
                }
            }else{
                // unset non usable keys from where clause
                unset($this->where["{$key}"]);
            }
        }
    }

    /**
     * Handle Errors
     * 
     * @param object $e
     * - \Instance of Throwable or PDOException
     * 
     * @return mixed
     */ 
    protected function OrmErrorHandler(Throwable|PDOException $e)
    {
        $queryString = $this->stmt->queryString ?? 'Unknown Error\\';

        // automatically pass error to check if database table exists
        $this->dbConnection('driver')->exec("SELECT 1 FROM `{$this->table}` LIMIT 1");

        // error message
        // $e->getTraceAsString()
        $errorMessage = "
            {$e->getMessage()}
            \n\n
            <<\\Query>> {$queryString}
        ";

        if(!defined('ORMDebugManager')){
            ORMDebugManager->handleException(
                new \Exception($queryString, (int) $e->getCode(), $e),
            );
        }
    }

    /**
     * Analize tables
     * 
     * @return object
     */
    protected function analizeTable()
    {
        // set query
        $this->query("ANALYZE TABLE {$this->table}");

        try {
            $this->execute();

            // message
            $result = $this->im()->convertOptimizeErrorTemp( $this->fetchAll(PDO::FETCH_ASSOC) );

            $this->endTimer();

            // if an error
            if($result['error']){
                return [
                    'status'    => Constant::STATUS_404, 
                    'message'   => $this->im()->replaceLeadEndSpace($result['message']),
                    'time'      => $this->timer,
                ];
            }

            return [
                'status'    => Constant::STATUS_200, 
                'message'   => $this->im()->replaceLeadEndSpace($result['message']),
                'time'      => $this->timer,
            ];
        } catch (\PDOException $e) {
            return $this->OrmErrorHandler($e);
        }
        
        return $this;
    }

    /**
     * Repair tables
     * @return object
     */
    protected function repairTable()
    {
        // set query
        $this->query("REPAIR TABLE {$this->table}");

        try {
            $this->execute();

            // message
            $result = $this->im()->convertOptimizeErrorTemp( $this->fetchAll(PDO::FETCH_ASSOC) );

            $this->endTimer();

            // if an error
            if($result['error']){
                return [
                    'status'    => Constant::STATUS_404, 
                    'message'   => $this->im()->replaceLeadEndSpace($result['message']),
                    'time'      => $this->timer,
                ];
            }

            return [
                'status'    => Constant::STATUS_200, 
                'message'   => $this->im()->replaceLeadEndSpace($result['message']),
                'time'      => $this->timer,
            ];
        } catch (\PDOException $e) {
            return $this->OrmErrorHandler($e);
        }
        
        return $this;
    }

    /**
     * Allow pagination
     *
     * @return object
     */
    protected function allowPaginate()
    {
        $this->paginateQuery = true;

        return $this;
    }

    /**
     * Allow query count(*)
     *
     * @return object
     */
    protected function allowCount()
    {
        $this->countQuery = true;

        return $this;
    }

    /**
     * Remove Tags Found as an XSS-Attack
     * @param bool $tag\Default true
     * - If set to true, then this will allow all possible tags 
     * - If false, it will allow few supported HTML5 tags
     * Apart from tags seen as an attack
     *
     * @return object
     */
    public function removeTags(?bool $tag = true)
    {
        $this->removeTags = true;
        if(!$tag){
            $this->allowAllTags = false;
        }
        return $this;
    }

    /**
     * Save data from each clause into a temp variable 
     * 
     * @return string|void
     */ 
    protected function restructureQueryString()
    {
        // save into property
        $joins = $this->im()->formatJoinQuery($this->joins);
        $limit = $this->im()->getLimitQuery($this->limit);
        
        // if query is count(*) only | perform SELECT By columns query 
        if($this->countQuery || $this->selectQuery){
            $query = "SELECT 
                        {$this->formatSelectQuery()} 
                        FROM `{$this->table}`";
        } else{
            $query = "SELECT * FROM `{$this->table}`";
        }

        return trim(
            "{$query}
            {$joins} 
            {$this->rawAndWherePositionBuilder()}
            {$this->groupBy} 
            {$this->orderBy} 
            {$limit}"
        );
    }

    /**
     * Position Raw and Where Queries
     * 1 === where
     * 2 === raw
     * 
     * @return string
     */ 
    protected function rawAndWherePositionBuilder()
    {
        $isInt = is_int($this->bt_raw_and_where);

        // query default
        $query = "{$this->tempQuery} {$this->tempRawQuery}";

        if($isInt && $this->bt_raw_and_where === 2){
            $query = "{$this->tempRawQuery} {$this->tempQuery}";
        } 

        return $query;
    }

    /**
     * Format array of selected columns passed by the users
     * 
     * @return string|void
     */ 
    protected function formatSelectQuery()
    {
        if(empty($this->selectColumns)){
            if($this->countQuery){
                return "count(*)";
            }
            return "*";
        }else{
            $asCount        = ""; 
            $formatColumn   = true;

            // when trying to count data
            if($this->countQuery){
                $asCount = ", count(*)";
            }

            // reset back when trying to paginate
            if($this->paginateQuery){
                $asCount        = "";
                $formatColumn   = false;
            }

            // trim excess strings if any
            $this->selectColumns = $this->im()->arrayWalkerTrim($this->selectColumns, $formatColumn);

            // get query string
            $queryString = implode(', ', $this->selectColumns);

            return "{$queryString}{$asCount}";
        }
    }

    /**
     * Format raw data when trying to count as result
     * 
     * @return string
     */ 
    protected function saveTempRawQuery(?array $query = [])
    {
        $this->tempRawQuery = $this->im()->saveTempQuery($query);

        return $this;
    }

    /**
     * Save data from each clause into a temp variable 
     * using the implode, to convert array data into a string and add to all instance
     * 
     * @param array $query
     * 
     * @return object\builder\Database\saveTempQuery
     */ 
    protected function saveTempQuery(?array $query = [])
    {
        $this->tempQuery = $this->im()->saveTempQuery($query);

        return $this;
    }

    /**
     * Save data from each clause into a temp variable 
     * using the implode, to convert array data into a string and add to all instance
     * 
     * @param array $param
     * 
     * @return object
     */ 
    protected function saveTempUpdateQuery(?array $param = [])
    {
        $this->tempUpdateQuery = $this->im()->saveTempUpdateQuery($param);
        
        return $this;
    }

    /**
     * Save data from increment queries
     * 
     * @param array $data
     * @param bool $type
     * true for increment|false for decrement
     * 
     * @return object
     */ 
    protected function saveTempIncrementQuery($data = [], $type = true)
    {
        $this->tempIncrementQuery = $this->im()->saveTempIncrementQuery($data, $type);
        
        return $this;
    }

    /**
     * Create insert value pairs
     * 
     * @param array $param
     * @return object
     */ 
    protected function saveTempInsertQuery(?array $param = [])
    {
        $this->tempInsertQuery = $this->im()->saveTempInsertQuery($param);
        
        return $this;
    }

    /**
     * Perform time stamp query to determine if 
     * `created_at` and `updated_at` columns exists in a table
     * 
     * @return object
     */ 
    protected function timeStampsQuery()
    {
        try {
            $PDO    = $this->dbDriver();
            $stmt   = $PDO->query("SHOW COLUMNS FROM {$this->table} WHERE Field IN ('created_at', 'updated_at')");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if(is_array($result) && count($result) === 2){
                return true;
            }
            return false;
        } catch (\PDOException $th) {
            return false;
        }
    }

    