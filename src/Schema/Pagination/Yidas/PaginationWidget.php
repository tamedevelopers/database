<?php

namespace builder\Database\Schema\Pagination\Yidas;

/**
 * Pagination Widget
 * 
 * @author  Nick Tsai <myintaer@gmail.com>
 * 
 * @link https://github.com/yidas
 * @link https://github.com/yidas/php-pagination
 * 
 * @since   1.0.6
 */
class PaginationWidget
{
    /**
     * Maximum numbers of page button software can generate
     *
     * @var int
     */    
    const MAX_PAGE_BUTTON = 20;

    /**
     * Set the Widget pager is center align or not
     *
     * @var boolean
     */    
    public $alignCenter = true;
    
    /**
     * Maximum number of page buttons that can be displayed
     *
     * @var integer
     */    
    public $buttonCount = 5;

    /**
     * The data pagination object that this pager is associated with.
     *
     * @var \builder\Database\Schema\Pagination\Yidas\PaginationLoader
     */
    public $pagination;

    /**
     * The text label for the "first" page button
     *
     * @var string null for disabling
     */
    public $firstPageLabel = 'First';

    /**
     * The text label for the "last" page button
     *
     * @var string null for disabling
     */
    public $lastPageLabel = 'Last';

    /**
     * The text label for the "next" page button
     *
     * @var string
     */
    public $nextPageLabel = 'Next';

    /**
     * The text label for the "previous" page button
     *
     * @var string
     */
    public $prevPageLabel = 'Prev';
    
    /**
     * The CSS class for the "first" page button
     *
     * @var string
     */
    public $firstPageCssClass = '';

    /**
     * The CSS class for the "last" page button
     *
     * @var string
     */
    public $lastPageCssClass = '';

    /**
     * The CSS class for the "next" page button
     *
     * @var string
     */
    public $nextPageCssClass = '';

    /**
     * The CSS class for the "previous" page button
     *
     * @var string
     */
    public $prevPageCssClass = '';

    /**
     * The CSS class for the "previous" page button
     *
     * @var string
     */
    public $pageCssClass = 'page-item';

    /**
     * HTML attributes for the link in a pager container tag
     *
     * @var array ['{attribute}' => '{value}']
     */
    public $linkAttributes = [];

    /**
     * The CSS class for the ul element of pagination
     * 
     * For example, 'pagination-sm' for Bootstrap small size.
     *
     * @var string
     */
    public $ulCssClass = '';

    /**
     * The view name or absolute file path that can be used to render view.
     * 
     * Template: 'bootstrap', 'simple'
     *
     * @var string view name or absolute file path
     */
    public $view = 'bootstrap';
    
    /**
     * Default options
     *
     * @param array
     */
    protected static $defaultOpt = [];

    /**
     * Button data stack for display
     *
     * @var array
     */
    protected $_buttonStack;

    /**
     * Widget object instance
     * 
     * @var object
     */
    protected static $_instance;

    /**
     * Widget 
     * @param array $options
     * 
     * @return mixed
     */
    public static function widget($options = [])
    {
        // Create an instance for each call
        $widgetClass = get_called_class();
        self::$_instance = new $widgetClass;

        $options = array_merge(self::$defaultOpt, $options);


        // Configuration for each call
        foreach ($options as $property => $value) {
            // Limit the number of page buttons that can be displayed
            if($property === 'buttonCount'){
                if($value > self::MAX_PAGE_BUTTON){
                    $value = self::MAX_PAGE_BUTTON;
                }
            }
            self::$_instance->$property = $value;
        }

        // change button count for cursor view type
        if(self::$_instance->view === 'cursor'){
            self::$_instance->buttonCount = 0;
        }
        
        return self::$_instance->run();
    }

    /**
     * Executes the widget.
     *
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        $page = $this->pagination->page;
        
        if ($this->pagination->pageCount >= $this->buttonCount) {

            $half = floor($this->buttonCount / 2);

            // Current page is in the middle of button stack
            if ($page > $half && $page < ($this->pagination->pageCount - $half)) {
                for ($i=$half; $i > 0; $i--) { 
                    
                    $this->_buttonStack[] = $page - $i;
                }
                $this->_buttonStack[] = $page;
                for ($i=1; $i <= $half; $i++) { 
                    
                  $this->_buttonStack[] = $page + $i;
                }
            } 
            // Current page is in the left of button stack
            elseif ($page <= $half) {
              
                $diff = $half - ($page-1);
                for ($i=$half-$diff; $i > 0; $i--) { 
                    
                    $this->_buttonStack[] = $page - $i;
                }
                $this->_buttonStack[] = $page;
                // Padding diff buttons
                for ($i=1; $i <= $half+$diff; $i++) { 
                    
                  $this->_buttonStack[] = $page + $i;
                }
            }
            // Current page is in the right of button stack
            else {

                $diff = $this->pagination->pageCount - $page;
                // Padding diff buttons
                for ($i=$half+($half-$diff); $i > 0; $i--) { 
                    
                    $this->_buttonStack[] = $page - $i;
                }
                $this->_buttonStack[] = $page;
                for ($i=1; $i <= $diff; $i++) { 
                    
                    $this->_buttonStack[] = $page + $i;
                }
            }
        }
        elseif ($this->pagination->pageCount < 1) {

            // Empty
            $this->_buttonStack[] = 1;
        }
        else {

            // Under count number
            for ($i=1; $i <= $this->pagination->pageCount; $i++) { 
                
                $this->_buttonStack[] = $i;
            }
        }

        // Link Attributes to html string
        $linkAttributes = '';
        foreach ($this->linkAttributes as $key => $value) {
            $linkAttributes .= "{$key}=\"{$value}\" ";
        }

        $isFirst = ($page<=1) ? true : false;
        $isLast = ($this->pagination->pageCount<=$page) ? true : false;

        // Choose view
        $viewFile = (in_array(substr($this->view, 0, 1), [DIRECTORY_SEPARATOR, '/']))
                    ? $this->view
                    : __DIR__ . "/views/{$this->view}.php";

        // Render view
        include $viewFile;

        return;
    }
}
