<?php

use builder\Database\DB;

include_once __DIR__ . "/../vendor/autoload.php";


$database = new DB();


$wallets = $database->table('tb_wallet')
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

        <!-- showing 2 of total results -->
        <?= 
            $wallets->showing(); 
        ?>

        <div class="wallet-container">
            <?php foreach($wallets as $wallet) {?>
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

        <!-- pagination links -->
        <?= 
            $wallets->links();
        ?>
</body>
</html>
