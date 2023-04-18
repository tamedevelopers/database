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
            'allow'     => $options['allow']    ?? false,
            'class'     => $options['class']    ?? null,
            'view'      => in_array($options['view'] ?? null, $this->pagination_view) ? $options['view'] : 'bootstrap',
            'first'     => $options['first']    ?? 'First',
            'last'      => $options['last']     ?? 'Last',
            'next'      => $options['next']     ?? 'Next',
            'prev'      => $options['prev']     ?? 'Prev',
            'span'      => $options['span']     ?? 'pagination-highlight',
            'showing'   => $options['showing']  ?? 'Showing',
            'to'        => $options['to']       ?? 'to',
            'of'        => $options['of']       ?? 'of',
            'results'   => $options['results']  ?? 'results',
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
     * Get Pagination Links
     * @param array $options
     *
     * @return object|array\links
     */
    public function links(?array $options = [])
    {
        // reset disallowed
        $options = $this->resetKeys($options, true);

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
     * @return object|array\showing
     */
    public function showing(?array $options = [])
    {
        // reset disallowed
        $options = $this->resetKeys($options, false);

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

        // get showing data
        $data = $this->getShowingData();

        // only display results when total count is more than 0
        if($data['total'] > 0){
            return "<div style='text-align: center;'>
                        <span class='{$settings['span']}'>
                            {$settings['showing']} 
                            {$data['showing']} 
    
                            {$settings['to']} 
                            {$data['to']}
    
                            {$settings['of']} 
    
                            {$data['total']}
                            {$settings['results']}
                        </span>
                    </div>
            ";
        }
    }

    /**
     * Format and Get Showing data
     * @return array|null\getShowingData
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
     * @return array|null\resetKeys
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
                'of'        => $options['of']       ?? 'of',
                'to'        => $options['to']       ?? 'to',
                'span'      => $options['span']     ?? 'pagination-highlight',
                'results'   => $options['results']  ?? 'results',
                'showing'   => $options['showing']  ?? 'Showing',
            ];
        }
        return $options;
    }

    /**
     * Get Pagination data
     * @param int|float $per_page
     *
     * @return object|array\getPagination
     */
    protected function getPagination($per_page = 10)
    {
        try {
            // Initialize a Data Pagination with previous count number
            $this->pagination = new Pagination([
                'totalCount'    => $this->count(),
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