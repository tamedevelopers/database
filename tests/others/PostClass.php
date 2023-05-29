<?php

use builder\Database\DB;


class PostClass extends DB{
    
    public function __construct() {
        // always remeber to add parent constructor before usage
        parent::__construct();

        // configure pagination settings for entire application
        $this->configPagination([
            'allow' => true, 
            'view'  => 'bootstrap', // default is (simple)
            'class' => 'Custom-Class', //can add a custom css and style
        ]);
    }

    public function getUsers()
    {
        $users = $this->table('tb_user')
                        ->random()
                        ->join('tb_orders_payment', 'tb_orders_payment.user_id', '=', 'tb_user.user_id')
                        ->join('tb_wallet', 'tb_wallet.user_id', '=', 'tb_user.user_id')
                        // ->join('tb_user_address', 'tb_user_address.user_id', '=', 'tb_user.user_id')
                        ->paginate(2);

        $this->dump(
            $users
        );
    }

    public function getWallets()
    {
        $wallets = $this->table('tb_wallet')
                        ->where('amount', '>', 0)
                        ->join('tb_user', 'tb_user.user_id', '=', 'tb_wallet.user_id')
                        ->latest('date')
                        ->paginate(2);

        $this->dump(
            $wallets
        );
    }

    // just sample data
    public function sample()
    {
        $this->dump(
            $this->dbConnection(), // database connection
            $this->dbDriver(), // `PDO` Driver
            $this->dbQuery(), // to get last query
        );
    }

}