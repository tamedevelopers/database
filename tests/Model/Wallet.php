<?php

use builder\Database\Model;

class Wallet extends Model{


    /**
     * The table associated with the model.
     * 
     * @var string|null
     * 
     * Now we have override the systems default model name of `wallets`
     * to `tb_wallet`
     */
    protected $table = 'tb_wallet';


    public function getWallets()
    {
        return $this->where('amount', '>', 0)
                    ->join('tb_user', 'tb_user.user_id', '=', 'tb_wallet.user_id')
                    ->latest('date');
    }

}