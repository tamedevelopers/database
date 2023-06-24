<?php

use builder\Database\Model;

class Post extends Model{
    
    // define custom table name
    // by default it uses Pluralization
    // to define table name using Classname
    protected $table = 'posts';


    public function getPost()
    {
        return $this->random()->paginate(20);
    }

    // just sample data
    public function sample()
    {
        $this->dump(
            $this->dbConnection(), // database connection
            $this->getPDO(), // `PDO` Driver
            $this->dbQuery(), // to get last query
        );
    }

}