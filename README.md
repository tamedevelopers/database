# Lightweight ORM PHP Database Model - UltimateOrmDatabase

### @author Fredrick Peterson (Tame Developers)
Lightweight ORM PHP Database Model

Ultimate ORM Database

* [Requirements](#requirements)
* [Installation](#installation)
* [Instantiate](#instantiate)
* [Database Connection](#database-connection)
* [More Database Connection Keys](#more-database-connection-keys)
* [Usage](#usage)
  * [Table](#table)
  * [Insert](#insert)
  * [Update](#update)
  * [Delete](#delete)
  * [Increment](#increment)
  * [Decrement](#decrement)
  * [Raw](#raw)
* [Fetching Data](#fetching-data)
    * [Get](#get)
    * [Get Arr](#getarr)
    * [First](#first)
    * [First or Fail](#first-or-fail)
    * [Count](#count)
    * [Paginate](#paginate)
    * [Table Exist](#table-exist)
* [Clause](#clause)
  * [select](#select)
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
  * [whereColumn](#wherecolumn)
  * [whereNull](#wherenull)
  * [whereNotNull](#wherenotnull)
  * [whereBetween](#wherebetween)
  * [whereNotBetween](#wherenotbetween)
  * [whereIn](#wherein)
  * [whereNotIn](#wherenotin)
  * [groupBy](#groupby)
* [Database Migration](#database-migration)
  * [Migration Table](#migration-table)
  * [Run Migration](#run-migration)
  * [Create Database Schema](#create-database-schema)
  * [Drop Table](#drop-table)
  * [Drop Column](#drop-column)
* [toArray](#toarray)
* [toObject](#toobject)
* [Pagination](#pagination)
* [Get Database Query](#get-database-query)
* [Get Database Config Data](#get-database-config-data)
* [Get Database Connection](#get-database-connection)
* [Pagination](#pagination)
* [Database Import](#database-import)
* [Update Env Variable](#update-env-variable)
* [Collation And Charset](#collation-and-charset)
* [Extend DB Class](#extend-db-class)
* [Error Status](#error-status)
* [Useful links](#useful-links)


## Requirements

- `>= php7.0+`

## Installation

Prior to installing `ultimate-orm-database` get the [Composer](https://getcomposer.org) dependency manager for PHP because it'll simplify installation.

**Step 1** — update your `composer.json`:
```composer.json
"require": {
    "peterson/ultimate-orm-database": "^2.0.1" 
}
```

**Or composer install**:
```
composer require peterson/ultimate-orm-database
```

**Step 2** — run [Composer](https://getcomposer.org):
```update
composer update
```

## Instantiate

**Step 1** — Composer  `Instantiate class using`:
```
require_once __DIR__ . '/vendor/autoload.php';

use UltimateOrmDatabase\DB;

$db = new DB();
```

## Database Connection

### Env Auto Loader  - `Most preferred`
- Just call class and see it's magic `.env auto setup`
    - By default you don't need to provide any path, since the Model use your project root [dir]
        - Have `removed support` for direct db config

```
use UltimateOrmDatabase\AutoloadEnv;

// call the autoLoader
AutoloadEnv::start([
    'path' => 'define root path or ignore'
]);

This is all you need to do, before using the DB Class
```

### Direct DB Connection - `Supported but ![Recommended]`
- When initializing the class
    - Pass an array as a param to the class
        - Why not recommended? Because you must use thesame varaible name `$db` everywhere in your app
```
$db = new DB([
    'DB_USERNAME' => '',
    'DB_PASSWORD' => '',
    'DB_DATABASE' => '',
]);

Still working but not supported, use `ENV autoloader` recommended
```

-1 Or Better still `If you consider using it that way`
```
define("DATABASE_CONNECT", new DB([
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
    'DB_DATABASE' => '',
]));

Now you have access to the CONSTANT andywhere in your app.

$db = DATABASE_CONNECT;

$db->table('users')
    ->limit(10),
    ->get();
```



## More Database Connection Keys
- All available connection keys
    - The DB_CONNECTION uses only `mysql`
        - No other connection type is supported for now.

| key               |  Type     |  Default Value        |
|-------------------|-----------|-----------------------|
| DB_CONNECTION     |  string   |  mysql                |
| APP_DEBUG         |  boolean  |  true                 |
| APP_DEBUG_BG      |  string   |  Default value is `default` and other color \| `main`  \| `dark` \| `red` \| `blue` |
| DB_HOST           |  string   |  `localhost`          |
| DB_USERNAME       |  string   |                       |
| DB_PASSWORD       |  string   |                       |
| DB_DATABASE       |  string   |                       |
| DB_PORT           |  int      |  `3306`               |
| DB_CHARSET        |  string   |  `utf8mb4_unicode_ci` |
| DB_COLLATION      |  string   |  `utf8mb4`            |


## Usage 
- All Methods of usage 

### Table
- Takes a parameter `string of table_name`
```
$db->table('users');
```

### Insert
- Takes one parameter as assoc array `column_name => value`
- It returns an array data

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

### Update
- Takes one parameter as assoc array `column_name => value`
- It returns an array data

```
$db->table('users')
    ->where('user_id', 10000001)
    ->update([
        'first_name' => 'Alfred C.',
    ]);
```

### Delete

| errors  |  Description                                         |
|---------|------------------------------------------------------|
| 200     |  Data deleted successfully                           |
| 400     |  Delete method successfully, but no data was deleted |
| 404     |  Delete was not successfully executed                |

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

1 By default if the the second param not passed, the it increment by 1
```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal');
```

```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal', 10);
    
-- Query
UPDATE `users` 
    SET wallet_bal=wallet_bal+:10 
    WHERE user_id=:user_id
```

- You can also pass in a second or third parameter to update additional columns
```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal', 10, [
        'first_name' => 'F. Peterson',
        'status'     => 1,
    ]);

-- Query
UPDATE `users` 
    SET wallet_bal=wallet_bal+:10, first_name=:first_name, status=:status
    WHERE user_id=:user_id
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

### Raw
- Allows you to use direct raw `SQL query syntax`

- 1 usage
```
$db->raw('SELECT * FROM users')
    ->where('is_active', 1)
    ->count();

-- Query
SELECT count(*) FROM users
    WHERE is_active=:is_active
```

- 2 usage
```
$db->raw('SELECT * FROM users')
    ->where('is_active', 1)
    ->first();

-- Query
SELECT * FROM users
    WHERE is_active=:is_active
    LIMIT 1
```


## Fetching Data

| object name   |  Returns           |
|---------------|--------------------|
| get()         |  array of objects  |
| first()       |  object            |
| firstOrFail() |  object or exit with 404 status   |
| count()       |  int               |
| paginate()    |  array of objects  |
| tableExist()  |  array             |

### GET
```
$db->table('users')->get();

-- Query
SELECT * 
    FROM `users`
```

### GetArr
```
$db->table('users')->getArr();

-- Query
SELECT * 
    FROM `users`
```

### First
```
$db->table('users')->first();

-- Query
SELECT * 
    FROM `users` LIMIT 1
```

### First or Fail
- Same as `first()` method but exit with error code 404, if data not found
```
$db->table('users')->firstOrFail();

-- Query
SELECT * 
    FROM `users` LIMIT 1
```

### Count
```
$db->table('users')->count();

-- Query
SELECT count(*) 
    FROM `users` 
```

### Paginate
- Takes param as `int` `$per_page`
    - By default if no param is given, then it displays 10 per page
```
$users = $db->table('users')->paginate(40);

SELECT * FROM `users` 
    LIMIT 0, 40 

object {
    "data": []
    "pagination": UltimateOrmDatabase\DB {}
}

$users->data // this will return the data objects
$users->paginate->links() // this will return the paginations links view
```

### Table Exist
- Takes param as `string` `$table_name`
```
$db->tableExist('users');

-- Either 404|200
array [
    "status" => 200
    "message" => "Table name `users` exist."
]
```

## Clause
- Multiple clause

### Select
- Used to select needed columns from database
```
$db->table('users')
    ->where('user_id', 10000001)
    ->select(['first_name', 'email'])
    ->first();

-- Query
SELECT first_name, email 
    FROM `users` 
    WHERE user_id=:user_id 
    LIMIT 1
```

### orderBy
- Takes two param `$column` and `$direction`
    - By default  `$direction` param is set to `ASC`
```
$db->table('wallet')
    ->orderBy('date', 'DESC')
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    ORDER By date DESC
```

### orderByRaw
- Takes one param `$query`
```
$db->table('wallet')
    ->orderByRaw('CAST(`amount` AS UNSIGNED) DESC')
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    ORDER By CAST(`amount` AS UNSIGNED) DESC
```

### Latest
- Takes one param `$column` by default the column used is `id`
```
$db->table('wallet')
    ->latest('date')
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    ORDER By date DESC
```

### Oldest
- Takes one param `$column` by default the column used is `id`
```
$db->table('wallet')
    ->oldest()
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    ORDER By id ASC
```

### inRandomOrder
```
$db->table('wallet')
    ->inRandomOrder()
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    ORDER BY RAND()
```

### random
- Same as `inRandomOrder()`
```
$db->table('wallet')
    ->random()
    ->get();
```

### limit
- Takes one param `$limit` as int. By default value is `1`
```
$db->table('wallet')
    ->limit(10)
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    LIMIT 10
```

### offset
- Takes one param `$offset` as int. By default value is `0`
```
$db->table('wallet')
    ->limit(3)
    ->offset(2)
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    LIMIT 2, 3
```

- Example 2 (Providing only offset will return as LIMIT without error)
```
$db->table('wallet')
    ->offset(2)
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    LIMIT 2
```

### join
| Params        |  Description      |
|---------------|-------------------|
| table         |  table            |
| foreignColumn |  table.column     |
| operator      |  operator sign    |
| localColumn   | local_table.column |
```
$db->table('wallet')
    ->join('users', 'users.user_id', '=', 'wallet.user_id')
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    INNER JOIN `users` ON users.user_id = wallet.user_id
```

### leftJoin
- Same as `join`
```
$db->table('wallet')
    ->leftJoin('users', 'users.user_id', '=', 'wallet.user_id')
    ->get();

SELECT * 
    FROM `wallet`
    LEFT JOIN `users` ON users.user_id = wallet.user_id
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

-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND amount >: amount AND balance >= : balance
```

### orWhere
- Same as Where clause
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->where('amount', '>', 10)
    ->orWhere('first_name', 'like', '%Peterson%')
    ->where('amount', '<=', 10)
    ->get();

-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND amount > :amount
    OR first_name like :first_name AND amount <= :amount
```

### whereColumn
- Takes three parameter `column` `operator` `column2`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereColumn('amount', 'tax')
    ->whereColumn('amount', '<=', 'balance')
    ->get();
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND amount=tax
    AND amount <= balance
```

### whereNull
- Takes one parameter `column`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNull('email_status')
    ->get();
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND email_status IS NULL
```

### whereNotNull
- Takes one parameter `column`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNotNull('email_status')
    ->get();
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND email_status IS NOT NULL
```

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
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND amount BETWEEN :0 AND :100
```

### whereNotBetween
- Same as `whereBetween()` method

```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNotBetween('amount', [0, 100])
    ->get();
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND amount NOT BETWEEN :0 AND :100
```

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
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND amount IN (:10, :20, :40, :100)
```

### whereNotIn
Same as `whereIn()` method
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->whereNotIn('amount', [10, 20, 40, 100])
    ->get();
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id AND amount NOT IN (:10, :20, :40, :100)
```

### groupBy
- Takes one param `$column`
```
$db->table('wallet')
    ->where('user_id', 10000001)
    ->groupBy('amount')
    ->get();
    
-- Query
SELECT * 
    FROM `wallet`
    WHERE user_id=:user_id GROUP BY amount
```

## Database Migration
- Similar to Laravel DB Migration `Just to make database table creation more easier`

| object name   |  Returns           |
|---------------|--------------------|
| create()      |  Used to create database table schema  |
| up()          |  used for commence migration `send table_schema to database` |
| drop()        |  used to drop table   |
| column()      |  used to drop `column` |

### Migration Table

- 1 Add path to migration class
```
use UltimateOrmDatabase\Migration\Migration;
```

### Run Migration

- 1 You need to pass in the `object name` as a param
    - This auto create folders/subfolder with read permission
        - The code above execute all files located in [root/database/migrations]
            - This will only create table that doesn't exist only

```
Migration::run('up');


Migration runned successfully on `2023_04_19_1681860618_user` 
Migration runned successfully on `2023_04_19_1681860618_user_wallet` 
```

### Create Database Schema 
- To create a php file or database schema
    - Takes param as `table name`

```
Migration::create('users_wallet');


Table `2023_04_19_1681860618_user_wallet` has been created successfully
```

### Drop Table
- Be careful as this will execute and drop all files table `located in the migration`

```
Migration::run('drop');
```

### Drop Column
- To Drop Column `takes two param`
    - This will drop the column available
```
Migration::run('column', 'column_name);
```


## toArray
- Takes one param as `array or object` `$data`
```
$db->toArray([]);

- This will convert all data into an array or arrays
```

## toObject
- Same as `toArray()` method
```
$db->toObject([]);

- This will convert all data into an array or objects
```

## Pagination
- Configuring Pagination

| key   | Data Type |  Description    |
|-------|-----------|-----------------|
| allow | `true` \| `false` |  Default is `false` Setting to true will allow the system use this setting allover the system app |
| class | string    | Css `selector` For pagination ul tag in the browser |
| span  | string    | Css `selector` For pagination Showing Span tags in the browser |
| view  | `bootstrap` \| `simple`   | Default is `bootstrap` - Supports either `boostrap` view or `simple` pagination design |
| first | string    | Change the letter of `First`
| last  | string    | Change the letter of `Last`
| next  | string    | Change the letter of `Next`
| prev  | string    | Change the letter of `Prev`
| showing | string    | Change the letter of `Prev`
| of    | string    | Change the letter `of`
| to    | string    | Change the letter `to`
| results | string    | Change the letter `results`

### Global Configuraton
- 1 Setup global pagination on ENV autostart `most preferred` method
```
AutoloadEnv::start([
    'allow' => true, 
    'prev'  => 'Prev Page', 
    'last'  => 'Last Page', 
    'next'  => 'Next Page', 
    'view'  => 'bootstrap',
    'class' => 'Custom-Class-Css-Selector', 
]);
```

- 2 Can be called same time initializing the DB 
```
$db = new DB([
    'allow' => true, 
    'prev'  => 'Prev Page', 
]);
```

- 3 Can also be called using the `$db->configurePagination` method
```
$db->configurePagination([
    'allow' => true, 
    'view'  => 'bootstrap',
    'class' => 'Custom-Class-Css-Selector', 
]);
```

### Paginate Query
```
$users = $db->table('users')
            ->paginate(40);

-- Query
SELECT * 
    FROM `users` 
    LIMIT 0, 40
```

### Get Pagination Data
```
$users->data
// This will return the pagination data objects
```

### Get Pagination Links
```
$users->pagination->links();
// This will return the view of pagination links
```

### Pagination Links Configuration
- You can directly configure pagination links
    - If `configurePagination()` `allow` is set to `true`
        - It'll override every other settings
```
$users->paginate->links([
    'first' => 'First Page',
    'last'  => 'Last Page',
    'prev'  => 'Previous Page',
    'next'  => 'Next Page',
])
```

### Get Pagination Showing of
```
$users->pagination->showing();

// This will create a span html element with text
<div>
    <span class='pagination-highlight'>
        Showing 0 to 40 of 500 results
    </span>
</div>
```

### Pagination Showing Configuration
- You can directly configure showing text directly
```
$users->paginate->showing([
    'showing'  => 'Showing',
    'to'       => 'To',
    'of'       => 'out of',
    'results'  => 'Results',
    'span'     => 'css-selector',
])

// This will change the span text to
<div>
    <span class='css-selector'>
        Showing 0 To 40 out of 500 Results
    </span>
</div>
```

## Get Database Query
- Get Database Query
```
$db->getQuery();
```

## Get Database Config Data
- Get Database Configuration data
```
$db->AppConfig();
```

## Get Database Connection
- Get Database Connection
```
$db->getConnection();
```

## Database Import
- You can use this class to import .sql into a database programatically

```
use UltimateOrmDatabase\DBImport;

$import = new DBImport();

// needs absolute path to database file
$response = $import->DatabaseImport('orm.sql');


- Status code
->response == 404 (Failed to read file or File does'nt exists
->response == 400 (Query to database error
->response == 200 (Success importing to database
```

## Update Env Variable
- You can use this class to import .sql into a database programatically

| Params        |  Description      |
|---------------|-------------------|
| key           |  ENV key          |
| value         |  ENV value        |
| allow_quote   |  `true` /| `false` - Default is true (Allow quotes within value)  |
| allow_space   | `true` /| `false`  - Default is false (Allow space between key and value)|

```
use UltimateOrmDatabase\Methods\OrmDotEnv;

OrmDotEnv::updateENV('DB_PASSWORD', 'newPassword');
OrmDotEnv::updateENV('APP_DEBUG', false);
OrmDotEnv::updateENV('DB_CHARSET', 'utf8', false);

Returns - Boolean
true|false
```

## Collation And Charset

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

## Extend DB Class
- You can as well extends the DB class and use along 
    - If inherited class must use a __construct, Then you must use `parent::__construct();`

```
use UltimateOrmDatabase\DB;

class PostClass extends DB{
    
    // needed only if the class has a construct
    // else ignore without adding
    public function __construct() {
        parent::__construct();
    }

    -- You now have access to the DB public instances
    public function getPost(){
        return $this->table('posts')
            ->select(['images', 'title', 'description', 'date'])
            ->get();
    }
}
```

## Error status

- On error returns `404` status code 
- On Database delete query `400` status code - `If successful - But no data was deleted`
- On success returns `200` status code

## Useful links

- If you love this PHP Library, you can [Buy Tame Developers a coffee](https://www.buymeacoffee.com/tamedevelopers)
- Link to Youtube Video Tutorial on usage will be available soon

