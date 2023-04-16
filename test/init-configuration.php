<?php

/*
 * This file is part of ultimate-orm-database.
 *
 * (c) Tame Developers Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use UltimateOrmDatabase\Methods\OrmDotEnv;

/*
|--------------------------------------------------------------------------
| Instance of class
|--------------------------------------------------------------------------
*/
$ormDotEnv = new OrmDotEnv();

/*
|--------------------------------------------------------------------------
| Create a sample .env file if not exist in project
|--------------------------------------------------------------------------
*/
$ormDotEnv::createOrIgnore();


/*
|--------------------------------------------------------------------------
| Load environment file (associated to database)
|--------------------------------------------------------------------------
| This will automatically6 setup our database configuration if found 
|
*/
$loader = $ormDotEnv::load();


/*
|--------------------------------------------------------------------------
| Defining background color for var dump
|--------------------------------------------------------------------------
| default | main | dark | red | blue
*/
$ormDotEnv->bg = 'default';


/*
|--------------------------------------------------------------------------
| Check If There was an error getting the environment file
|--------------------------------------------------------------------------
|
| If there's an error then exit code from running, as this will cause 
| Error on using the Database model
|
*/
if($ormDotEnv['response'] != 200){
    /**
     * Setting application to use the dump error handling
     */
    $ormDotEnv->dump_final = true;

    /**
     * Dump error message
     */
    $ormDotEnv->dump( $loader['message'] );
    die(1);
}


/*
|--------------------------------------------------------------------------
| Storing data into a Constant once everything is successful
|--------------------------------------------------------------------------
| We can now use on anywhere on our application 
| Mostly to get our defined .env root Path
|
| ENV_CLASS['path'] -> return .env root Path
*/
if ( ! defined('ORM_ENV_CLASS') ) {
    define('ORM_ENV_CLASS', $ormDotEnv);
}

