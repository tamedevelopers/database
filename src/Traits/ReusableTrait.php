<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use Whoops\Run;
use builder\Database\Capsule\Manager;
use Whoops\Handler\PrettyPageHandler;
use Symfony\Component\VarDumper\VarDumper;

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
            if(is_array($dataArray)){
                foreach ($dataArray as $var) {
                    VarDumper::dump($var);
                }
            } else{
                VarDumper::dump($dataArray);
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