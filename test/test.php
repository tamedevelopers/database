<?php

use UltimateOrmDatabase\DB;
use UltimateOrmDatabase\Methods\OrmDotEnv;

include_once __DIR__ . "/../vendor/autoload.php";
include_once __DIR__ . "/init-configuration.php";


$model = new DB();

// configure pagination settings for entire application
$model->configurePagination([
    'allow' => true, 
    'view'  => 'bootstrap', // default is (bootstrap)
    'class' => 'Custom-UL__Class', //can add a custom css and style
]);

$wallets = $model->table('tb_wallet')
                ->where('amount', '>', 0)
                ->join('tb_user', 'tb_user.user_id', '=', 'tb_wallet.user_id')
                ->latest('date')
                ->paginate(2);
?>


<!DOCTYPE html>
<html>
    <head>
        <title>Wallet</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link href="style.css" rel="stylesheet" type="text/css">
    </head>
<body>

        <div class="wallet-container">
            <?php foreach($wallets->data as $wallet) {?>
                <div class="wallet-card">
                    <div class="user-name"><?= "{$wallet->first_name} {$wallet->last_name}" ?></div>
                    <div class="amount">$<?= number_format($wallet->amount, 2) ?></div>
                    <div class="section">
                        <label>Wallet ID:</label>
                        <span><?= $wallet->payment_id ?></span>
                    </div>
                    <div class="section">
                        <label>Note:</label>
                        <span><?= $wallet->note ?></span>
                    </div>
                    <div class="section">
                        <label>Card:</label>
                        <span>Visa **** **** **** 1234</span>
                    </div>
                    <div class="footer">Tran Date: <?= date('F d, Y', $wallet->date) ?></div>
                </div>
            <?php }?>
        </div>

        <!-- pagination -->
        <?= 
            $wallets->pagination->links();
        ?>
</body>
</html>
