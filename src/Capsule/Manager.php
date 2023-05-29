<?php

declare(strict_types=1);

namespace builder\Database\Capsule;

use builder\Database\Constants;


class Manager extends Constants{
    
    /**
     * Remove all whitespace characters
     * @var string
     */
    static public $regex_whitespace = "/\s+/";

    /**
     * Remove leading or trailing spaces/tabs from each line
     * @var string
     */
    static public $regex_lead_and_end = "/^[ \t]+|[ \t]+$/m";

    /**
     * pagination text
     * @var array
     */
    static public $pagination_text = [
        'first'     => 'First',
        'last'      => 'Last',
        'next'      => 'Next',
        'prev'      => 'Prev',
        'span'      => 'pagination-highlight',
        'showing'   => 'Showing',
        'to'        => 'to',
        'of'        => 'of',
        'results'   => 'results',
        'view'      => 'simple',
    ];

    /**
     * @var array
     */
    static public $pagination_views = [
        'bootstrap' => 'bootstrap',
        'simple'    => 'simple',
    ];

    /**
     * @var array
     */
    static private $collations = [
        'utf8mb4_unicode_ci',
        'utf8mb4_general_ci',
        'utf8mb4_bin',
        'utf8_general_ci',
        'utf8_bin',
        'latin1_general_ci',
        'latin1_bin',
    ];

    /**
     * @var array
     */
    static private $charsets = [
        'utf8mb4',
        'utf8',
        'latin1',
    ];
    
    /**
     * Initilize and Set the Database Configuration on constructor
     * 
     * @param array $options
     * APP_DEBUG
     * DB_HOST
     * DB_DATABASE
     * DB_USERNAME
     * DB_PASSWORD
     * DB_PORT
     * DB_COLLATION
     * DB_CHARSET
     * 
     * @return void
     */
    static public function initConfiguration(?array $options = [])
    {
        $defaultOption = array_merge([
            'APP_DEBUG'     => true,
            'DRIVER_NAME'   => 'mysql',
            'DB_HOST'       => 'localhost',
            'DB_DATABASE'   => '',
            'DB_USERNAME'   => '',
            'DB_PASSWORD'   => '',
            'DB_PORT'       => 3306,
            'DB_COLLATION'  => 'utf8mb4_unicode_ci',
            'DB_CHARSET'    => 'utf8mb4',
        ], $options);

        // get accepted data
        $defaultOption['DB_COLLATION']  = self::findCollation($defaultOption['DB_COLLATION']);
        $defaultOption['DB_CHARSET']    = self::findCharset($defaultOption['DB_CHARSET']);
        

        // APP_DEBUG
        if ( ! defined('APP_DEBUG') ) {
            define('APP_DEBUG', self::setEnvBool($_ENV['APP_DEBUG'] ?? $defaultOption['APP_DEBUG']));
        }

        // DRIVER_NAME
        if ( ! defined('DRIVER_NAME') ) {
            define('DRIVER_NAME', $_ENV['DRIVER_NAME'] ?? $defaultOption['DRIVER_NAME']);
        }

        // DB_HOST
        if ( ! defined('DB_HOST') ) {
            define('DB_HOST', $_ENV['DB_HOST'] ?? $defaultOption['DB_HOST']);
        }
        
        // DB_DATABASE
        if ( ! defined('DB_DATABASE')) {
            define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? $defaultOption['DB_DATABASE']);
        }
        
        // DB_USERNAME
        if ( ! defined('DB_USERNAME')) {
            define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? $defaultOption['DB_USERNAME']);
        }
        
        // DB_PASSWORD
        if ( ! defined('DB_PASSWORD')) {
            define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? $defaultOption['DB_PASSWORD']);
        }
        
        // DB_PORT
        if ( ! defined('DB_PORT')) {
            define('DB_PORT', $_ENV['DB_PORT'] ?? $defaultOption['DB_PORT']);
        }
        
        // DB_COLLATION
        if ( ! defined('DB_COLLATION')) {
            define('DB_COLLATION', $_ENV['DB_COLLATION'] ?? $defaultOption['DB_COLLATION']);
        }
        
        // DB_CHARSET
        if ( ! defined('DB_CHARSET')) {
            define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? $defaultOption['DB_CHARSET']);
        }
    }

    /**
     * Get Database Constant configuration data
     * @param string $key\* 
     * APP_DEBUG|DB_HOST|DB_DATABASE|DB_USERNAME|DB_PASSWORD|DB_PORT|DB_COLLATION|DB_CHARSET
     * 
     * @return string|array
     */
    static public function getConfig($key = 'DB_HOST')
    {
        $data =  [
            'APP_DEBUG'     => defined('APP_DEBUG')     ? APP_DEBUG     : true,
            'DRIVER_NAME'   => defined('DRIVER_NAME')   ? DRIVER_NAME   : 'mysql',
            'DB_HOST'       => defined('DB_HOST')       ? DB_HOST       : 'localhost',
            'DB_DATABASE'   => defined('DB_DATABASE')   ? DB_DATABASE   : null,
            'DB_USERNAME'   => defined('DB_USERNAME')   ? DB_USERNAME   : null,
            'DB_PASSWORD'   => defined('DB_PASSWORD')   ? DB_PASSWORD   : null,
            'DB_PORT'       => defined('DB_PORT')       ? DB_PORT       : 3306,
            'DB_COLLATION'  => defined('DB_COLLATION')  ? DB_COLLATION  : 'utf8mb4_unicode_ci',
            'DB_CHARSET'    => defined('DB_CHARSET')    ? DB_CHARSET    : 'utf8mb4',
        ];

        return $data[$key] ?? array_merge($data, $_ENV);
    }

    /**
     * Set env boolean value
     * @param string $value
     * 
     * @return string|null
     */
    static public function setEnvBool($value)
    {
        if(is_string($value)){
            if(trim((string) strtolower($value)) === 'true'){
                return true;
            }
            return false;
        }

        return $value;
    }

    /**
     * Set header response code
     * Mainly for firstOrFail()
     * 
     * @return void|null\builder\Database\setHeaders
     */
    static public function setHeaders()
    {
        // Set HTTP response status code to 404
        http_response_code(404);

        // Flush output buffer
        flush();

        // Exit with response 404
        exit(1);
    }

    /**
     * Get supported database Collation
     * @param string $collation
     * 
     * @return string|null
     */
    static public function findCollation(?string $collation = null)
    {
        // collation get
        if(!in_array(trim(strtolower((string) $collation)), self::$collations)){
            return self::$collations[1]; 
        }

        return $collation; 
    }

    /**
     * Get supported database Charset
     * @param string $charset
     * 
     * @return string|null
     */
    static public function findCharset(?string $charset = null)
    {
        // charset get
        if(!in_array(trim(strtolower((string) $charset)), self::$charsets)){
            return self::$charsets[0]; 
        }

        return $charset; 
    }

    /**
     * Remove whitespace from string
     * 
     * @param string $string
     * 
     * @return string
     */ 
    static public function replaceWhiteSpace(?string $string = null)
    {
        return trim(preg_replace(
            self::$regex_whitespace, 
            " ", 
            $string
        ));
    }

    /**
     * Remove leading and ending space from string
     * 
     * @param string $string
     * 
     * @return string
     */ 
    static public function replaceLeadEndSpace(?string $string = null)
    {   
        return preg_replace(self::$regex_lead_and_end, " ", $string);
    }

    /**
     * Save data from each clause into a temp variable 
     * using the implode, to convert array data into a string and add to all instance
     * 
     * @param array $query
     * 
     * @return string
     */ 
    static public function saveTempQuery(?array $query = [])
    {
        // add to tempQuery
        foreach($query as $value){
            $data[] = $value['query'];
        }

        return trim(implode('', $data ?? []));
    }

    /**
     * Create insert statement query and save as temp query
     * using the implode, to convert array data into a string and add to all instance
     * 
     * @param array $param
     * 
     * @return array
     */ 
    static public function saveTempInsertQuery(?array $param = [])
    {
        // add to tempQuery
        foreach($param as $key => $value){
            $data[]     = "{$key}";
            $values[]   = ":{$key}";
        }

        $tempInsertQuery = [
            'columns'   => trim(implode(', ', $data ?? [])),
            'values'    => trim(implode(', ', $values ?? [])),
        ];
        
        return $tempInsertQuery;
    }

    /**
     * Save data from each clause into a temp variable 
     * using the implode, to convert array data into a string and add to all instance 
     * 
     * @param array $param
     * 
     * @return string
     */ 
    static public function saveTempUpdateQuery(?array $param = [])
    {
        // add to tempQuery
        foreach($param as $key => $value){
            $data[] = "{$key}=:{$key}";
        }

        return trim(implode(', ', $data ?? []));
    }

    /**
     * Save data from increment queries
     * 
     * @param array $data
     * @param bool $type
     * true for increment|false for decrement
     * 
     * @return string
     */ 
    static public function saveTempIncrementQuery($data = [], $type = true)
    {
        $sign = '+'; //increment
        if(!$type){
            $sign = '-'; //decrement
        }

        $tempIncrementQuery = "{$data['column']}={$data['column']} {$sign} :{$data['column']}";
        if(count($data['param']) > self::COUNT){
            $tempIncrementQuery .= ",";
        }
        
        return $tempIncrementQuery;
    }

     /**
     * Format Jions query 
     * @param array $joins
     * 
     * @return string
     */ 
    static public function formatJoinQuery($joins = [])
    {
        $query = '';
        if (!empty($joins)) {
            foreach ($joins as $join) {
                $query .= " {$join['type']} 
                            JOIN 
                            `{$join['table']}` 
                            ON {$join['foreignColumn']} 
                            {$join['operator']} 
                            {$join['localColumn']}";
            }
        }

        return $query;
    }

    /**
     * Get Limits query
     * 
     * @param string|null $limit
     * 
     * @return string|null
     */ 
    static public function getLimitQuery($limit = null)
    {
        if(!empty($limit) && !is_null($limit)){
            return $limit;
        }
        return;
    }

    /**
     * Get Where Clause Operator and value data
     * 
     * @param string $operator
     * @param string $value
     * 
     * @return array
     */ 
    static public function configWhereClauseOperator($operator = null, $value = null)
    {
        $data = [];

        // both value and operator came
        if(!is_null($operator) && !is_null($value)){
            if(empty($operator)){
                // operator is empty
                $data = [
                    'operator'  => '=', 
                    'value'     => $value
                ];
            }else{
                // check for LIKE
                if(in_array($operator, ['LIKE', strtolower($operator)])){
                    $data = [
                        'operator'  => " {$operator} ", 
                        'value'     => $value
                    ];
                }else{
                    $data = [
                        'operator'  => $operator, 
                        'value'     => $value
                    ];
                }
            }
        }else{
            $data = [
                'operator'  => '=', 
                'value'     => $operator
            ];
        }

        return $data;
    }

    /**
     * Configure Where Columns Clause Operator and value data
     * 
     * @param string|array $column
     * @param string $operator
     * @param string $value
     * 
     * @return array
     */ 
    static public function configWhereColumnClauseOperator($column = null, $operator = null, $value = null)
    {
        $data = [];

        // begin formatting
        if(is_array($column)){
            foreach($column as $cols){
                // check if all param found
                if(count($cols) === 3){
                    $data[] = [
                        'column1'   => $cols[0], 
                        'operator'  => $cols[1], 
                        'column2'   => $cols[2]
                    ];
                }else{
                    $data[] = [
                        'column1'   => $cols[0], 
                        'operator'  => "=", 
                        'column2'   => $cols[1]
                    ];
                }
            }
        }else{
            $temData = self::configWhereClauseOperator($operator, $value);
            $data[] = [
                'column1'   => $column, 
                'operator'  => trim($temData['operator']), 
                'column2'   => $temData['value']
            ];
        }

        return $data;
    }

    /**
     * Configure Increment Operators 
     * @param string $column
     * @param string $count
     * @param string $param
     * 
     * @return array
     */ 
    static public function configIncrementOperator($column = null, $count = 1, $param = [])
    {
        $data = [];

        // both column and count came
        if(!is_null($column) && is_int($count)){
            $data = [
                'column'    => $column, 
                'count'     => $count, 
                'param'     => $param
            ];
        }else{
            // if no count is passed
            if(is_array($count)){
                $data = [
                    'column'    => $column, 
                    'count'     => 1, 
                    'param'     => $count
                ];
            }else{
                $data = [
                    'column'    => $column, 
                    'count'     => $count, 
                    'param'     => $param
                ];
            }
        }

        return [
            'column' => $data['column'],
            'count'  => $data['count'],
            'param'  => $data['param'],
        ];
    }

    /**
     * Trim empty strings from an array value
     * 
     * @param array $param
     * @param bool $indent
     * 
     * @return array
     */ 
    static public function arrayWalkerTrim(?array $param = [], ?bool $indent = false)
    {
        array_walk($param, function(&$value, $index) use($indent){
            if(!empty($value)){
                if(is_string($value)){
                    // trim
                    $value = trim($value);

                    // if indentation of value is allowed
                    if($indent){
                        $value = "`$value`";
                    }
                }
            }
        });

        return $param;
    }

    /**
     * Create String Template of all possible analysis error
     * @param array|object $result 
     * 
     * @return array
     */ 
    static public function convertOptimizeErrorTemp($result)
    {
        $temp           = '';
        $result         = $result ?? [];
        $errorStatus    = false;

        foreach($result as $key => $error){
            $temp .= "
                #{$key} {$error['Table']} <<\\{$error['Op']}>> <span>{$error['Msg_type']}</span>{$error['Msg_text']}";

            // check for error
            if(in_array(strtolower($error['Msg_type']), ['error',])){
                $errorStatus = true;
            }
        }

        return [
            'error'     => $errorStatus,
            'message'   => $temp,
        ];
    }

}