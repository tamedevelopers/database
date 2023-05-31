<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use Kint\Kint;
use Whoops\Run;
use Kint\Renderer\RichRenderer;
use builder\Database\Capsule\Manager;
use Whoops\Handler\PrettyPageHandler;

trait ReusableTrait{
    
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
            RichRenderer::$folder   = false;
            RichRenderer::$theme    = 'solarized.css';
            
            if(is_array($dataArray)){
                foreach ($dataArray as $var) {
                    Kint::dump($var);
                }
            } else{
                Kint::dump($dataArray);
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
            // header not sent
            // register error handler
            if (!headers_sent()) {
                $whoops = new Run();
                $whoops->pushHandler(new PrettyPageHandler());
                $whoops->register();
            }
        } 
    }

}