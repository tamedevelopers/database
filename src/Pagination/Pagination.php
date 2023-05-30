<?php

declare(strict_types=1);

namespace builder\Database\Pagination;
    
use builder\Database\Capsule\Manager;
use builder\Database\Pagination\Yidas\PaginationLoader;
use builder\Database\Pagination\Yidas\PaginationWidget;

trait Pagination{
    
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
     * Get Pagination data
     * @param array $options
     *
     * @return object
     */
    public function configPagination(?array $options = [])
    {
        // getViews
        $getViews = Manager::$pagination_views;

        // trying to us global EnvAutoLoad::configPagination data
        if(defined('PAGINATION_CONFIG') && is_bool(PAGINATION_CONFIG['allow']) && PAGINATION_CONFIG['allow'] === true){
            $this->pagination_settings = PAGINATION_CONFIG;
        }else{
            // create a default data
            $this->pagination_settings = array_merge([
                'allow'     => false,
                'class'     => null,
                'view'      => null,
                'first'     => $this->text('first'),
                'last'      => $this->text('last'),
                'next'      => $this->text('next'),
                'prev'      => $this->text('prev'),
                'span'      => $this->text('span'),
                'showing'   => $this->text('showing'),
                'of'        => $this->text('of'),
                'results'   => $this->text('results'),
            ], $options);

            // get actual view
            $this->pagination_settings['view'] = in_array($this->pagination_settings['view'], $getViews) 
                                                ? $options['view'] 
                                                : $this->text('view');
        }

        // helps to use one simple settings for all pagination within applicaiton life circle
        $this->use_global = $this->pagination_settings['allow'];

        // if bootstrap view
        if(strtolower($this->pagination_settings['view']) == $getViews['bootstrap']){
            $this->pagination_css = $getViews['bootstrap'];
        }else{
            $this->pagination_css = $getViews['simple'];
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

        // pagination css get style
        $getStyle = $this->getStyles($this->pagination_css);

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
        $settings = $this->getSettings($options);

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
     * Get settings
     * @param array $options
     *
     * @return mixed
     */
    private function getSettings(?array $options = [])
    {
        // pagination configurations
        if(count($this->pagination_settings) === 0){
            $settings = $this->configPagination($options)->pagination_settings;
        }else{
            // If global is not allowed
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
                'of'        => $this->text('of'),
                'span'      => $this->text('span'),
                'results'   => $this->text('results'),
                'showing'   => $this->text('showing'),
            ], $options);

        }
        return $options;
    }

    /**
     * Get Pagination data
     * @param int|string $per_page
     *
     * @return mixed
     */
    protected function getPagination(int|string $per_page = 10)
    {
        try {
            // convert to int
            $per_page = (int) $per_page;

            // reset headers
            $this->headerControlNoCache();

            // get Counted Data
            $totalCount = $this->count(false);

            // Initialize a Data Pagination with previous count number
            $this->pagination = new PaginationLoader([
                'totalCount'    => $totalCount,
                'perPage'       => $per_page,
            ]);

            // auto set the Per Page 
            // With this we override the perPage from the browser
            // and collect that from the var above\$per_page
            $this->pagination->setPerPage($per_page);
            
            // Turn off the per-page number change
            $this->pagination->perPageParam = false;

            // create additional pagination links
            $this->pagination->params = [
                'cache-buster' => time()
            ];

            // query pagination
            $this->allowPaginate()
                    ->limit($this->pagination->limit)
                    ->offset($this->pagination->offset)
                    ->compileQuery()
                    ->execute();
            
            // get results while closing connection
            return $this->getDataAndCloseConnection( 
                $this->fetchAll()
            );
        } catch (\PDOException $e) {
            return $this->errorTemp($e);
        }
    }

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
     * Customize browser header
     *
     * @return void
     */
    private function headerControlNoCache()
    {
        @header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        @header("Pragma: no-cache");
        @header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
    }

    /**
     * Pagination style
     * @param string $mode
     * 
     * @return array
     */
    private function getStyles(?string $mode = 'simple')
    {
        if(!defined('STYLE_EXISTS')){
            // Helps to define not getting style more than once per page
            define('STYLE_EXISTS', true);

            if($mode == Manager::$pagination_views['simple']){
                return $this->getSimpleCss();
            }
            return $this->getBootstrapCss();
        }
    }

    /**
     * Compresses CSS by removing comments, compressing spaces, and trimming the resulting string.
     *
     * @param string $s The CSS string to compress
     * 
     * @return string 
     * - The compressed CSS string
     */
    private function compressCss(string $string)
    {
        // Step 1: Remove CSS comments (/* ... */)
        $string = preg_replace('#/\*.*?\*/#s', '', $string);

        // Step 2: Compress consecutive whitespace characters into a single space
        $string = preg_replace('#[ \t\r\n]+#', ' ', $string);

        // Step 3: Remove spaces before certain characters to ensure proper CSS syntax
        $string = preg_replace('# ([^0-9a-z.\#*-])#i', '$1', $string);

        // Step 4: Remove spaces after certain characters to ensure proper CSS syntax
        $string = preg_replace('#([^0-9a-z%]) #i', '$1', $string);

        // Step 5: Remove leading semicolon after a closing bracket
        $string = str_replace(';}', '}', $string);

        // Trim leading and trailing whitespace
        return trim($string);
    }

    /**
     * Return boostrap pagination css style
     *
     * @return string
     */
    private function getBootstrapCss()
    {
        return $this->compressCss("<style>
            .pagination{text-align:center;margin-top:20px}.pagination .page-item.active .page-link,.pagination .page-item.active .page-link:hover{background-color:#1098ad}.pagination .page-item{margin:0 1px}.pagination .page-link{border:0;height:40px;min-width:40px;text-align:center;padding:10px;font-weight:600;color:#212121;border-radius:2em;background:0 0;box-shadow:none}.pagination .page-item.disabled .page-link{background:0 0;color:#a6a6a6}.pagination .page-item:not(.disabled) .page-link:hover{background-color:#fff}.pagination .page-item.active .page-link,.pagination .page-item.active .page-link:hover{background-color:#1098ad}.pagination .page-item:first-child .page-link,.pagination .page-item:last-child .page-link{border-radius:2em}
        </style>");
    }

    /**
     * Return boostrap pagination css style
     *
     * @return string
     */
    private function getSimpleCss()
    {
        return $this->compressCss("<style>
            .pagination{text-align: center;margin-top: 20px;display: block;}.pagination * {margin: 0 5px 0 0;}.pagination span {background: 0 0;color: #a6a6a6;}.pagination span:not(.disabled) a[href]:hover {background-color: #fff;}
            .pagination a[href] {display: inline-block;background-color: rgba(0, 0, 0, 0);background: 0 0;text-align: center;color: #212121;font-weight: 600;padding: 5px 10px;border: 0;box-shadow: none;}.pagination a[href].active,.pagination a[href].active:hover {background-color: #d0e2ff;}
        </style>");
    }

}