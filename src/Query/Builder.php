<?php

declare(strict_types=1);

namespace builder\Database\Query;

class Builder extends MySqlExec{
    
    /**
     * Set order by
     * 
     * @param string $column
     * @param string|null $direction\Default is `ASC`
     * 
     * @return object
     */ 
    public function orderBy($column, $direction = null)
    {
        // empty check
        if(empty($direction) || is_null($direction)){
            $direction = 'ASC';
        }
        
        // orderBy query
        $this->orderBy = "ORDER BY {$column} {$direction}";

        return $this;
    }

    /**
     * Set orderByRaw
     * 
     * @param string $query
     * 
     * @return object
     */ 
    public function orderByRaw($query = null)
    {
        $this->orderBy = "ORDER BY {$query}";

        return $this;
    }

    /**
     * Get latest query
     * @param string $column
     * Default column has been set to 'id'
     *
     * @return object
     */
    public function latest($column = 'id')
    {
        $this->orderBy($column, 'DESC');

        return $this;
    }

    /**
     * Get oldest query
     * @param string $column
     * Default column has been set to 'id'
     *
     * @return object
     */
    public function oldest($column = 'id')
    {
        $this->orderBy($column);

        return $this;
    }

    /**
     * Set random order
     * 
     * @return object
     */ 
    public function inRandomOrder()
    {
        $this->orderBy = "ORDER BY RAND()";

        return $this;
    }

    /**
     * Set random order
     * 
     * @return object
     */ 
    public function random()
    {
        $this->inRandomOrder();
        
        return $this;
    }

    /**
     * Set limits
     * 
     * @param string $limit\Default is set to `0`
     * 
     * @return object
     */ 
    public function limit($limit = 1)
    {
        // limit
        $this->limitCount = $limit;

        $this->limit = "LIMIT {$this->limitCount}";

        // offset query check
        if( str_contains(strtoupper((string) $this->offset), "OFFSET")  ){
            $this->limit = "LIMIT {$this->offsetCount}, {$this->limitCount}";
        }

        return $this;
    }

    /**
     * Set offset
     * 
     * @param string $offset\Default is set to `0`
     * 
     * @return object
     */ 
    public function offset($offset = 0)
    {
        // offset
        $this->offsetCount = $offset;

        // offset query
        $this->offset = "OFFSET {$this->offsetCount}";

        // limit query check
        if( str_contains(strtoupper((string) $this->limit), "LIMIT")  ){
            $this->limit = "LIMIT {$this->offsetCount}, {$this->limitCount}";
        }else{
            $this->limit = "LIMIT {$this->offsetCount}";
        }
        
        return $this;
    }

    /**
     * Define join
     * 
     * @param string $table
     * @param string $foreignColumn
     * @param string $operator
     * @param string $localColumn
     * 
     * @return object
     */ 
    public function join($table, $foreignColumn, $operator, $localColumn)
    {
        $this->joins[] = [
            'type'          => 'INNER',
            'table'         => $table,
            'foreignColumn' => $foreignColumn,
            'operator'      => $operator,
            'localColumn'   => $localColumn
        ];

        return $this;
    }

    /**
     * Define leftJoin
     * 
     * @param string $table
     * @param string $foreignColumn
     * @param string $operator
     * @param string $localColumn
     * 
     * @return object
     */ 
    public function leftJoin($table, $foreignColumn, $operator, $localColumn)
    {
        $this->joins[] = [
            'type'          => 'LEFT',
            'table'         => $table,
            'foreignColumn' => $foreignColumn,
            'operator'      => $operator,
            'localColumn'   => $localColumn
        ];
        return $this;
    }

    /**
     * Raw Query string 
     * 
     * @param string $query
     * 
     * @return object
     */ 
    public function raw(?string $query = null)
    {
        // if query already exists
        if($this->isRawExist()){
            $this->rawQuery[] = [
                'query' => " AND $query",
            ];
        }else{
            // first query
            $this->rawQuery[] = [
                'query' => " WHERE $query",
            ];
        }

        // get into query
        $this->saveTempRawQuery($this->rawQuery);

        return $this;
    }

    /**
     * PDO where clause. Expects three params (only two mandatory)
     * By default if you provide two param (seperator becomes =) equals to. And Value becomes 2nd param
     * If you provide three values then, operator must be the middle param
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * 
     * @return object
     */ 
    public function where($column, $operator = null, $value = null)
    {
        // operator
        $temp       = $this->console::configWhereClauseOperator($operator, $value);
        $value      = $temp['value'];
        $operator   = $temp['operator'];

        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$column}{$operator}:{$column}",
                'data'  => [
                    'column'    => $column,
                    'operator'  => $operator,
                    'value'     => $value,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column}{$operator}:{$column}",
                'data'  => [
                    'column'    => $column,
                    'operator'  => $operator,
                    'value'     => $value,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO orWhere clause. Expects three params (only two mandatory)
     * By default if you provide two param (operator becomes =) equals to. And Value becomes 2nd param
     * If you provide three values then, operator must be the middle param
     * 
     * @param string $column
     * @param string $operator
     * @param string $value
     * 
     * @return object
     */ 
    public function orWhere($column, $operator = null, $value = null)
    {
        // operator
        $temp       = $this->console::configWhereClauseOperator($operator, $value);
        $value      = $temp['value'];
        $operator   = $temp['operator'];

        // or Where query add
        $this->where[] = [
            'query' => " OR {$column}{$operator}:{$column}",
            'data'  => [
                'column'    => $column,
                'operator'  => $operator,
                'value'     => $value,
            ]
        ];
        
        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Column clause. Expects three params (only one or two mandatory)
     * By default if you provide two param (operator becomes =) equals to. And Value becomes 2nd param
     * If you provide three values then, operator must be the middle param
     * 
     * @param string|array $column
     * @param string $operator
     * @param string $column2
     * 
     * @return object
     */ 
    public function whereColumn($column, $operator = null, $column2 = null)
    {
        // operator
        $temp = (array) $this->console::configWhereColumnClauseOperator($column, $operator, $column2);

        // Create a placeholder for each value in the array
        $placeholders = implode(' AND ', array_map(function($value){
            return "{$value['column1']}{$value['operator']}{$value['column2']}";
        }, $temp));

        // Adding 'Special Key to Query' as Trackable strings to remove later on
        // As this will allow us bind this data separately
        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$this->special_key} {$placeholders}",
                'data'  => [
                    'column'    => null,
                    'operator'  => null,
                    'value'     => null,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$this->special_key} {$placeholders}",
                'data'  => [
                    'column'    => null,
                    'operator'  => null,
                    'value'     => null,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where column IS NULL
     * 
     * @param string $column
     * 
     * @return object
     */ 
    public function whereNull($column)
    {
        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$column} IS NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} IS NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }
        
        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where column IS NOT NULL
     * 
     * @param string $column
     * 
     * @return object
     */ 
    public function whereNotNull($column)
    {
        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$column} IS NOT NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} IS NOT NULL",
                'data'  => [
                    'column'    => $column,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Between columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object
     */ 
    public function whereBetween($column, ?array $param = [])
    {
        // set param
        $param = $param ?? [];

        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$column} BETWEEN :{$param[0]} AND :{$param[1]}",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} BETWEEN :{$param[0]} AND :{$param[1]} ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Not Between columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object
     */ 
    public function whereNotBetween($column, ?array $param = [])
    {
        // set param
        $param = $param ?? [];

        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$column} NOT BETWEEN :{$param[0]} AND :{$param[1]}",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} NOT BETWEEN :{$param[0]} AND :{$param[1]} ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Not In columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object
     */ 
    public function whereIn($column, ?array $param = [])
    {
        // trim excess strings if any
        $param = $this->console::arrayWalkerTrim($param) ?? [];

        // Create a placeholder for each value in the array
        $placeholders = implode(', ', array_map(function($value){
            return ":$value";
        }, $param));

        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$column} IN ($placeholders)",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} IN ($placeholders) ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Where Not In columns
     * 
     * @param string $column
     * @param array $param
     * 
     * @return object
     */ 
    public function whereNotIn($column, ?array $param = [])
    {
        // trim excess strings if any
        $param = $this->console::arrayWalkerTrim($param) ?? [];

        // Create a placeholder for each value in the array
        $placeholders = implode(', ', array_map(function($value){
            return ":$value";
        }, $param));

        // if query already exists
        if($this->isWhereExist()){
            $this->where[] = [
                'query' => " AND {$column} NOT IN ($placeholders)",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }else{
            // first query
            $this->where[] = [
                'query' => " WHERE {$column} NOT IN ($placeholders) ",
                'data'  => [
                    'column'    => $column,
                    'value'     => $param,
                ]
            ];
        }

        // get into query
        $this->saveTempQuery($this->where);

        return $this;
    }

    /**
     * PDO Group By clause.
     * 
     * @param string $column
     * @return object
     */ 
    public function groupBy($column)
    {
        $this->groupBy = $column;

        // not empty
        if(!empty($this->groupBy)){
            $this->groupBy = "GROUP BY {$this->groupBy}";
        }

        return $this;
    }

    /**
     * SELECT by columns
     * @param array $columns
     * 
     * @return object
     */ 
    public function select(?array $columns = [])
    {
        $this->selectQuery = true;

        $this->selectColumns = $columns;

        return $this;
    }

    /**
     * Check if Raw or Where clause already exist
     * 
     * @return bool
     */ 
    private function isRawExist()
    {
        // position
        if(is_null($this->bt_raw_and_where)){
            $this->bt_raw_and_where = 2;
        }

        if(count($this->where) > 0 || count($this->rawQuery) > 0){
            return true;
        }

        return false;
    }

    /**
     * Check if Raw or Where clause already exist
     * 
     * @return bool
     */ 
    private function isWhereExist()
    {
        // position
        if(is_null($this->bt_raw_and_where)){
            $this->bt_raw_and_where = 1;
        }

        if(count($this->where) > 0 || count($this->rawQuery) > 0){
            return true;
        }

        return false;
    }

    /**
     * Whitelist imput from cross-site scripting (XSS) attacks
     * 
     * @param string $input
     * 
     * @return string
     */ 
    public function whitelistInput(mixed $input) 
    {
        if($this->removeTags){
            if (is_array($input)) {
                return array_map(array($this, 'whitelistInput'), $input)[0] ?? '';
            }
            
            // Convert input to string
            $filteredInput = (string) $input;

            // Remove any script or style tags and their contents
            $filteredInput = preg_replace('/<(script|style)[^>]*?>.*?<\/\\1>/si', '', $filteredInput);
            
            // Allow only letters, digits, spaces, and common punctuation marks
            $filteredInput = preg_replace('/[^\w\s.,!?():;\'"`-]/u', '', $filteredInput);
            
            // Remove any extra whitespace
            $filteredInput = trim(preg_replace('/\s+/u', ' ', $filteredInput));
            
            return $filteredInput;
        }
        
        return $input;
    }
    
}
