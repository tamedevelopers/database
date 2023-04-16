<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Schema;

use Throwable;
use PDOException;
use UltimateOrmDatabase\Schema\ModelQuery;
use UltimateOrmDatabase\Pagination\Trait\PaginateTrait;


abstract class Model extends ModelQuery{

    use PaginateTrait;

    /**
     * Constructor
     * @param array $options\Database options settings
     * 
     * @return void
     */
	public function __construct(?array $options = [])
    {
        // init configuration
        $this->initConfiguration($options);
        
        // start db
        $this->startDatabase();
	}

    /**
     * Check if table exist
     * 
     * @param string $table_name\Table name
     * 
     * @return array|void\tableExist
     */
    public function tableExist(?string $table_name = null)
    {
        try{
            $this->raw("SELECT 1 FROM `{$table_name}` LIMIT 1")
                        ->compileQuery()
                        ->execute();

            return [
                'status'    => self::ERROR_200, 
                'message'   => "Table name `{$table_name}` exist."
            ];
        }catch (PDOException $e){
            return [
                'status'    => self::ERROR_404, 
                'message'   => "Table name `{$table_name}` doesn't exist.",
                'query'     => $e->getMessage(),
            ];
        }
    }

    /**
     * Convert data to array
     * 
     * @param array|object|null $data
     * @return array\toArray
     */ 
    public function toArray($data = [])
    {
        return json_decode( json_encode($data), TRUE);
    }
    
    /**
     * Convert data to object
     * 
     * @param array|object|null $data
     * @return array|object\toObject
     */ 
    public function toObject($data = [])
    {
        return json_decode( json_encode($data), FALSE);
    }

    /**
     * Get result data as an arrays of objects
     *
     * @return object|array\get
     */
    public function get()
    {
        try {
            // query builder
            $this->compileQuery()->execute();

            return $this->getQueryResult( $this->tryFetchAll() );
        } catch (\Throwable $th) {
            $this->dump_final = false;
            $this->dump( $this->errorTemp($th)['message'] );
        }
    }

    /**
     * Get first query
     *
     * @return boolean|object\first
     */
    public function first()
    {
        try {
            $this->limit(1)->compileQuery()->execute();

            return $this->getQueryResult( $this->tryFetchAll()[0] ?? false );
        } catch (\Throwable $th) {
            $this->dump_final = false;
            $this->dump( $this->errorTemp($th)['message'] );
        }
    }

    /**
     * Get first query or abort with response code
     *
     * @return boolean|array|object|null|void\firstOrFail
     */
    public function firstOrFail()
    {
        try {
            // query builder
            $this->limit(1)->compileQuery()->execute();
            $stmt = $this->tryFetchAll()[0] ?? $this->setHeaders();

            return $stmt;
        } catch (\Throwable $th) {
            $this->setHeaders();
        }
    }

    /**
     * Get result data as an arrays of objects
     * @param int $per_page
     *
     * @return object|array\resultObject
     */
    public function paginate($per_page = 10)
    {
        return (object) $this->getPagination($per_page);
    }

    /**
     * Get Pagination Links
     * @param array $options
     *
     * @return object|array\links
     */
    public function links(?array $options = [])
    {
        // If global is not allowed, 
        // Then use each settings for each view
        if(!$this->use_global){
            $this->pagination_settings = [
                'allow' => $options['allow'] ?? false,
                'class' => $options['class'] ?? null,
                'view'  => in_array($options['view'] ?? null, $this->pagination_view) ? $options['view'] : 'bootstrap',
                'first' => $options['first'] ?? 'First',
                'last'  => $options['last'] ?? 'Last',
                'next'  => $options['next'] ?? 'Next',
                'prev'  => $options['prev'] ?? 'Prev',
            ];
        }

        echo \yidas\widgets\Pagination::widget([
            'pagination'        => $this->pagination,
            'ulCssClass'        => $this->pagination_settings['class'],
            'view'              => $this->pagination_settings['view'],
            'firstPageLabel'    => $this->pagination_settings['first'],
            'lastPageLabel'     => $this->pagination_settings['last'],
            'nextPageLabel'     => $this->pagination_settings['next'],
            'prevPageLabel'     => $this->pagination_settings['prev']
        ]) . "{$this->pagination_css}";
    }

    /**
     * Count results
     *
     * @return int\count
     */
    public function count()
    {
        try {
            // convert query
            $this->allowCount()->compileQuery()->execute();
            $stmt = $this->getQueryResult( $this->tryFetchAll(false) );

            // count data
            $count = count($stmt);

            $this->close();
            return  $count > self::ONE
                    ? $count 
                    : $stmt[0]['count(*)'] 
                    ?? $count
                    ?? 0;
        } catch (Throwable $th) {
            $this->dump_final = false;
            $this->dump( $this->errorTemp($th)['message'] );
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