<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use Tracy\Debugger;
use builder\Database\Capsule\Manager;

trait ReusableTrait{
    
    /**
     * Exit script on dump
     * @var bool
    */
    public $dump_final = true;
    
    /**
     * Headers
     * 
     * @return void
    */
    private function setHeaders()
    {
        Manager::setHeaders();
    }
    
    /**
     * Die or Dump Error Handler
     * @param mixed $data
     *  
     * @return mixed
     */
    public function dump(...$data)
    {
        // if DEBUG MODE IS ON
        if(Manager::setEnvBool(APP_DEBUG)){
            $dataArray = $data[0] ?? $data;
            if(is_array($dataArray)){
                foreach ($dataArray as $var) {
                    Debugger::dump($var);
                }
            }else{
                Debugger::dump($dataArray);
            }
        } else{
            if($this->dump_final){
                $this->setHeaders();
            }
        }
    }

    /**
     * Autostart debugger for error logger
     * 
     * @return string
     */
    public function autoStartDebugger()
    {
        // if DEBUG MODE IS ON
        if(Manager::setEnvBool(APP_DEBUG)){
            // register debugger
            Debugger::$showBar = false;
            Debugger::$strictMode = true; // display all errors
            Debugger::$maxDepth = 5; // default: 3
            Debugger::$maxLength = 1000; // default: 150
            Debugger::$dumpTheme = $this->getBgColor(APP_DEBUG_BG);
            Debugger::enable(!APP_DEBUG);
        } 
    }

    /**
     * Remove footer
     * @return void
     */
    private function removeFooter()
    {
        echo "<style>footer, .tracy-footer--sticky{display: none !important; visibility: hidden !important;}</style>";
    }

    /**
     * Get background color
     * @param string $color
     * 
     * @return string
     */
    private function getBgColor(?string $color = null)
    {
        $data = [
            'light' => 'light',
            'dark'  => 'dark',
        ];

        return $data[$color] ?? $data['light'];
    }


}