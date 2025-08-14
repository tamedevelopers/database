<?php

use Tamedevelopers\Database\Model;

class Admin extends Model{
    
    // this model table name will become `users`
    // you can dump to always see table name
    
    public function getUsers()
    {
        dd(
            $this->getTableName(),
            $this->average('user_id')
        );
        
        return $this->where('active', 1)
                    ->join('tb_orders_payment', 'tb_orders_payment.user_id', '=', 'tb_user.user_id')
                    ->join('tb_wallet', 'tb_wallet.user_id', '=', 'tb_user.user_id')
                    // ->limit(10)
                    ->take(10)
                    ->get();
    }

}