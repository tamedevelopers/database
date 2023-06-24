<?php

use builder\Database\Model;

class User extends Model{
    
    // this model table name will become `users`
    // you can dump to always see table name
    
    public function getUsers()
    {
        return $this->where('active', 1)
                    ->join('tb_orders_payment', 'tb_orders_payment.user_id', '=', 'tb_user.user_id')
                    ->join('tb_wallet', 'tb_wallet.user_id', '=', 'tb_user.user_id')
                    ->limit(10)
                    ->get();
    }

}