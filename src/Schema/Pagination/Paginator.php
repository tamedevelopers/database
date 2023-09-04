<?php

declare(strict_types=1);

/*
 * This file is part of ultimate-orm-database.
 *
 * (c) Tame Developers Inc.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace builder\Database\Schema\Pagination;
    
use builder\Database\Capsule\Str;
use builder\Database\Schema\Builder;
use builder\Database\Schema\Pagination\PaginatorAsset;
use builder\Database\Schema\Pagination\Yidas\PaginationLoader;
use builder\Database\Schema\Pagination\Yidas\PaginationWidget;


class Paginator extends Builder{
    
    /**
     * pagination data
     */
    public $pagination;

    /**
     * global config traction
     * @var bool
     */
    private $use_global;

    /**
     * pagination settings
     * @var array
     */
    public $pagination_settings = [];

    /**
     * pagination style
     * @var string
     */
    private $pagination_css;

    /**
     * Name of the parameter storing the current page index
     *
     * @var string
     */
    public $pageParam = 'page';

    /**
     * Name of the parameter storing the page size
     *
     * @var string|boolean `false` to turn off per-page input by client
     */
    public $perPageParam = 'per-page';

    /**
     * pagination asset class
     * @var \builder\Database\Schema\Pagination\PaginatorAsset
     */
    private $asset;


    /**
     * Paginator constructor
     * @param  string  $pageParam
     * @param  string|bool  $perPageParam
     * 
     */
    public function __construct($pageParam = 'page', $perPageParam = false)
    {
        $this->asset = new PaginatorAsset();
        $this->pageParam = $pageParam;
        $this->perPageParam = $perPageParam;
    }

    /**
     * Get Pagination data
     * @param array $options
     *
     * @return $this
     */
    public function configPagination(?array $options = [])
    {
        // trying to us global AutoLoader::configPagination data
        if(defined('PAGINATION_CONFIG') && is_bool(PAGINATION_CONFIG['allow']) && PAGINATION_CONFIG['allow'] === true){
            $this->pagination_settings = PAGINATION_CONFIG;
        }else{
            // create a default data
            $this->pagination_settings = array_merge([
                'allow'     => false,
                'class'     => null,
                'view'      => null,
                'first'     => $this->asset->texts('first'),
                'last'      => $this->asset->texts('last'),
                'next'      => $this->asset->texts('next'),
                'prev'      => $this->asset->texts('prev'),
                'span'      => $this->asset->texts('span'),
                'showing'   => $this->asset->texts('showing'),
                'of'        => $this->asset->texts('of'),
                'results'   => $this->asset->texts('results'),
                'buttons'   => $this->asset->texts('buttons'),
            ], $options);

            // get actual view
            $this->pagination_settings['view'] = in_array($this->pagination_settings['view'], $this->asset->views()) 
                                                ? $options['view'] 
                                                : $this->asset->texts('view');
        }

        // helps to use one simple settings for all pagination within applicaiton life circle
        $this->use_global = $this->pagination_settings['allow'];

        return $this;
    }

    /**
     * Get Pagination Links
     * @param array $options
     *
     * @return string
     */
    public function links(?array $options = [])
    {
        // reset disallowed
        $options = $this->resetKeys($options);

        // get pagination settings
        $settings = array_merge($this->getSettings($options), $options);

        // pagination views
        $this->paginationViews($settings);

        // pagination css get style
        $getStyle = $this->asset->getStyles($this->pagination_css);

        // instance of Yidas Widgets 
        $yidasWidgets = new PaginationWidget;

        echo $yidasWidgets->widget([
            'pagination'        => $this->pagination,
            'ulCssClass'        => $settings['class'],
            'view'              => $settings['view'],
            'firstPageLabel'    => $settings['first'],
            'lastPageLabel'     => $settings['last'],
            'nextPageLabel'     => $settings['next'],
            'prevPageLabel'     => $settings['prev'],
            'buttonCount'       => $settings['buttons'],
        ]) . "{$getStyle}";
    }

    /**
     * Format Pagination Data
     * @param array $options
     * 
     * @return string
     */
    public function showing(?array $options = [])
    {
        // reset disallowed
        $options = $this->resetKeys($options, false);

        // get pagination settings
        $settings = array_merge($this->getSettings($options), $options);

        // get showing data
        $data = $this->getShowingData();

        // convert to lowercase
        $settings['of'] =  mb_strtolower($settings['of'], 'UTF-8');
        
        // only display full text formatting when total count is more than 0
        if($data['total'] > 0){
            // if total is greater than or equal to limit
            if($data['total'] >= $this->pagination->limit){
                $formatDisplayText = "
                    {$settings['showing']} 
                    {$data['showing']}-{$data['to']}

                    {$settings['of']} 

                    {$data['total']}
                    {$settings['results']}
                ";
            }else{
                $formatDisplayText = "
                    {$settings['showing']} 
                    {$data['showing']}-{$data['to']}

                    {$settings['results']}
                ";
            }
        } else{
            $formatDisplayText = "
                {$settings['showing']} 
                {$data['total']}
                {$settings['results']}
            ";
        }

        // only display results when total count is more than 0
        echo "<span class='{$settings['span']}' style='display: inline-block; text-align: center; padding: 0;'>
                {$formatDisplayText} 
            </span>
        ";
    }

    /**
     * Get pagination view
     * @param array $options
     *
     * @return void
     */
    private function paginationViews(?array $options = [])
    {
        // if bootstrap view
        if(Str::lower($options['view']) == $this->asset->views('bootstrap')){
            $this->pagination_css = $this->asset->views('bootstrap');
        } elseif(Str::lower($options['view']) == $this->asset->views('simple')){
            $this->pagination_css = $this->asset->views('simple');
        } else{
            $this->pagination_css = $this->asset->views('cursor');
        }
    }

    /**
     * Get settings
     * @param array $options
     *
     * @return array
     */
    private function getSettings(?array $options = [])
    {
        // pagination configurations
        if(empty($this->pagination_settings)){
            $settings = $this->configPagination($options)->pagination_settings;
        }else{
            // If global is allowed
            if($this->use_global){
                $settings = $this->pagination_settings;
            }else{
                $settings = $this->configPagination($options)->pagination_settings;
            }
        }

        return $settings;
    }

    /**
     * Format and Get Showing data
     * @return mixed
     */
    private function getShowingData()
    {
        $to         = (int) $this->pagination->limit;
        $offset     = (int) $this->pagination->offset;
        $limit      = (int) $this->pagination->limit;
        $total      = (int) $this->pagination->totalCount;
        $showing    = 0;

        // calculate showing if data is greater than 1
        if($total > 1){
            $showing = 1;
        }

        // calculate data if not first page
        if($this->pagination->page !=  1){
            $to         = ($this->pagination->page * $limit);
            $showing    = $offset + 1;
        }

        // calculate data for last page
        if($to >= $total){
            $to = $total; 
        }

        return [
            'showing'   => $showing,
            'to'        => $to,
            'total'     => $total,
        ];
    }

    /**
     * Reset Options Keys 
     * @param array $options
     * @param bool $links
     *
     * @return mixed
     */
    private function resetKeys(?array $options = [], $links = true)
    {
        unset($options['allow']);
        if($links){
            // unset disallowed keys
            unset($options['of']);
            unset($options['span']);
            unset($options['results']);
            unset($options['showing']);
        }else{
            // only needed key data
            $options = array_merge([
                'of'        => $this->asset->texts('of'),
                'span'      => $this->asset->texts('span'),
                'results'   => $this->asset->texts('results'),
                'showing'   => $this->asset->texts('showing'),
            ], $options);

        }
        return $options;
    }

    /**
     * Get Pagination data
     * @param int $totalCount
     * @param int|string $perPage
     * @param \builder\Database\Schema\Builder  $query
     * 
     * @return $this
     */
    protected function getPagination($totalCount, int|string $perPage = 15, Builder $query = null)
    {
        try {
            // convert to int
            $perPage = (int) $perPage;

            // reset headers
            $this->asset->headerControlNoCache();

            // Initialize a Data Pagination with previous count number
            $this->pagination = new PaginationLoader([
                'totalCount'    => $totalCount,
                'perPage'       => $perPage,
            ]);

            // set perPageParam 
            // Default is to Turn off the per-page from url
            $this->pagination->perPageParam = $this->perPageParam;

            // set pageParam
            // pagination pages numbers
            $this->pagination->pageParam = $this->pageParam;

            // auto set the Per Page 
            // With this we override the perPage from the browser
            // and collect that from the var above\$perPage
            $this->pagination->setPerPage($perPage);

            // auto setup pageParam
            $pageParam = $_GET[$this->pageParam] ?? $this->pagination->page;

            // To avoi conflicts on multiple pagination
            // in same page. We autoset from $_GET request
            $this->pagination->setPage($pageParam);

            // create additional pagination links
            $this->pagination->params = [
                'cache-buster' => time()
            ];
            
            // builde query to get the correct data for pagination
            $query = $this->buildPaginatorQuery($query);

            $query->applyBeforeQueryCallbacks();

            // result data
            $results = $query->paginateStatement($query, $this);

            // adding method used
            $this->method = $query->method;

            // only close when allowed
            $query->close();
            
            return [
                'data'      => $results,
                'builder'   => $this,
            ];
        } catch (\PDOException $e) {
            $this->errorException($e);
        }
    }

    /**
     * Build paginator query
     *
     * @param  \builder\Database\Schema\Builder  $query
     * @return $this
     */
    private function buildPaginatorQuery(Builder $query)
    {
        foreach($query->columns as $key => $column){
            if($query->isExpression($column)){
                // in other for us to be able to fetch correct data
                // we first need to remove the query builder for counting
                // since we have not yet closed the first query before pagination
                if(strpos($column->getValue(), 'count(*) as aggregate') !== false){
                    unset($query->columns[$key]);
                }
            } 
        }

        return $query;
    }

}