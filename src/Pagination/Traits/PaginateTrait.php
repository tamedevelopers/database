<?php

declare(strict_types=1);

namespace builder\Database\Pagination\Traits;

use yidas\data\Pagination;
use builder\Database\Capsule\Manager;
use builder\Database\Pagination\OrmPagination;

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
     * pagination text
     * @param string $type
     * 
     * @return string
     */
    private function text(?string $type = null)
    {
        return Manager::$pagination_text[$type] ?? '';
    }

    /**
     * Get Pagination data
     * @param array $options
     *
     * @return object
     */
    public function configurePagination(?array $options = [])
    {
        // trying to us global AutoloadEnv::configurePagination data
        if(defined('PAGINATION_CONFIG') && is_bool(PAGINATION_CONFIG['allow']) && PAGINATION_CONFIG['allow'] === true){
            $this->pagination_settings = PAGINATION_CONFIG;
        }else{
            // create a default data
            $this->pagination_settings = [
                'allow'     => $options['allow']    ?? false,
                'class'     => $options['class']    ?? null,
                'view'      => in_array($options['view'] ?? null, $this->pagination_view) ? $options['view'] : $this->text('view'),
                'first'     => $options['first']    ?? $this->text('first'),
                'last'      => $options['last']     ?? $this->text('last'),
                'next'      => $options['next']     ?? $this->text('next'),
                'prev'      => $options['prev']     ?? $this->text('prev'),
                'span'      => $options['span']     ?? $this->text('span'),
                'showing'   => $options['showing']  ?? $this->text('showing'),
                'to'        => $options['to']       ?? $this->text('to'),
                'of'        => $options['of']       ?? $this->text('of'),
                'results'   => $options['results']  ?? $this->text('results'),
            ];
        }

        // helps to use one simple settings for all pagination within applicaiton life circle
        $this->use_global = $this->pagination_settings['allow'];

        // if bootstrap view
        if(strtolower($this->pagination_settings['view']) == $this->pagination_view['bootstrap']){
            $this->pagination_css = OrmPagination::getBootstrapCss();
        }else{
            $this->pagination_css = OrmPagination::getSimpleCss();
        }

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
        $settings = $this->getSettings($options);

        echo \yidas\widgets\Pagination::widget([
            'pagination'        => $this->pagination,
            'ulCssClass'        => $settings['class'],
            'view'              => $settings['view'],
            'firstPageLabel'    => $settings['first'],
            'lastPageLabel'     => $settings['last'],
            'nextPageLabel'     => $settings['next'],
            'prevPageLabel'     => $settings['prev']
        ]) . "{$this->pagination_css}";
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
        $settings = $this->getSettings($options);

        // get showing data
        $data = $this->getShowingData();
        
        // only display full text formatting when total count is more than 0
        if($data['total'] > 0){
            // if total is greater than or equal to limit
            if($data['total'] >= $this->pagination->limit){
                $formatDisplayText = "
                    {$settings['showing']} 
                    {$data['showing']} 

                    {$settings['to']} 
                    {$data['to']}

                    {$settings['of']} 

                    {$data['total']}
                    {$settings['results']}
                ";
            }else{
                $formatDisplayText = "
                    {$settings['showing']} 
                    {$data['showing']} 

                    {$settings['to']} 
                    {$data['to']}
                    
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
     * Get settings
     * @param array $options
     *
     * @return mixed
     */
    private function getSettings(?array $options = [])
    {
        // pagination configurations
        if(count($this->pagination_settings) === 0){
            $settings = $this->configurePagination($options)->pagination_settings;
        }else{
            // If global is not allowed
            if($this->use_global){
                $settings = $this->pagination_settings;
            }else{
                $settings = $this->configurePagination($options)->pagination_settings;
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
        $to         = $this->pagination->limit;
        $offset     = $this->pagination->offset;
        $limit      = $this->pagination->limit;
        $total      = $this->pagination->totalCount;
        $showing    = 0;

        // calculate data if not first page
        if($this->pagination->page !=  1){
            $to         = ($this->pagination->page * $limit);
            $showing    = $offset;
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
            unset($options['to']);
            unset($options['span']);
            unset($options['results']);
            unset($options['showing']);
        }else{
            // unset disallowed keys
            $options = [
                'of'        => $options['of']       ?? $this->text('of'),
                'to'        => $options['to']       ?? $this->text('to'),
                'span'      => $options['span']     ?? $this->text('span'),
                'results'   => $options['results']  ?? $this->text('results'),
                'showing'   => $options['showing']  ?? $this->text('showing'),
            ];
        }
        return $options;
    }

    /**
     * Get Pagination data
     * @param int|float $per_page
     *
     * @return mixed
     */
    protected function getPagination($per_page = 10)
    {
        try {
            // Initialize a Data Pagination with previous count number
            $this->pagination = new Pagination([
                'totalCount'    => $this->count(false),
                'perPage'       => $per_page
            ]);

            // auto set the Per Page 
            // With this we override the perPage from the browser
            // and collect that from the var above\$per_page
            $this->pagination->setPerPage($per_page);

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