<?php

use Tamedevelopers\Database\DB;

include_once __DIR__ . "/../vendor/autoload.php";


$database = DB::connection();

config_pagination([
    'allow' => true,
    'view' => 'cursor' //bootstrap|loading|cursor| simple[default]
]);

$wallets = $database->table('wallet')
                    ->where('amount', '>', 0)
                    ->join('user', 'user.user_id', '=', 'wallet.user_id')
                    ->latest('date')
                    ->paginate(3);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Wallet</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="style.css" rel="stylesheet" type="text/css">
    <style>
        .wallet-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px; /* spacing between cards */
        }

        .wallet-card {
            flex: 0 0 calc(33.333% - 20px); /* 3 per row, minus gap */
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .wallet-card .user-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .wallet-card .amount {
            font-size: 16px;
            color: #28a745;
            margin-bottom: 10px;
        }

        .wallet-card .section {
            margin-bottom: 8px;
        }

        .wallet-card .section label {
            font-weight: bold;
            margin-right: 6px;
        }

        .wallet-card .footer {
            font-size: 13px;
            color: #666;
            margin-top: 10px;
        }

        /* Responsive: 2 per row on tablets, 1 per row on mobile */
        @media (max-width: 992px) {
            .wallet-card {
                flex: 0 0 calc(50% - 20px);
            }
        }

        @media (max-width: 576px) {
            .wallet-card {
                flex: 0 0 100%;
            }
        }
    </style>
</head>
<body>
    <!-- showing 2 of total results -->
    <div style="text-align: center; padding: 30px 0 20px">
        <?= $wallets->showing() ?>
    </div>

    <div data-pagination-content>
        <div class="wallet-container" data-pagination-append>
            <?php foreach($wallets as $wallet) {?>
                <div class="wallet-card">
                    <div class="user-name">
                        <?= "({$wallet->numbers()})" ?>
                        <?= "{$wallet->first_name} {$wallet->last_name}" ?>
                    </div>
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
                    <div class="footer">
                        Tran Date: <?= TameTime()->format(null, $wallet->date) ?>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>

    <!-- pagination links -->
    <div>
        <?= $wallets->links(); ?>
    </div>
</body>
</html>
