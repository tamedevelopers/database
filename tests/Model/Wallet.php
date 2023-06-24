<?php

use builder\Database\Model;

class Wallet extends Model{

    // defining a table name will override 
    // the default pluralization name handling
    protected $table = 'tb_wallet';

    public function getWallets()
    {
        return $this->where('amount', '>', 0)
                    ->join('tb_user', 'tb_user.user_id', '=', 'tb_wallet.user_id')
                    ->latest('date');
    }

}