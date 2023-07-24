<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use Kint\Kint;
use Kint\Renderer\RichRenderer;
use builder\Database\Capsule\Manager;

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
        if(Manager::AppDebug()){ 
            $dataArray = $data[0] ?? $data;
            RichRenderer::$folder = false;
            
            if(is_array($dataArray)){
                foreach ($dataArray as $var) {
                    Kint::dump($var);
                }
            } else{
                Kint::dump($dataArray);
            }
        }
    }

}