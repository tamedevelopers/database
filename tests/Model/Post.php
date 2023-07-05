<?php

use builder\Database\DB;
use builder\Database\Model;

class Post extends Model{
    
    /**
     * The table associated with the model.
     * 
     * @var string|null
     * 
     * - by default the system uses Pluralization 
     * - to define table name for model, if no table name was given
     */
    // protected $table = 'posts';


    public function getPost()
    {
        return $this->random()->paginate(20);
    }

    // just sample data
    public function sample()
    {
        $woocommerce = DB::connection('woocommerce');
        $woocommerce2 = db('woocommerce');


        dd(
            $this->raw(''), // raw expression
            DB::raw(''), // raw expression
            DB::connection()->dbConnection(), // connection data
            DB::connection()->getPDO(), // `PDO` Driver
            DB::connection()->getDatabaseName(), // database name
            DB::connection()->getConfig(), // database configuration 
            DB::connection()->getTablePrefix(), // table prefix


            db_connection('default'), //helper function
            $woocommerce->dbConnection(), // table prefix
            $woocommerce2->dbConnection(), // table prefix
        );
    }

}