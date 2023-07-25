<?php

declare(strict_types=1);

namespace builder\Database\Traits;

use builder\Database\Capsule\Manager;
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
        if(Manager::AppDebug()){ 
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

}