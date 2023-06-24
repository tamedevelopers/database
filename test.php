<?php
// include_once __DIR__ . "/vendor/autoload.php";

use builder\Database\DB;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use builder\Database\Capsule\Forge;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;


include_once __DIR__ . "/init.php";
include_once __DIR__ . "/Post.php"; 

// start env configuration
// env_start();
// config_pagination([
//     'allow' => false,
//     'view'  => 'bootstrap',
//     'first' => 'First Test Page',
// ]);

// 

// $posts->getWallets();

// $other = DB::connection('woocommerce', [
//     'database' => 'packones',
//     'username' => 'root',
//     'password' => '',
// ])->getConnection();

$container = new Container;
$container->bind('db', function () {
    $capsule = new Capsule;

    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'packones',
        'username' => 'root',
        'password' => '',
        'database' => 'dbquery',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ]);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule->getDatabaseManager();
});
$db = $container->make('db');


$database = DB::connection();
$wocoomerce = DB::connection('woocommerce');

// dd(
//     $database,
//     $wocoomerce
// );

// $posts  = new Post();

// Example with CarbonPeriod
$startDate = Carbon::now()->subDays(7);
$endDate = Carbon::now();
$period = new CarbonPeriod($startDate, $endDate);


dd(
        $db->table('tb_wallet')
            // ->select($db->raw('count(*) as user_count, status'))
            // ->select('name', 'email as user_email')
            ->select('department', $db->raw('SUM(price) as total_sales'))
            // ->select(['first', 'amount'])
            ->select($db->raw('count(*) as user_count, status'))
            ->selectRaw('amount * ? < 400', [1.0825])
            ->orderByRaw('updated_at - created_at DESC')
            ->inRandomOrder()
            ->groupBy('department')
            ->groupByRaw('city, state')
            ->havingRaw('SUM(price) > ?', [2500])
            ->orderBy('department')
            // ->where('is_published', true)
            // ->join('contacts', 'users.id', '=', 'contacts.user_id')
            // ->joinWhere('orders', 'users.id', '=', 'orders.user_id')
            ->where(function ($query) {
                    // $query->whereRaw("date >= 1111")
                    //     ->select(['email'])
                    //     ->from('membership')
                    //     ->where('is_published', true)
                    //     ->orWhere('shipper_slug', '');
            })
            ->whereColumnIn('amount', [100, 210, 90])
            ->where([
                ['email_verified', 1],
                ['balanace', '>', 10]
            ])
            // ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
            // ->groupBy('department', 'xenxier')
            // ->havingRaw('SUM(price) > ?', [2500])
            // ->toSql()
            // ->get()
        // ->raw('cast(amount)')
        // ->min('amount')
        // ->toSql()

        // ::select([''])

        , $data = $database->table('table_users_wallet')
                ->select(DB::raw('SUM(price) as total_sales'), 'amount')
                ->selectRaw('amount * ? < 400 and date >= ?', [1.0825, time()])
                ->select('amount')
                ->select('department', $database->raw('SUM(price) as total_sales'))
                ->orderBy('department')
                ->orderByRaw('updated_at - created_at DESC')
                ->groupBy('department', 'menu')
                ->inRandomOrder()
                ->where('is_published', 'LiKE', 'true')
                ->where(function ($query) {
                    $query->from('clifform')
                            ->where('is_published')
                            ->select(['email'])
                            ->where('name', 'LiKE', '%Fred%')
                            ->whereNotNull('shipper_slug');
                })
                ->where('phone', '>', 10)
                ->join('contacts', 'users.id', '=', 'contacts.user_id')
                ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                ->crossJoin('orders', 'users.id', '=', 'orders.user_id')
                ->where('is_published', true)
                ->selectRaw(DB::raw('count(*) as user_count, status'), [])
                ->select(DB::raw('count(*) as user_count, status'))
                ->whereNull('email_status')
                ->whereNotNull('email_status')
                ->whereBetween('amount', [0, 100])
                ->having('price', '===', 2)
                ->havingRaw('SUM(price) > ?', [2500])
                ->limit(2)
                ->offset(25)
                ->whereNotBetween('amount', [0, 100])
                ->whereIn('amount', [10, 20, 40, 100])
                ->toSql()


        , $database
            ->table('wallet')
            ->where('id', 5)
            // ->whereBetween('amount', [0, 1000])
            // ->where(function ($query) {
            //     $query->where(function ($query) {
            //         $query->where('amount', '>', 750)->whereNull('note');
            //     })->orWhere(function ($query) {
            //         $query->where('tax', '>', 0.00)->whereNull('note');
            //     });
            // })
            ->insertOrIgnore([
                // 'user_id' => 616581394,
                'amount' => rand(100, 999),
                'payment_id' => substr(str_shuffle(md5(rand(100, 999))), 0, 10),
                'tax' => 0.0,
                // 'note' => 'new note info',
            ])


        // , Post::from('wallet')->find(3)

        // , $wocoomerce->table('woocommerce_table')

        // ->join('tb_user', 'tb_user.user_id', '=', 'tb_wallet.user_id')
        // , $wocoomerce->getTablePrefix()
                        // ->select(['first', 'amount'])
                        // ->where('f')
                        // ->where('f')
                        // // ->orWhere('phone', '>', 10)
                        // // ->whereColumn('phone', [100, 210, 90])
                        // ->where([
                        //     ['email_verified', 1],
                        //     ['balanace', '>', 10]
                        // ])


        // , Forge::flattenValue([
        //     [[11, 14], 1, 2, 3],
        //     [4, [6, 89]],
        //     7,
        //     [8, [94, 10]],
        // ])

);



exit();
$collections = $db->table('tb_wallet')
                    ->select(['amount', 'charge', 'note'])
                    ->random()
                    ->latest()
                    // ->where('id', '1323')
                    ->first('amount', 10, [
                        'user_id' => 6165813943,
                        // 'amount' => rand(100, 999),
                        // 'payment_id' => str_shuffle(substr(md5(rand(100, 999)), 0, 10)),
                        'date' => strtotime('now'),
                    ]);


dd(
    $collections
);

dd( 
    $collections,
    $collections->amount,
    (int) $collections['charge'],
    // $collections->isProxyAllowed,
    // $db->table('tb_lang')->limit(10)->get()->toSql(),
    // $db->tableExists('users'),
);


echo "Jsss";