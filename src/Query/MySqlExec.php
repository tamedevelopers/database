<?php

declare(strict_types=1);

namespace builder\Database\Query;

use PDO;
use Exception;
use Throwable;
use PDOException;
use builder\Database\Constants;
use builder\Database\Capsule\Manager;
use builder\Database\Traits\ReusableTrait;
use builder\Database\Traits\MySqlProperties;

class MySqlExec  extends Constants{

    use MySqlProperties, 
        ReusableTrait;

    /**
     * Constructor
     * @return void
     */
	public function __construct()
    {
        $this->console = new Manager();
	}

    /**
     * Get Application Config Settings
     * 
     * @return string|array\builder\Database\AppConfig
     */
    public function AppConfig()
    {
        return $this->console::getConfig('all');
    }

    /**
     * Get last Database query sample
     * 
     * @return mixed\builder\Database\getQuery
     */
    public function getQuery()
    {
        return is_null($this->getQuery) 
                ? (object) $this->setQueryProperty()
                : (object) $this->getQuery;
    }

    /**
     * Get Database connection status
     * @param string $type\reponse|message|driver
     * 
     * @return mixed\builder\Database\getConnection
     */
    public function getConnection(?string $type = null)
    {
        return $this->connection[$type] ?? $this->connection;
    }

    /**
     * Table name
     * This is being used on all instance of one query
     * 
     * @param string $table
     * 
     * @return object\builder\Database\table
     */
    public function table(?string $table)
    {
        $this->closeQuery();
        $this->startTimer();
        $this->table        = $table;
        $this->rawQuery     = false;
        $this->modelQuery   = true;

        return $this;
    }

    /**
     * Query raw DB
     *
     * @param string $query 
     * @return object\builder\Database\raw
     */ 
    public function raw($query = null)
    {
        $this->closeQuery();
        
        $this->modelQuery   = false;
        $this->rawQuery     = true;
        
        if($this->rawQuery){
            $this->startTimer();
            $this->tempRawQuery = "$query";

            // query builder
            $this->compileQueryBuilder();
        }
        return $this;
    }

    /**
     * Get last insert ID
     *
     * @return mixed\builder\Database\lastInsertId
     */
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId() ?? null;
    }

    /**
     * Prepare query
     * 
     * @param string $query
     * 
     * @return void|object\builder\Database\query
     */
    public function query($query)
    {
        try {
            $this->query = str_replace("{$this->special_key} ", '', $query);
            $this->stmt  = $this->dbh->prepare($this->query);
        } catch (\PDOException $th) {
            return $this->errorTemp($th, true);
        }

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
        if(is_null($type))
        {
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
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true);
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
        return $this->stmt->execute();
    }

    /**
     * Bind Where Querie
     * 
     * @param array $query
     * 
     * @return object\builder\Database\bindWhereQuery
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
        
        return $this;
    }

    /**
     * Create error temp
     * 
     * @param object $e\Instance of Throwable or PDOException class
     * @param bool $prepareQueryError
     * 
     * @return array\builder\Database\errorTemp
     */ 
    protected function errorTemp(Throwable|PDOException $e, $prepareQueryError = false)
    {
        $dbError        = "";
        $exception      = (new Exception);
        $queryString    = $this->stmt->queryString ?? 'Unknown Error\\';

        // if for prepared query error only
        if($prepareQueryError){
            if($this->connection['status'] != self::ERROR_200){
                $queryString = 'Database Connection Error\\';
                $dbError = "<<\\Table name `{$this->connection['message']}`>>";
            }else{
                $queryString = $this->stmt->queryString ?? 'Unknown Error\\';
            }
        }

        return [
            'response' => self::ERROR_404, 
            'message' => preg_replace(
                '/^[ \t]+|[ \t]+$/m', '', 
                "   $dbError
                    {$exception->getTraceAsString()}
                    <<\\Query>> {$queryString}
                    <br><br>
                    <<\\PDO::ERROR>> {$e->getMessage()}
                "
            )
        ];
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
            $result = $this->console::convertOptimizeErrorTemp( $this->tryFetchAll(false) );

            $this->endTimer();

            // if an error
            if($result['error']){
                return [
                    'response'  => self::ERROR_404, 
                    'message'   => $this->console::replaceLeadEndSpace($result['message']),
                    'time'      => $this->timer,
                ];
            }

            return [
                'response'  => self::ERROR_200, 
                'message'   => $this->console::replaceLeadEndSpace($result['message']),
                'time'      => $this->timer,
            ];
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true);
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
            $result = $this->console::convertOptimizeErrorTemp( $this->tryFetchAll(false) );

            $this->endTimer();

            // if an error
            if($result['error']){
                return [
                    'response'  => self::ERROR_404, 
                    'message'   => $this->console::replaceLeadEndSpace($result['message']),
                    'time'      => $this->timer,
                ];
            }

            return [
                'response'  => self::ERROR_200, 
                'message'   => $this->console::replaceLeadEndSpace($result['message']),
                'time'      => $this->timer,
            ];
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true);
        }
        
        return $this;
    }

    /**
     * Compile Raw or Normal Query data
     * 
     * @return void|object\compileQuery
     */ 
    public function compileQuery()
    {
        if($this->PaginateQuery){

            // reset count
            $this->countQuery = false;

            // query builder
            $this->compileQueryBuilder();
        }
        // other query
        elseif($this->modelQuery){
            // query builder
            $this->compileQueryBuilder();
        }
        
        return $this;
    }

    /**
     * Allow pagination
     *
     * @return object\builder\Database\allowPaginate
     */
    protected function allowPaginate()
    {
        $this->PaginateQuery    = true;

        return $this;
    }

    /**
     * Allow query count(*)
     *
     * @return object\builder\Database\allowCount
     */
    protected function allowCount()
    {
        $this->countQuery = true;

        return $this;
    }

    /**
     * Remove Tags Found as an XSS-Attack
     *
     * @return object\builder\Database\removeTags
     */
    public function removeTags()
    {
        $this->removeTags = true;

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
        $joins = $this->console::formatJoinQuery($this->joins);
        $limit = $this->console::getLimitQuery($this->limit);
        
        // if raw query
        if($this->rawQuery){
            return $this->console::replaceWhiteSpace( 
                "{$this->rawCountQueryFinder()} 
                {$joins} 
                {$this->tempQuery} 
                {$this->groupBy} 
                {$this->orderBy} 
                {$limit}"
            );
        }
        // if query is count(*) only | perform SELECT By columns query 
        else if($this->countQuery || $this->selectQuery){
            $query = "SELECT 
                        {$this->formatSelectQuery()} 
                        FROM `{$this->table}`";
        }else{
            $query = "SELECT * FROM `{$this->table}`";
        }

        return $this->console::replaceWhiteSpace(
            "{$query}
            {$joins} 
            {$this->tempQuery} 
            {$this->groupBy} 
            {$this->orderBy} 
            {$limit}"
        );
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
        $this->setQueryProperty();
    }

    /**
     * Format array of selected columns passed by the users
     * 
     * @return string|void
     */ 
    protected function formatSelectQuery()
    {
        if(is_array($this->selectColumns) && count($this->selectColumns) === 0){
            if($this->countQuery){
                return "count(*)";
            }
            return "*";
        }else{
            // trim excess strings if any
            $this->selectColumns = $this->console::arrayWalkerTrim($this->selectColumns);

            return implode(', ', $this->selectColumns);
        }
    }

    /**
     * Format raw data when trying to count as result
     * 
     * @return string
     */ 
    protected function rawCountQueryFinder()
    {
        if($this->countQuery && !$this->PaginateQuery){
            if(!is_null($this->tempRawQuery)){
                $this->tempRawQuery =  str_replace("SELECT *", "SELECT count(*) as `count`", $this->tempRawQuery);
            }
        }

        return $this->tempRawQuery;
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
        $this->tempQuery = $this->console::saveTempQuery($query);

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
        $this->tempUpdateQuery = $this->console::saveTempUpdateQuery($param);
        
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
        $this->tempIncrementQuery = $this->console::saveTempIncrementQuery($data, $type);
        
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
        $this->tempInsertQuery = $this->console::saveTempInsertQuery($param);
        
        return $this;
    }

    /**
     * Close all queries and restore back to default
     *
     * @return void
     */
    protected function closeQuery()
    {
        $this->stmt                 = null;
        $this->query                = null;
        $this->limit                = null;
        $this->limitCount           = null;
        $this->offset               = null;
        $this->offsetCount          = null;
        $this->orderBy              = null;
        $this->groupBy              = null;
        $this->tempQuery            = null;
        $this->tempRawQuery         = null;
        $this->tempUpdateQuery      = null;
        $this->tempInsertQuery      = null;
        $this->tempIncrementQuery   = null;
        $this->joins                = [];
        $this->where                = [];
        $this->selectColumns        = [];
        $this->paramValues          = [];
        $this->selectQuery          = false;
        $this->PaginateQuery        = false;
        $this->countQuery           = false;
        $this->modelQuery           = false;
        $this->rawQuery             = false;
        $this->removeTags           = false;
        $this->runtime              = 0.00;
        $this->timer                = [
            'start'   => 0.00,
            'end'     => 0.00,
            'runtime' => 0.00,
        ];
    }

     /**
     * Get last insert ID
     * @param bool $type true or false
     * If true then it return an OBJECT data
     * Else returns and ARRAY data
     *
     * @return mixed\tryFetchAll
     */
    protected function tryFetchAll($type = true)
    {
        try {
            $getType = $type ? 
                        PDO::FETCH_OBJ : 
                        PDO::FETCH_ASSOC;

            return $this->stmt->fetchAll($getType);
        } catch (\Throwable $th) {
            return $this->errorTemp($th, true);
        }
    }

    /**
     * Close all query and get results
     * 
     * @return mixed
     */
    protected function getQueryResult( $data )
    {
        // end final time
        $this->getExecutionTime();

        // save to temp query data
        $this->setQueryProperty();

        if(is_bool($data)){
            return false;
        }

        // close query on completion
        $this->closeQuery();
        
        return $data;
    } 
    
    /**
     * set query property
     * 
     * @return object|array\builder\Database\setQueryProperty
     */
    protected function setQueryProperty()
    {
        // save to temp queri data
        $this->getQuery = [
            'stmt'          => $this->stmt,
            'where'         => $this->where,
            'groupBy'       => $this->groupBy,
            'joins'         => $this->joins,
            'selectColumns' => $this->selectColumns,
            'paramValues'   => $this->paramValues,
            'time'          => $this->timer,
            'runtime'       => $this->runtime,
        ];
        
        return $this->getQuery;
    } 

    /**
     * Microtime start time
     * @return void
     */ 
    protected function startTimer()
    {
        $this->timer['start'] = microtime(true);
    }

    /**
     * Microtime end time
     * @return void
     */ 
    protected function endTimer()
    {
        $this->timer['end'] = microtime(true);
    }

    /**
     * Get execution time
     * @return void
     */ 
    protected function getExecutionTime()
    {
        // end timer
        $this->endTimer();

        $this->runtime = ($this->timer['end'] - $this->timer['start']) * 1000;

        $this->runtime = (float) sprintf('%0.1f', $this->runtime);

        $this->timer['runtime'] = $this->runtime;
    }

    /**
     * Staring the Database
     * 
     * @return array
     */
    protected function startDatabase()
    {
        try {
            // get all data
            $db = $this->console::getConfig('all');
            
            // Set DSN
            $dsn = "mysql:host={$db['DB_HOST']};port={$db['DB_PORT']};dbname={$db['DB_DATABASE']}";

            // Create new PDO
            $this->dbh  = new PDO($dsn, $db['DB_USERNAME'], $db['DB_PASSWORD'], [
                PDO::ATTR_PERSISTENT    => true,
                PDO::ATTR_ERRMODE	    => PDO::ERRMODE_EXCEPTION,
            ]);     
            
            // exec more options settings
            $this->dbh->exec("SET NAMES {$db['DB_CHARSET']}");
            $this->dbh->exec("SET COLLATION_CONNECTION = '{$db['DB_COLLATION']}'");
            $this->dbh->exec("USE {$db['DB_DATABASE']}");

            $this->connection    = [
                'status'    => self::ERROR_200, 
                'message'   => 'Connection successful', 
                'driver'    => $this->dbh
            ];

            return $this;
        } catch(PDOException $e){
            $this->connection    = [
                'status'    => self::ERROR_400, 
                'message'   => $e->getMessage(), 
                'driver'    => $this->dbh
            ];

            return $this;
        }
    }

    /**
     * Initilize and Set the Database Configuration on constructor
     * 
     * @param array $options
     * DB_HOST\DB_HOST\DB_DATABASE\DB_USERNAME\DB_PASSWORD
     * 
     * @return void
     */
    protected function initConfiguration(?array $options = [])
    {
        $this->console::initConfiguration( $options );
    }

}
