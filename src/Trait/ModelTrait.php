<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Trait;


trait ModelTrait{
    
    protected   $getQuery;
    protected   $paramValues = [];

    /**
     * Get Database connection status
     * 
     * @return object|array\getConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get Application Config Settings
     * 
     * @return string|array\AppConfig
     */
    public function AppConfig()
    {
        return $this->getConfig('all');
    }

    /**
     * Get last Database query sample
     * 
     * @return object|array|void\getQuery
     */
    public function getQuery()
    {
        return is_null($this->getQuery) 
                ? (object) $this->setQueryProperty()
                : (object) $this->getQuery;
    }
    
    /**
     * Close all query and get results
     * 
     * @return bool|array|object|int|void\getQueryResult
     */
    protected function getQueryResult( $data )
    {
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
     * @return object|array\setQueryProperty
     */
    protected function setQueryProperty()
    {
        // save to temp queri data
        $this->getQuery = [
            'stmt'          => $this->stmt,
            // 'query'         => $this->query,
            'where'         => $this->where,
            'groupBy'       => $this->groupBy,
            'joins'         => $this->joins,
            'selectColumns' => $this->selectColumns,
            'paramValues'   => $this->paramValues,
        ];
        
        return $this->getQuery;
    } 

    /**
     * Set header response code
     * Mainly for firstOrFail()
     * 
     * @return void|null\setHeaders
     */
    protected function setHeaders()
    {
        // Set HTTP response status code to 404
        http_response_code(404);

        // Flush output buffer
        flush();

        // Exit with response 404
        exit();
    }

}