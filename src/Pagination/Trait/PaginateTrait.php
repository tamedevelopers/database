<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Pagination\Trait;

use Throwable;
use yidas\data\Pagination;
use UltimateOrmDatabase\Pagination\OrmPagination;

trait PaginateTrait{
    
    /**
     * pagination data
     */
    public $pagination;

    /**
     * global config traction
     * @var bool
     */
    public $use_global;

    /**
     * pagination style
     * @var string
     */
    public $pagination_css;

    /**
     * pagination settings
     * @var array
     */
    public $pagination_settings = [];
    
    /**
     * pagination view
     * @var array
     */
    public $pagination_view = [
        'bootstrap' => 'bootstrap',
        'simple'    => 'simple',
    ];

    /**
     * Get Pagination data
     * @param array $options
     *
     * @return object|array\configurePagination
     */
    public function configurePagination(?array $options = [])
    {
        // create a default data
        $this->pagination_settings = [
            'allow' => $options['allow'] ?? false,
            'class' => $options['class'] ?? null,
            'view'  => in_array($options['view'] ?? null, $this->pagination_view) ? $options['view'] : 'bootstrap',
            'first' => $options['first'] ?? 'First',
            'last'  => $options['last'] ?? 'Last',
            'next'  => $options['next'] ?? 'Next',
            'prev'  => $options['prev'] ?? 'Prev',
        ];

        // helps to use one simple settings for all pagination within applicaiton life circle
        $this->use_global = $this->pagination_settings['allow'];

        // if bootstrap view
        if(strtolower($this->pagination_settings['view']) == 'bootstrap'){
            $this->pagination_css = OrmPagination::getBootstrapCss();
        }else{
            $this->pagination_css = OrmPagination::getSimpleCss();
        }

        return $this;
    }

    /**
     * Get Pagination data
     * @param int|float $per_page
     *
     * @return object|array\getPagination
     */
    public function getPagination($per_page = 10)
    {
        try {
            // Initialize a Data Pagination with previous count number
            $this->pagination = new Pagination([
                'totalCount'    => $this->count(),
                'perPage'       => $per_page
            ]);

            // query pagination
            $this->allowPaginate()
                    ->limit($this->pagination->limit)
                    ->offset($this->pagination->offset)
                    ->compileQuery()
                    ->execute();
            
            // get data
            $stmt = $this->tryFetchAll();

            $this->close();
            
            return [
                'data'          => $stmt,
                'pagination'    => $this,
            ];
        } catch (\Throwable $th) {
            $this->dump( $this->errorTemp($th)['message'] );
        }
    }

}