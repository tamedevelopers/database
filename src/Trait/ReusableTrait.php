<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Trait;

use Symfony\Component\VarDumper\VarDumper;


trait ReusableTrait{
    
    /**
     * Exit script on dump
     * @var bool
    */
    public $dump_final = true;
    
    /**
     * Define var_dump background color
     * @var string
    */
    public $bg = 'default';

    /**
     * Background colors
     * @var array
    */
    private $backgroundColors = [
        'default'   => '',
        'main'      => 'background-color: #18171B !important; color: #FF8400 !important;',
        'dark'      => 'background-color: #222222 !important; color: #F1F1F1 !important;',
        'red'       => 'background-color: #840808 !important; color: #FFFFFF !important;',
        'blue'      => 'background-color: #160082 !important; color: #FF8400 !important;',
    ];
    
    /**
     * Format query data to browser
     *  
     * @return void|string\dump
     */
    public function dump($data)
    {
        // get App Config
        $appConfig = $this->AppConfig();

        // get bg
        $bg =   isset($_ENV['APP_DEBUG_BG']) 
                ? $_ENV['APP_DEBUG_BG'] 
                : isset($appConfig['APP_DEBUG_BG']) 
                ?? $this->bg;

        // app data
        $App =  is_array($appConfig) 
                ? $appConfig['APP_DEBUG'] 
                : true;

        // if DEBUG MODE IS ON
        if($App){
            VarDumper::dump($data, 20, true, [
                'highlight'         => true, // Enable syntax highlighting
                'maxDepth'          => 3, // Set maximum depth for arrays and objects
                'maxStringLength'   => 500, // Set maximum string length to display
                'expand'            => 'toggle', // Enable toggling arrays and objects
            ]);
            echo "<style>pre.sf-dump, pre.sf-dump .sf-dump-default{{$this->getBgColor( $bg )}}</style>";
            if($this->dump_final)
                die(1);
        }
    }
    
    /**
     * Get background color
     * @param string $color
     * 
     * @return string\getBgColor
     */
    private function getBgColor($color)
    {
        return isset($this->backgroundColors[$color]) 
                ? $this->backgroundColors[$color] 
                : $this->backgroundColors['default'];
    }

}