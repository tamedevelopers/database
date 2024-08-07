# PHP ORM Database

[![Total Downloads](https://poser.pugx.org/tamedevelopers/database/downloads)](https://packagist.org/packages/tamedevelopers/database)
[![Latest Stable Version](https://poser.pugx.org/tamedevelopers/database/version.png)](https://packagist.org/packages/tamedevelopers/database)
[![License](https://poser.pugx.org/tamedevelopers/database/license)](https://packagist.org/packages/tamedevelopers/database)
[![Code Coverage](https://codecov.io/gh/tamedevelopers/database/branch/2.2.x/graph/badge.svg)](https://codecov.io/gh/tamedevelopers/database/branch/2.2.x)
[![Gitter](https://badges.gitter.im/tamedevelopers/database.svg)](https://app.element.io/#/room/#php-orm-database:gitter.im)

## Inspiration

Having been introduced to learning Laravel Framework; Over the past yr(s), Coming back to vanilla PHP,
was pretty tough. So i decided to create a much more easier way of communicating with Database, using native `PHP PDO:: Driver`.


## Documentation

* [Requirements](#requirements)
* [Installation](#installation)
* [Instantiate](#instantiate)
* [Init.php File](#init.php-file)
* [BootLoader](#bootLoader)
* [Database Connection](#database-connection)
* [Database Disconnect](#database-disconnect)
* [App Debug ENV](#app-debug-env)
* [More Database Connection Keys](#more-database-connection-keys)
* [Usage](#usage)
  * [Table](#table)
  * [Insert](#insert)
  * [Insert Or Ignore](#insert-or-ignore)
  * [Update](#update)
  * [Update Or Ignore](#update-or-ignore)
  * [Delete](#delete)
  * [Increment](#increment)
  * [Decrement](#decrement)
* [Fetching Data](#fetching-data)
    * [Get](#get)
    * [First](#first)
    * [First or Create](#first-or-create)
    * [First or Fail](#first-or-fail)
    * [Count](#count)
    * [Paginate](#paginate)
    * [Exist](#exists)
    * [Table Exists](#table-exists)
* [Collections](#collections)
    * [Collection Methods](#collection-methods)
    * [Collection Usage](#collection-usage)
* [Pagination](#pagination)
    * [Global Configuration](#global-configuration)
    * [Pagination Query](#pagination-query)
    * [Pagination Data](#pagination-data)
    * [Pagination Links](#pagination-links)
    * [Pagination Links Config](#pagination-links-config)
    * [Pagination Showing](#pagination-showing)
    * [Pagination Showing Config](#pagination-showing-config)
    * [Pagination Foreach Numbers](#pagination-foreach-numbers)
    * [Get Pagination](#get-pagination)
* [Clause](#clause)
  * [Query](#query)
  * [select](#select)
  * [selectRaw](#selectRaw)
  * [orderBy](#orderby)
  * [orderByRaw](#orderbyraw)
  * [latest](#latest)
  * [oldest](#oldest)
  * [inRandomOrder](#inRandomOrder)
  * [random](#random)
  * [limit](#limit)
  * [offset](#offset)
  * [join](#join)
  * [leftJoin](#leftJoin)
  * [where](#where)
  * [orWhere](#orwhere)
  * [whereRaw](#whereRaw)
  * [whereColumn](#wherecolumn)
  * [whereNull](#wherenull)
  * [orWhereNull](#orWhereNull)
  * [whereNotNull](#wherenotnull)
  * [orWhereNotNull](#orWhereNotNull)
  * [whereBetween](#wherebetween)
  * [whereNotBetween](#wherenotbetween)
  * [whereIn](#wherein)
  * [whereNotIn](#wherenotin)
  * [groupBy](#groupby)
* [Database Migration](#database-migration)
  * [Create Table Schema](#create-table-schema)
  * [Default String Length](#default-string-length)
  * [Update Column Default Value](#update-column-default-value)
  * [Run Migration](#run-migration)
  * [Drop Migration](#drop-migration)
  * [Drop Table](#drop-table)
  * [Drop Column](#drop-column)
* [Get Database Config](#get-database-config)
* [Get Database Connection](#get-database-connection)
* [Get Database Name](#get-database-name)
* [Get Database PDO](#get-database-pdo)
* [Get Database TablePrefix](#get-database-tableprefix)
* [Database Import](#database-import)
* [Update Env Variable](#update-env-variable)
* [Autoload Register](#autoload-register)
* [Collation And Charset](#collation-and-charset)
* [Extend Model Class](#extend-model-class)
* [Helpers Functions](#helpers-functions)
* [Error Dump](#error-dump)
* [Error Status](#error-status)
* [Useful links](#useful-links)


## Requirements

- `>= php 8.0+`

## Installation

Prior to installing `database package` get the [Composer](https://getcomposer.org) dependency manager for PHP because it'll simplify installation.

```
composer require tamedevelopers/database
```

## Instantiate

**Step 1** — `Require composer autoload`:
```
require_once __DIR__ . '/vendor/autoload.php';
```

**Step 2** — Call `the below method() and Run once in browser`
- This will auto setup your entire application on a `go!`

|  Description                                                                                  | 
|-----------------------------------------------------------------------------------------------|
| It's important to install vendor in your project root, As we use this to get your root [dir]  | 
| By default you don't need to define any path again                                            |
|                                                                                               |
| Files you'll see after you reload browser:                                                    |
| `.env` `.env.example` `.gitignore` `.htaccess` `.user.ini` `init.php`                         |

```
use Tamedevelopers\Database\AutoLoader;

AutoLoader::start();
```

- or -- `Helpers Function`
```
autoloader_start();
```

## Init.php File
- [optional] This will extends the `composer autoload` and other setup

|  Description                                                                                  | 
|-----------------------------------------------------------------------------------------------|
| Once application is started! You can choose to include the `init.php`                         | 
| The file includes all configuration needed and as well extends the `vendor/autoload.php` path.|


## BootLoader
- If you do not want to include or use the `init.php` file
    - All you need do is call the bootloader, to start your application.

```
use Tamedevelopers\Database\Capsule\AppManager;

AppManager::bootLoader();
```

- or -- `Helpers Function`
```
app_manager()->bootLoader();
```

## Database Connection
- You have the options to connect to multiple database 
    - First navigate to [config/database.php] file and add a configuration
    - Takes two (2) params `key` as `string` and `array` [optional]

```
DB::connection('connName', [optional]);
```

## Database Disconnect
- If you want to connect to already connected database, You first need to disconnect
    - Takes one param as `string`

```
DB::disconnect('connName');
```

## Database Reconnect
- same as `Database Connection`

```
DB::reconnect('connName', [optional]);
```

## App Debug Env
- The `.env` file contains a key called `APP_DEBUG`
    - It's mandatory to set to false in Production environment
    - This helps to secure your applicaiton and exit error 404
    - instead of displaying entire server errors.

| key               |  Type     |  Default Value        |
|-------------------|-----------|-----------------------|
| APP_DEBUG         |  boolean  |  `true`               |


## More Database Connection Keys
- All available connection keys
    - The DB_CONNECTION uses only `mysql`
    - No other connection type is supported for now.

| key               |  Type     |  Default Value        |
|-------------------|-----------|-----------------------|
| driver            |  string   |  `mysql`              |
| host              |  string   |  `localhost`          |
| port              |  int      |  `3306`               |
| database          |  string   |                       |
| username          |  string   |                       |
| password          |  string   |                       |
| charset           |  string   |  `utf8mb4`            |
| collation         |  string   |  `utf8mb4_unicode_ci` |
| prefix            |  string   |                       |
| prefix_indexes    |  bool     |  `false`              |

## Usage 
- All Methods of usage 

### Table
- Takes a parameter as `string` table_name
```
$db->table('users');
```

### Insert
- Takes one parameter as assoc array `column_name => value`
    - It returns an object on success or error

```
$db->table('users')->insert([
    'user_id'    => 10000001,
    'first_name' => 'Alfred',
    'last_name'  => 'Pete',
    'wallet_bal' => 0.00,
    'registered' => strtotime('now'),
]);

-- To see data, you need to save into a variable
```

### Insert Or Ignore
- Same as `insert()` method
    - It returns an object of created data or `false` on error

```
$db->table('users')->insertOrIgnore([
    'user_id'    => 10000001,
    'first_name' => 'Alfred',
]);
```

### Update
- Takes one parameter as assoc array `column_name => value`
    - Returns an `int` numbers of affected rows or error

```
$db->table('users')
    ->where('user_id', 10000001)
    ->update([
        'first_name' => 'Alfred C.',
    ]);
```

### Update Or Ignore
- Same as `update()` method
    - Returns an `int` numbers of affected rows or `0` on error

```
$db->table('users')
    ->where('user_id', 10000001)
    ->updateOrIgnore([
        'first_name' => 'Alfred C.',
    ]);
```

### Delete
- Returns an `int`

```
$db->table('users')
    ->where('user_id', 10000001)
    ->delete();
```

### Increment

- Takes three parameter
    - Only the first param is required

| param             |  Data types     |
|-------------------|-----------------|
| column `required` |  string         |
| `count or []`     |  int \| array   |
| param             |  array          |

1 By default if the the second param not passed, this will increment by 1
```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal');
```

```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal', 10);
```

- You can also pass in a second or third parameter to update additional columns
```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal', 100.23, [
        'first_name' => 'F. Peterson',
        'status'     => 1,
    ]);
```

- You can ommit the second param and it'll be automatically seen as update param (If an array)
```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal', [
        'first_name' => 'F. Peterson',
        'status'     => 1,
    ]);
```

### Decrement
- Same as Increment
```
$db->table('users')
    ->where('user_id', 10000001)
    ->decrement('wallet_bal', [
        'first_name' => 'F. Peterson',
        'status'     => 1,
    ]);
```

## Fetching Data

| object name       |  Returns                          |
|-------------------|-----------------------------------|
| get()             |  array of objects                 |
| find()            |  `object` \| `null`               |
| first()           |  `object` \| `null`               |
| FirstOrIgnore()   |  `object` \| `null`               |
| FirstOrCreate()   |  object                           |
| firstOrFail()     |  object or exit with 404 status   |
| count()           |  int                              |
| paginate()        |  array of objects                 |
| exists()          |  boolean `true` \| `false`        |
| tableExists()     |  boolean `true` \| `false`        |

### GET
```
$db->table('users')->get();
```

### First
```
$db->table('users')->first();
```

### First or Create
- Take two param as an `array`
    -  Mandatory `$conditions` param as `array`
    - [optional] `$data` param as `array`

- First it checks if codition to retrieve data.
    If fails, then it merge the `$conditions` to `$data` value to create new records


```
$db->table('users')->firstOrCreate(
    ['email' => 'example.com']
);
```
- or -- `Example 2`

```
$db->table('users')->firstOrCreate(
    ['email' => 'example.com'],
    [
        'country'   => 'Nigeria',
        'age'       => 18,
        'dob'       => 2001,
    ]
);
```

### First or Fail
- Same as `first()` method but exit with error code 404, if data not found

```
$db->table('users')->firstOrFail();
```

### Count
```
$db->table('users')->count();
```

### Paginate
- Takes param as `int` `$per_page`
    - By default if no param is given, then it displays 10 per page
```
$users = $db->table('users')
            ->paginate(40);


$users // this will return the data objects
$users->links() // this will return the paginations links view
$users->showing() // Display items of total results
```

### Exists
- Returns boolean `true \| false`

```
$db->table('users')
    ->where('email', 'email@gmail.com')
    ->orWhere('name', 'Mandison')
    ->exists();
```

### Table Exists
- Takes param as `string` `$table_name`
```
$db->tableExists('users');
```

## Collections
- You can directly use `methods` of `Collections Instance` on any of the below
    - All the below `methods` are received by Collection `class`
    1. get()
    2. find()
    2. first()
    3. firstOrIgnore()
    3. firstOrCreate()
    4. firstOrFail()
    5. insert()
    6. insertOrIgnore()


### Collection Methods
|    Methods        |          Description                          |
|-------------------|-----------------------------------------------|
|  getAttributes()  |  `array` Returns an array of data             |
|  getOriginal()    |  `object` Returns an object of data           |
|  isEmpty()        |  `boolean` `true \| false` If data is empty   |
|  isNotEmpty()     |  `opposite` of `->isEmpty()`                  |
|  count()          |  `int` count data in items collection         |
|  toArray()        |  `array` Convert items to array               |
|  toObject()       |  `object` Convert items to object             |
|  toJson()         |  `string` Convert items to json               |
 

### Collection Usage
- Colections are called automatically on all Database Fetch Request
    - With this you can access data as an `object\|array` key property
    - If no data found then it returns null on `->first()` method only

```
$user = $db->tableExists('users')
            ->first();

if($user){
    $user->first_name
    $user['first_name']
}

$user->toArray()
$user->getAttributes()
```

- Example two(2) `->get() \| ->paginate()` Request

```
$users = $db->tableExists('users')
            ->where('is_active', 1),
            ->random(),
            ->get();

if($users->isNotEmpty()){
    foreach($users as $user){
        $user->first_name
        $user['first_name']
        $user->toArray()
        $user->getAttributes()
    }
}
```

## Pagination
- Configuring Pagination

| key       | Data Type                 |  Description    |
|-----------|---------------------------|-----------------|
| allow     | `true` \| `false`         | Default `false` Setting to true will allow the system use this settings across app|
| class     | string                    | Css `selector` For pagination ul tag in the browser |
| span      | string                    | Default `.page-span` Css `selector` For pagination Showing Span tags in the browser |
| view      | `bootstrap` \| `simple` \| `cursor` | Default `simple` - For pagination design |
| first     | string                    | Change the letter `First` |
| last      | string                    | Change the letter `Last` |
| next      | string                    | Change the letter `Next` |
| prev      | string                    | Change the letter `Prev` |
| showing   | string                    | Change the letter `Showing` |
| of        | string                    | Change the letter `of`      |
| results   | string                    | Change the letter `results` |
| buttons   | int                       | Numbers of pagination links to generate. Default is 5 and limit is 20 |


### Global Configuration 
- 1 Setup global pagination on ENV autostart `most preferred` method
```
AutoLoader::configPagination([
    'allow' => true, 
    'prev'  => 'Prev Page', 
    'last'  => 'Last Page', 
    'next'  => 'Next Page', 
    'view'  => 'bootstrap',
    'class' => 'Custom-Class-Css-Selector', 
]);
```

- or -- `Helpers Function`
```
config_pagination([
    'allow' => true,
]);
```

### Pagination Query
```
$users = $db->table('users')->paginate(40);
```

### Pagination Data
```
$users
// This will return `Collections` of pagination data
```

### Pagination Links
```
$users->links();
// This will return pagination links view
```

### Pagination Links Config
<details><summary>Read more...</summary>

- You can directly configure pagination links
    - Note: If `configPagination()` `allow` is set to `true`
    - It'll override every other settings
```
$users->links([
    'first' => 'First Page',
    'last'  => 'Last Page',
    'prev'  => 'Previous Page',
    'next'  => 'Next Page',
])
```
</details>

### Pagination Showing
```
$users->showing();

// This will create a span html element with text
<span class='page-span'>
    Showing 0-40 of 500 results
</span>
```

### Pagination Showing Config
<details><summary>Read more...</summary>

- You can configure showing text directly as well
```
$users->showing([
    'showing'  => 'Showing',
    'of'       => 'out of',
    'results'  => 'Results',
    'span'     => 'css-selector',
])
```
</details>

### Pagination Foreach Numbers
- Page numbering `starts counting from 1`
    - This will format all pagination items collections
    - On each page, it starts counting from last pagination item number

```
$users = $db->table('users')->paginate(20);

foreach($users as $user){
    echo $user->numbers();
}
```

### Get Pagination
- Returns pagination informations

| key           |  Description              |
|---------------|---------------------------|
| limit         | Pagination limit `int`    |
| offset        | Pagination offset `int`   |
| page          | Pagination Current page `int`     |
| pageCount     | Pagination Total page count `int` |
| perPage       | Pagination per page count `int`   |
| totalCount    | Pagination total items count `int`|

```
$users = $db->table('users')->paginate(20);

$users->getPagination();
```

## Clause
- Multiple clause

### Query
- Allows the use direct sql query `SQL query syntax`
    - Or direct query exec()

```
$db->query("SHOW COLUMNS FROM users")
    ->limit(10)
    ->get();


$db->query("ALTER TABLE `langs` ADD COLUMN es TEXT; UPDATE `langs` SET es = en;")
    ->exec();
```

### Select
- Used to select needed columns from database

```
$db->table('users')
    ->where('user_id', 10000001)
    ->select(['first_name', 'email'])
    ->select('email, 'name')
    ->first();
```

### orderBy
- Takes two param `$column` and `$direction`
    - By default  `$direction` param is set to `ASC`

```
$db->table('wallet')
    ->orderBy('date', 'DESC')
    ->get();
```

### orderByRaw
- Takes one param `$query`

```
$db->table('wallet')
    ->orderByRaw('CAST(`amount` AS UNSIGNED) DESC')
    ->get();
```


### Latest
- Takes one param `$column` by default the column used is `id`
```
$db->table('wallet')
    ->latest('date')
    ->get();
```

### Oldest
- Takes one param `$column` by default the column used is `id`
```
$db->table('wallet')
    ->oldest()
    ->get();
```

### inRandomOrder
```
$db->table('wallet')
    ->inRandomOrder()
    ->get();
```

### random
<details><summary>Read more...</summary>

- Same as `inRandomOrder()`
```
$db->table('wallet')
    ->random()
    ->get();
```
</details>

### limit
- Takes one param `$limit` as int. By default value is `1`
```
$db->table('wallet')
    ->limit(10)
    ->get();
```

### offset
<details><summary>Read more...</summary>

- Takes one param `$offset` as int. By default value is `0`
```
$db->table('wallet')
    ->limit(3)
    ->offset(2)
    ->get();
```

- Example 2 (Providing only offset will return as LIMIT without error)
```
$db->table('wallet')
    ->offset(2)
    ->get();
```
</details>

### join
- Includes `join`|`leftJoin`|`rightJoin`|`crossJoin`

| Params        |  Description      |
|---------------|-------------------|
| table         |  table            |
| foreignColumn |  table.column     |
| operator      |  operator sign    |
| localColumn   | local_table.column|

```
$db->table('wallet')
    ->join('users', 'users.user_id', '=', 'wallet.user_id')
    ->get();
```

- or
```
$db->table('wallet')
    ->join('users', 'users.user_id', '=', 'wallet.user_id')
    ->where('wallet.email', 'example.com')
    ->orWhere('wallet.user_id', 10000001)
    ->paginate(10);
```

### leftJoin
- Same as `join`

```
$db->table('wallet')
    ->leftJoin('users', 'users.user_id', '=', 'wallet.user_id')
    ->where('wallet.email', 'example.com')
    ->get();
```

### where
- Takes three parameter
    - Only the first param is required

| param     | Data types |
|-----------|------------|
| column    |  string    |
| operator  |  string    |
| value     |  string    |
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->where('amount', '>', 10)
    ->where('balance', '>=', 100)
    ->get();
```

### orWhere
<details><summary>Read more...</summary>

- Same as Where clause
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->where('amount', '>', 10)
    ->orWhere('first_name', 'like', '%Peterson%')
    ->where('amount', '<=', 10)
    ->get();
```
</details>

### whereRaw
- Allows you to use direct raw `SQL query syntax`

```
$date = strtotime('next week');

$db->table("tb_wallet")
    ->whereRaw("NOW() > created_at")
    ->whereRaw("date >= ?", [$date])
    ->where(DB::raw("YEAR(created_at) = 2022"))
    ->where('email', 'email@gmail.com')
    ->limit(10)
    ->random()
    ->get();
```

### whereColumn
- Takes three parameter `column` `operator` `column2`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereColumn('amount', 'tax')
    ->whereColumn('amount', '<=', 'balance')
    ->get();
```

### whereNull
- Takes one parameter `column`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNull('email_status')
    ->get();
```

### whereNotNull
<details><summary>Read more...</summary>

- Takes one parameter `column`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNotNull('email_status')
    ->get();
```
</details>

### whereBetween
- Takes two parameter `column` as string `param` as array
    - Doesn't support float value

| param  | Data types |     Value    |
|--------|------------|--------------|
| column | string     | `column_name`|
| param  | array      | [10, 100]    |

```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereBetween('amount', [0, 100])
    ->get();
```

### whereNotBetween
<details><summary>Read more...</summary>

- Same as `whereBetween()` method

```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNotBetween('amount', [0, 100])
    ->get();
```
</details>

### whereIn
- Takes two parameter `column` as string `param` as array
    - Doesn't support float value

| param  | Data types |     Value    |
|--------|------------|--------------|
| column | string     | `column_name`|
| param  | array      | [0, 20, 80]  |

```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereIn('amount', [10, 20, 40, 100])
    ->get();
```

### whereNotIn
<details><summary>Read more...</summary>

Same as `whereIn()` method
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNotIn('amount', [10, 20, 40, 100])
    ->get();
```
</details>

### groupBy
- Takes one param `$column`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->groupBy('amount')
    ->get();
```

## Database Migration
- Similar to Laravel DB Migration `Just to make database table creation more easier`

| object name   |  Returns                          |
|---------------|---------------------------------- |
| create()      |  Create table schema              |
| run()         |  Begin migration                  |
| drop()        |  Drop migration tables            |

```
use Tamedevelopers\Database\Migrations\Migration;
```

### Create Table Schema
- Takes param as `table name`
    - Second parameter `string` `jobs|sessions` (optional) -If passed will create a dummy `jobs|sessions` table schema

```
Migration::create('users');
Migration::create('users_wallet');
Migration::create('tb_jobs', 'jobs');
Migration::create('tb_sessions', 'sessions'); 

Table `2023_04_19_1681860618_user` has been created successfully
Table `2023_04_19_1681860618_user_wallet` has been created successfully
Table `2023_04_19_1681860618_tb_jobs` has been created successfully
Table `2023_04_19_1681860618_tb_sessions` has been created successfully
```

- or -- `Helpers Function`
```
migration()->create('users');
```
![Sample Session Schema](https://raw.githubusercontent.com/tamedevelopers/UltimateOrmDatabase/main/sessions.png)

### Default String Length
- In some cases you may want to setup default string legnth to all Migration Tables

|  Description                                                                          | 
|---------------------------------------------------------------------------------------|
| The Default Set is `255` But you can override by setting custom value                 |
| According to MySql v:5.0.0 Maximum allowed legnth is  `4096` chars                    |
| If provided length is more than that, then we'll revert to default as the above       |
| This affects only `VACHAR`                                                            |
| You must define this before start using the migrations                                |

```
use Tamedevelopers\Database\Migrations\Schema;

Schema::defaultStringLength(200);
```

- or -- `Helpers Function`
```
schema()->defaultStringLength(2000);
```

### Update Column Default Value
- In some cases you may want to update the default column value
    - Yes! It's very much possible with the help of Schema. Takes three (3) params
    - `$tablename` as string
    - `$column_name` as string
    - `$values` as mixed data `NULL` `NOT NULL\|None` `STRING` `current_timestamp()`

```
use Tamedevelopers\Database\Migrations\Schema;

Schema::updateColumnDefaultValue('users_table', 'email_column', 'NOT NULL);
Schema::updateColumnDefaultValue('users_table', 'gender_column', []);

or

schema()->updateColumnDefaultValue('users_table', 'gender_column', []);
```

### Run Migration
- This will execute and run migrations using files located at [root/database/migrations]

```
Migration::run();

or
migration()->run();

Migration runned successfully on `2023_04_19_1681860618_user` 
Migration runned successfully on `2023_04_19_1681860618_user_wallet` 
```

### Drop Migration
<details><summary>Read more...</summary>

- Be careful as this will execute and drop all files table `located in the migration`
- [optional param] `bool` to force delete of tables
```
Migration::drop();

or
migration()->drop(true);
```
</details>

### Drop Table
<details><summary>Read more...</summary>

- Takes one param as `string` $table_name
```
use Tamedevelopers\Database\Migrations\Schema;

Schema::dropTable('table_name');

or 
schema()->dropTable('table_name');
```
</details>

### Drop Column
<details><summary>Read more...</summary>

- To Drop Column `takes two param`
    - This will drop the column available
```
use Tamedevelopers\Database\Migrations\Schema;

Schema::dropColumn('table_name', 'column_name');

or 
schema()->dropColumn('table_name', 'column_name');
```
</details>

## Get Database Config
```
$db->getConfig()
```

## Get Database Connection
```
$db->dbConnection()
```

- or -- `Helpers Function`
```
db_connection();
```

## Get Database Name
```
$db->getDatabaseName()
```

## Get Database PDO
```
$db->getPDO()
```

## Get Database TablePrefix
```
$db->getTablePrefix()
```

## Database Import
- You can use this class to import .sql into a database programatically
    - Remember the system already have absolute path to your project.

```
use Tamedevelopers\Database\DBImport;

$database = new DBImport();

// needs absolute path to database file
$status = $database->import('path_to/orm.sql');

- Status code
->status == 404 (Failed to read file or File does'nt exists
->status == 400 (Query to database error
->status == 200 (Success importing to database
```

- or -- `Helpers Function`
```
import('path_to/orm.sql');
```

## Update Env Variable
- You can use this class to import .sql into a database programatically

| Params        |  Description      |
|---------------|-------------------|
| key           |  ENV key          |
| value         |  ENV value        |
| allow_quote   |  `true` \| `false` - Default is true (Allow quotes within value)  |
| allow_space   | `true` \| `false`  - Default is false (Allow space between key and value)|

```
use Tamedevelopers\Support\Env;

Env::updateENV('DB_PASSWORD', 'newPassword');
Env::updateENV('APP_DEBUG', false);
Env::updateENV('DB_CHARSET', 'utf8', false);

Returns - Boolean
true|false
```

- or -- `Helpers Function`
```
env_update('DB_CHARSET', 'utf8', false);
```

## Collation And Charset
- Collation and Charset Data `listing`

### Collation
- utf8_bin
- utf8_general_ci
- utf8mb4_bin
- utf8mb4_unicode_ci
- utf8mb4_general_ci
- latin1_bin
- latin1_general_ci

### Charset
- utf8
- utf8mb4
- latin1

## Extend Model Class
<details><summary>Read more...</summary>

- You can as well extends the DB Model class directly from other class
```
use Tamedevelopers\Database\Model;

class Post extends Model{
    
    // define your custom model table name
    protected $table = 'posts';

    -- You now have access to the DB public instances
    public function getPost(){
        return $this->select(['images', 'title', 'description'])->get();
    }
}
```
</details>

## Helpers Functions

| function name             | Description                                   |
|---------------------------|-----------------------------------------------|
| db()                      | Return instance of `new DB($options)` class   |
| db_connection()           | Same as `$db->dbConnection()`                 |
| config_pagination()       | Same as `$db->configPagination()` or `AutoLoader::configPagination`  |
| autoloader_start()        | Same as `AutoLoader::start()`                 |
| env_update()              | Same as `Env::updateENV` method               |
| app_manager()             | Return instance of `(new AppManager)` class       |
| import()                  | Return instance of `(new DBImport)->import()` method      |
| migration()               | Return instance of `(new Migration)` class    |
| schema()                  | Return instance of `(new Schema)` class       |

## Error Dump

| function  | Description       |
|-----------|-------------------|
| dump      | Dump Data         |
| dd        | Dump and Die      |


## Error Status

- On error returns `404` status code 
- On success returns `200` status code

## Useful Links

- @author Fredrick Peterson (Tame Developers)   
- If you love this PHP Library, you can [Buy Tame Developers a coffee](https://www.buymeacoffee.com/tamedevelopers)
- [Lightweight - PHP ORM Database](https://github.com/tamedevelopers/database)
- [Support - Library](https://github.com/tamedevelopers/support)
