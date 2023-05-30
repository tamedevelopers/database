<?php

declare(strict_types=1);

namespace builder\Database\Query;

use PDO;
use DateTime;
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
     */
	public function __construct()
    {
        if(is_null($this->console)){
            $this->initConsole();
        }
	}

    /**
     * Constructor init
     * For class that extends without calling the constructor
     */
	public function initConsole()
    {
        if(is_null($this->console)){
            $this->console = new Manager();
        }

        return $this->console;
	}

    /**
     * Get Database Application Config
     * - Returns all Database setup CONSTANTS as an arrays
     * - or .env Information if present
     * 
     * @return mixed
     */
    public function env()
    {
        return $this->initConsole()->getConfig('all');
    }

    /**
     * Get last Database query sample
     * 
     * @return mixed\builder\Database\dbQuery
     */
    public function dbQuery()
    {
        if(is_null($this->dbQuery)){
            $this->setQueryProperty();
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
        return $this->dbh ?? false;
    }

    /**
     * Get Database Connection Constant Status
     * - When You connecto to Database using autoload or -- Direct connection
     * A Global Constant is Instantly defined for us.
     * This is to check if it has been defined or not
     * 
     * @return bool\builder\Database\isDatabaseConnectionDefined
     */
    public function isDatabaseConnectionDefined()
    {
        return defined('DATABASE_CONNECTION');
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
        $this->modelQuery   = true;

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
            $this->query = $this->initConsole()->replaceWhiteSpace(
                $this->query
            );
            $this->stmt  = $this->dbh->prepare($this->query);
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
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
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
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
        $this->stmt->execute();

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
    protected function fetchAll(int $mode = PDO::FETCH_OBJ)
    {
        try {
            return $this->stmt->fetchAll($mode);
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
        }
    }

    /**
     * Close Database Connecton while returning the results
     * 
     * @return mixed
     */
    protected function getDataAndCloseConnection( $data )
    {
        // save to temp query data
        $this->setQueryProperty();

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
    protected function setQueryProperty()
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
                'status'    => self::ERROR_404, 
                'message'   => preg_replace(
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

            // cons
            $con = $this->initConsole();

            // message
            $result = $con->convertOptimizeErrorTemp( $this->fetchAll(PDO::FETCH_ASSOC) );

            $this->endTimer();

            // if an error
            if($result['error']){
                return [
                    'status'    => self::ERROR_404, 
                    'message'   => $con->replaceLeadEndSpace($result['message']),
                    'time'      => $this->timer,
                ];
            }

            return [
                'status'    => self::ERROR_200, 
                'message'   => $con->replaceLeadEndSpace($result['message']),
                'time'      => $this->timer,
            ];
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
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

            // cons
            $con = $this->initConsole();
            
            // message
            $result = $con->convertOptimizeErrorTemp( $this->fetchAll(PDO::FETCH_ASSOC) );

            $this->endTimer();

            // if an error
            if($result['error']){
                return [
                    'status'    => self::ERROR_404, 
                    'message'   => $con->replaceLeadEndSpace($result['message']),
                    'time'      => $this->timer,
                ];
            }

            return [
                'status'    => self::ERROR_200, 
                'message'   => $con->replaceLeadEndSpace($result['message']),
                'time'      => $this->timer,
            ];
        } catch (\PDOException $e) {
            return $this->errorTemp($e, true);
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
        if($this->paginateQuery){

            // reset count
            $this->countQuery = false;

            // query builder
            $this->compileQueryBuilder(false);
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
        $this->paginateQuery = true;

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
     * @param bool $tag\Default true
     * - If set to true, then this will allow all possible tags 
     * - If false, it will allow few supported HTML5 tags
     * Apart from tags seen as an attack
     *
     * @return object\builder\Database\removeTags
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
        // cons
        $con = $this->initConsole();

        // save into property
        $joins = $con->formatJoinQuery($this->joins);
        $limit = $con->getLimitQuery($this->limit);
        
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
            $this->selectColumns = $this->initConsole()->arrayWalkerTrim($this->selectColumns, $formatColumn);

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
        $this->tempRawQuery = $this->initConsole()->saveTempQuery($query);

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
        $this->tempQuery = $this->initConsole()->saveTempQuery($query);

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
        $this->tempUpdateQuery = $this->initConsole()->saveTempUpdateQuery($param);
        
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
        $this->tempIncrementQuery = $this->initConsole()->saveTempIncrementQuery($data, $type);
        
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
        $this->tempInsertQuery = $this->initConsole()->saveTempInsertQuery($param);
        
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
        $this->timeStampsQuery      = null;
        $this->tempUpdateQuery      = null;
        $this->tempInsertQuery      = null;
        $this->tempIncrementQuery   = null;
        $this->bt_raw_and_where     = null;
        $this->joins                = [];
        $this->where                = [];
        $this->rawQuery             = [];
        $this->selectColumns        = [];
        $this->paramValues          = [];
        $this->selectQuery          = false;
        $this->paginateQuery        = false;
        $this->countQuery           = false;
        $this->modelQuery           = false;
        $this->removeTags           = false;
        $this->allowAllTags         = true;
        $this->runtime              = 0.00;
        $this->timer                = [
            'start'   => 0,
            'end'     => 0,
        ];
    }

    /**
     * Microtime start time
     * @return void
     */ 
    protected function startTimer()
    {
        $this->timer['start'] = new DateTime();
    }

    /**
     * Microtime end time
     * @return void
     */ 
    protected function endTimer()
    {
        $this->timer['end'] = new DateTime();
    }

    /**
     * Get execution time
     * @return void
     */ 
    protected function getExecutionTime()
    {
        // end timer
        $this->endTimer();

        $start  = $this->timer['start'];
        $end    = $this->timer['end'];

        // time difference
        if(is_object($start)){
            $diff = $start->diff($end);

            // runtime  
            $this->runtime = $diff->format('%s.%f');

            // round to 2 decimal
            $this->runtime = round((float) $this->runtime, 2);
        }
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
            $db = $this->initConsole()->getConfig('all');
            
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
        $this->initConsole()->initConfiguration( $options );
    }

}
