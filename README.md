# PHP ORM Database

[![Total Downloads](https://poser.pugx.org/peterson/php-orm-database/downloads)](https://packagist.org/packages/peterson/php-orm-database)
[![Latest Stable Version](https://poser.pugx.org/peterson/php-orm-database/version.png)](https://packagist.org/packages/peterson/php-orm-database)
[![License](https://poser.pugx.org/peterson/php-orm-database/license)](https://packagist.org/packages/peterson/php-orm-database)
[![Build Status](https://github.com/tamedevelopers/phpOrmDatabase/actions/workflows/php.yml/badge.svg)](https://github.com/tamedevelopers/phpOrmDatabase/actions)
[![Code Coverage](https://codecov.io/gh/peterson/php-orm-database/branch/2.2.x/graph/badge.svg)](https://codecov.io/gh/peterson/php-orm-database/branch/2.2.x)
[![Gitter](https://badges.gitter.im/peterson/php-orm-database.svg)](https://app.element.io/#/room/#php-orm-database:gitter.im)

## Inspiration

Having been introduced to learning Laravel Framework; Over the past yr(s), Coming back to vanilla PHP,
was pretty tough. So i decided to create a much more easier way of communicating with Database, using native `PHP PDO:: Driver`.


## Documentation

* [Requirements](#requirements)
* [Installation](#installation)
* [Instantiate](#instantiate)
* [Database Connection](#database-connection)
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
  * [Query](#query)
  * [Remove Tags](#remove-tags)
* [Fetching Data](#fetching-data)
    * [Get](#get)
    * [First](#first)
    * [First or Fail](#first-or-fail)
    * [Count](#count)
    * [Paginate](#paginate)
    * [Exist](#exists)
    * [Table Exist](#table-exist)
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
    * [Pagination Numbers](#pagination-numbers)
    * [Get Pagination](#get-pagination)
* [Clause](#clause)
  * [Raw](#raw)
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
  * [Create Table Schema](#create-table-schema)
  * [Run Migration](#run-migration)
  * [Drop Table](#drop-table)
  * [Drop Column](#drop-column)
* [Optimize Table](#optimize-table)
    * [Optimize](#optimize)
    * [Analize](#analize)
    * [Repair](#repair)
* [Get Database Query](#get-database-query)
* [Get Database Config Data](#get-database-config-data)
* [Get Database Connection](#get-database-connection)
* [Database Import](#database-import)
* [Update Env Variable](#update-env-variable)
* [Collation And Charset](#collation-and-charset)
* [Extend DB Class](#extend-db-class)
* [Helpers](#helpers)
* [Error Dump](#error-dump)
* [Error Status](#error-status)
* [Useful links](#useful-links)


## Requirements

- `>= php7.2+`

## Installation

Prior to installing `php-orm-database` get the [Composer](https://getcomposer.org) dependency manager for PHP because it'll simplify installation.

**Step 1** — update your `composer.json`:
```composer.json
"require": {
    "peterson/php-orm-database": "^4.0.1"
}
```

**Step 2** — run [Composer](https://getcomposer.org):
```update
composer update
```

**Or composer require**:
```
composer require peterson/php-orm-database
```

## Instantiate

**Step 1** — `Instantiate class using`:
```
require_once __DIR__ . '/vendor/autoload.php';

use builder\Database\DB;

$db = new DB();
```

## Database Connection

### Env Auto Loader  - `Most preferred`
- This will auto setup your entire application on a go! `.env auto setup`
    - By default you don't need to provide any path, since the Model use your project root [dir]
        - The below code should be called before using the database model

```
use builder\Database\AutoloadEnv;

AutoloadEnv::start([
    'path' => 'define root path or ignore'
]);

or - helpers function
env_start([
    'path' => 'define root path or ignore'
]);
```

### Direct DB Connection - `Supported but ![Recommended]`
<details><summary>Read more...</summary>

- When initializing the class
    - Pass an array as a param to the class
        - Why not recommended? Because you must use thesame varaible name `$db` everywhere in your app
            - Working but not supported, use `ENV autoloader` recommended
```
$db = new DB([
    'DB_USERNAME' => '',
    'DB_PASSWORD' => '',
    'DB_DATABASE' => '',
]);
```

- Or Better still `If you consider using it that way`
```
define("DATABASE_CONNECT", new DB([
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => '',
    'DB_DATABASE' => '',
]));

Now you have access to the CONSTANT anywhere in your app.

$db = DATABASE_CONNECT;

$db->table('users')
    ->limit(10),
    ->get();
```
</details>

## More Database Connection Keys
- All available connection keys
    - The DRIVER_NAME uses only `mysql`
        - No other connection type is supported for now.

| key               |  Type     |  Default Value        |
|-------------------|-----------|-----------------------|
| DRIVER_NAME       |  string   |  mysql                |
| APP_DEBUG         |  boolean  |  true                 |
| APP_DEBUG_BG      |  string   |  Default value is `default` and other color \| `main` \| `dark` \| `red` \| `blue` |
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
<details><summary>Read more...</summary>

- Same as `insert()` method
    - It returns an object of created data or `false` on error

```
$db->table('users')->insertOrIgnore([
    'user_id'    => 10000001,
    'first_name' => 'Alfred',
]);
```
</details>

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
<details><summary>Read more...</summary>

- Same as `update()` method
    - Returns an `int` numbers of affected rows or `0` on error

```
$db->table('users')
    ->where('user_id', 10000001)
    ->updateOrIgnore([
        'first_name' => 'Alfred C.',
    ]);
```
</details>

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
```

- You can also pass in a second or third parameter to update additional columns
```
$db->table('users')
    ->where('user_id', 10000001)
    ->increment('wallet_bal', 100.23, [
        'first_name' => 'F. Peterson',
        'status'     => 1,
    ]);

-- Query
UPDATE `users` 
    SET wallet_bal=wallet_bal + :wallet_bal, first_name=:first_name, status=:status
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

### Query
<details><summary>Read more...</summary>

- Allows you to use direct `SQL query syntax`

- 1 usage
```
$db->query('SHOW COLUMNS FROM users')->get();
$db->query('DROP TABLE users')->execute();
```

- 2 usage
```
$db->query('SELECT count(*) FROM users WHERE status=:status');
$db->bind('status', 1);
$db->get();

-- Query
SELECT count(*) FROM users WHERE status=:status
```
</details>

### Remove Tags
- Takes one param as `bool` Default is `false`
    - Helps against `XSS attacks` 
        - By default we did not handle `XSS attacks`. As we assume this should be done by `Forms Validation` before sending to Database
            -> Applies to `insert` `update` `increment` `decrement` methods.

- 1 usage
```
$db->table('post')
    ->removeTags(true)
    ->insert([
        'description' => "<script> alert(2); console.log('Blossom');</script>",
        'user_id' => 
    ])

- If param set to true, then this will allow all possible tags
- If false, it will allow few supported HTML5 tags
```

## Fetching Data

| object name   |  Returns           |
|---------------|--------------------|
| get()         |  array of objects  |
| first()       |  object            |
| firstOrFail() |  object or exit with 404 status   |
| count()       |  int               |
| paginate()    |  array of objects  |
| exists()      |  boolean `true` \| `false` |
| tableExist()  |  boolean `true` \| `false` |

### GET
```
$db->table('users')->get();

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
$users = $db->table('users')
            ->paginate(40);


$users // this will return the data objects
$users->links() // this will return the paginations links view
$users->showing() // Display items of total results


-- Query
SELECT * FROM `users` 
    LIMIT 0, 40 
```

### Exists
```
$db->table('users')
    ->where('email', 'email@gmail.com')
    ->orWhere('name', 'Mandison')
    ->exists();

-- Query
SELECT EXISTS(SELECT 1 FROM `users` WHERE email=:email OR name=:name) as `exists`
```

### Table Exist
- Takes param as `string` `$table_name`
```
$db->tableExist('users');
```

## Collections
- You can directly use `methods` of `Collections Instance` on any of the below
    - All the below `methods` are received by Collection `class`
    1. get()
    2. first()
    3. firstOrFail()
    4. insert()
    5. insertOrIgnore()



### Collection Methods
|    Methods        |          Description                      |
|-------------------|-------------------------------------------|
|  getAttributes()  |  `array` Returns an array of data         |
|  getOriginal()    |  `object` Returns an object of data       |
|  isEmpty()        |  `boolean` `true \| false` If data is empty |
|  isNotEmpty()     |  `opposite` of `->isEmpty()`              |
|  count()          |  `int` count data in items collection     |
|  toArray()        |  `array` Convert items to array           |
|  toObject()       |  `object` Convert items to object         |
|  toJson()         |  `string` Convert items to json           |
|  toSql()          |  `string` Sql Query String without execution |
 

### Collection Usage
<details><summary>Read more...</summary>

- Takes one param as `mixed` data
    - Convert data into an array or arrays

```
$user = $db->tableExist('users')
            ->first();

$user->first_name
$user['first_name']

$user->toArray()
$user->getAttributes()
```

```
$users = $db->tableExist('users')
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
</details>

## Pagination
- Configuring Pagination

| key       | Data Type               |  Description    |
|-----------|-------------------------|-----------------|
| allow     | `true` \| `false`       | Default `false` Setting to true will allow the system use this settings across app|
| class     | string                  | Css `selector` For pagination ul tag in the browser |
| span      | string                  | Default `.pagination-highlight` Css `selector` For pagination Showing Span tags in the browser |
| view      | `bootstrap` \| `simple` | Default `simple` - For pagination design |
| first     | string                  | Change the letter `First` |
| last      | string                  | Change the letter `Last` |
| next      | string                  | Change the letter `Next` |
| prev      | string                  | Change the letter `Prev` |
| showing   | string                  | Change the letter `Showing` |
| of        | string                  | Change the letter `of`      |
| results   | string                  | Change the letter `results` |


### Global Configuration 
- 1 Setup global pagination on ENV autostart `most preferred` method
```
AutoloadEnv::configurePagination([
    'allow' => true, 
    'prev'  => 'Prev Page', 
    'last'  => 'Last Page', 
    'next'  => 'Next Page', 
    'view'  => 'bootstrap',
    'class' => 'Custom-Class-Css-Selector', 
]);
```

- or Helpers Function
```
configure_pagination([
    'allow' => true,
]);
```

<details><summary>Read more...</summary>
- 2 Can also be called using the `$db->configurePagination` method
```
$db->configurePagination([
    'allow' => true, 
    'view'  => 'bootstrap',
]);
```

- 3 Can be called same time initializing the DB 
```
$db = new DB([
    'allow' => true, 
    'prev'  => 'Prev Page', 
]);
```
</details>

### Pagination Query
```
$users = $db->table('users')->paginate(40);

-- Query
SELECT * 
    FROM `users` 
    LIMIT 0, 40
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
    - Note: If `configurePagination()` `allow` is set to `true`
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
<span class='pagination-highlight'>
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

### Pagination Numbers
- Page numbering `starts counting from 1`
    - This will format each numbers of data according to it's possition

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

### Raw
- Allows you to use direct raw `SQL query syntax`

```
$date = strtotime('next week');


$db->table("tb_wallet")
    ->raw("date >= $date")
    ->raw("NOW() > created_at")
    ->raw("YEAR(created_at) = 2022")
    ->where('email', 'email@gmail.com')
    ->limit(10)
    ->random()
    ->get();


-- Query
SELECT * FROM `tb_wallet` 
        WHERE date >= 1681178855 
        AND NOW() > created_at 
        AND YEAR(created_at) = 2022
        AND email=:email
        ORDER BY RAND() LIMIT 10
```

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
<details><summary>Read more...</summary>
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
</details>


### orderByRaw
<details><summary>Read more...</summary>
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
</details>


### Latest
<details><summary>Read more...</summary>

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
</details>


### Oldest
<details><summary>Read more...</summary>

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
</details>

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

-- Query
SELECT * 
    FROM `wallet`
    LIMIT 10
```

### offset
<details><summary>Read more...</summary>

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
</details>

### join
<details><summary>Read more...</summary>

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
</details>

### leftJoin
<details><summary>Read more...</summary>

- Same as `join`
```
$db->table('wallet')
    ->leftJoin('users', 'users.user_id', '=', 'wallet.user_id')
    ->get();

SELECT * 
    FROM `wallet`
    LEFT JOIN `users` ON users.user_id = wallet.user_id
```
</details>

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
<details><summary>Read more...</summary>

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
</details>

### whereColumn
<details><summary>Read more...</summary>

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
</details>

### whereNull
<details><summary>Read more...</summary>

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
</details>

### whereNotNull
<details><summary>Read more...</summary>

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
</details>

### whereBetween
<details><summary>Read more...</summary>

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
</details>

### whereNotBetween
<details><summary>Read more...</summary>

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
</details>

### whereIn
<details><summary>Read more...</summary>

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
</details>

### whereNotIn
<details><summary>Read more...</summary>

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
</details>

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
| create()      |  Create table schema  |
| run()         |  Begin migration `up` \| `drop` \| `column` |

```
use builder\Database\Migrations\Migration;
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
![Sample Session Schema](https://raw.githubusercontent.com/tamedevelopers/UltimateOrmDatabase/main/sessions.png)

### Run Migration
- You need to pass in `up` as a param
    - This auto create folders/subfolder with read permission
        - The code above execute all files located in [root/database/migrations]

```
Migration::run('up');


Migration runned successfully on `2023_04_19_1681860618_user` 
Migration runned successfully on `2023_04_19_1681860618_user_wallet` 
```

### Drop Table
<details><summary>Read more...</summary>

- Be careful as this will execute and drop all files table `located in the migration`

```
Migration::run('drop');
```
</details>

### Drop Column
<details><summary>Read more...</summary>

- To Drop Column `takes two param`
    - This will drop the column available
```
Migration::run('column', 'column_name);
```
</details>

## Optimize-table 
- Database table optimization

### Optimize
<details><summary>Read more...</summary>

- Optimize Multiple Tables
    - Takes a param as an `array` table_name
        - This will automatically `Analize` and `Repair` each tables
        
```
$db->optimize(['tb_wallet', 'tb_user']);
```
</details>

### Analize
<details><summary>Read more...</summary>

- Analize Single Table
    - Takes a param as an `string` table_name

```
$db->analize('tb_wallet');
```
</details>

### Repair
<details><summary>Read more...</summary>

- Repair Single Table
    - Takes a param as an `string` table_name

```
$db->repair('tb_wallet');
```
</details>

## Get Database Query

| object            | Helpers      |
|-------------------|--------------|
| $db->getQuery()   | get_query()  |


## Get Database Config Data

| object            | Helpers      |
|-------------------|--------------|
| $db->AppConfig()  | app_config() |


## Get Database Connection
| object                | Helpers      |
|-----------------------|--------------|
| $db->getConnection()  | get_connection() |

## Database Import
<details><summary>Read more...</summary>

- You can use this class to import .sql into a database programatically

```
use builder\Database\DBImport;

$import = new DBImport();

// needs absolute path to database file
$response = $import->DatabaseImport('orm.sql');

- Status code
->response == 404 (Failed to read file or File does'nt exists
->response == 400 (Query to database error
->response == 200 (Success importing to database
```
</details>

## Update Env Variable
- You can use this class to import .sql into a database programatically

| Params        |  Description      |
|---------------|-------------------|
| key           |  ENV key          |
| value         |  ENV value        |
| allow_quote   |  `true` \| `false` - Default is true (Allow quotes within value)  |
| allow_space   | `true` \| `false`  - Default is false (Allow space between key and value)|

```
use builder\Database\Methods\OrmDotEnv;

OrmDotEnv::updateENV('DB_PASSWORD', 'newPassword');
OrmDotEnv::updateENV('APP_DEBUG', false);
OrmDotEnv::updateENV('DB_CHARSET', 'utf8', false);

or
dot_env()->updateENV('DB_CHARSET', 'utf8', false);

Returns - Boolean
true|false
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

## Extend DB Class
<details><summary>Read more...</summary>

- You can as well extends the DB class and use along 
    - If inherited class must use a __construct, Then you must use `parent::__construct();`
```
use builder\Database\DB;

class PostClass extends DB{
    
    // needed only if the class has a construct
    // else ignore without adding
    public function __construct() {
        parent::__construct();
    }

    -- You now have access to the DB public instances
    public function getPost(){
        return $this->table('posts')
            ->select(['images', 'title', 'description'])
            ->get();
    }
}
```
</details>

## Helpers

| function                  | Description                       |
|---------------------------|-----------------------------------|
| base_dir()                | Return `server` base directory    |
| db()                      | Return instance of `new DB($options)` class   |
| db_exec()                 | Return instance of `(new MySqlExec)` class    |
| import()                  | Return instance of `(new DBImport)` class     |
| migration()               | Return instance of `(new Migration)` class    |
| schema()                  | Return instance of `(new Schema)` class       |
| dot_env()                 | Return instance of `(new OrmDotEnv)` class    |
| autoload_env()            | Return instance of `(new AutoloadEnv)` class  |
| env_start()               | Same as `AutoloadEnv::start()`    |
| config_database()         | Same as `Direct DB Connection` get access to `DATABASE_CONNECTION` Constant   |
| configure_pagination()    | Same as `$db->configurePagination()` or `AutoloadEnv::configurePagination`    |
| app_config()              | Same as `$db->AppConfig()`        |
| get_connection()          | Same as `$db->getConnection()`    |
| get_app_data()            | Get `path` `database` & `pagination` info |
| get_query()               | Same as `$db->getQuery()`         |
| to_array()                | `array` Convert items to array    |
| to_object()               | `object` Convert items to object  |
| to_json()                 | `string` Convert items to json    |

## Error Dump

| function  | Description     |
|-----------|-----------------|
| ddump     | Custom made error dump  |
| dump      | Dump error handling |
| dd        | Dump and Die - Error handling |


## Error Status

- On error returns `404` status code 
- On success returns `200` status code

## Useful Links

- @author Fredrick Peterson (Tame Developers)   
- [Lightweight - PHP ORM Database](https://github.com/tamedevelopers/phpOrmDatabase)
- If you love this PHP Library, you can [Buy Tame Developers a coffee](https://www.buymeacoffee.com/tamedevelopers)
- Udemy Course on Usage [Coming Soon]()

