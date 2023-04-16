<?php

use UltimateOrmDatabase\Methods\OrmDotEnv;

/*
 * This file is part of ultimate-orm-database.
 *
 * (c) Tame Developers Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$ormDotEnv = OrmDotEnv::load();


/*
|--------------------------------------------------------------------------
| Defining background color for var dump
|--------------------------------------------------------------------------
| default | main | dark | red | blue
*/

$ormDotEnv->bg = "default";


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
    $ormDotEnv->dump($ormDotEnv['message']);
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

define('ENV_CLASS', $ormDotEnv);

