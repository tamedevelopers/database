<?php

use builder\Database\DB;

include_once __DIR__ . "/init.php";

autoload_register('tests/model');

// start env configuration
// env_start();
config_pagination([
    'allow' => true,
    'view'  => 'cursor',
    'first' => 'First',
]);

DB::connection('woocommerce', [
    'database' => 'packone',
    'username' => 'root',
    'password' => '',
]);


$database = DB::connection();
$wocoomerce = DB::connection('woocommerce');



// dd(
//     Wallet::table('wallet')
//         ->whereRaw('amount > ?', [400])
//         ->orWhereRaw('created_at <= ?', [strtotime('yesterday')])
//         ->sum('amount')

//     , $database
//     , $wocoomerce
// );


$wallets = $database->table('wallet')
                ->select(['*'])
                ->selectRaw('amount * ? as price_with_tax', [1.0825])
                ->where(DB::raw("YEAR(created_at) = 2023"))
                ->join('user', 'user.user_id', '=', 'wallet.user_id')
                ->whereBetween('amount', [100, 400])
                ->whereRaw('amount > ?', [100])
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('amount', '>', 50)->whereNull('note');
                    })->orWhere(function ($query) {
                        $query->where('tax', '>', 0.00)->whereNull('note');
                    });
                })
                ->first(4);




dd(
    $wallets,
);
