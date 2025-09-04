<?php
    
use Tamedevelopers\Database\Auth;
use Tamedevelopers\Database\Capsule\AppManager;

include_once __DIR__ . "/../vendor/autoload.php";

// as long as we're not including the init.php file
// then we must boot into out app to start using package
// AppManager::bootLoader();


// 'woocommerce'

$admin = (new Auth)->guard('tb_admin');
$user = (new Auth)->guard('tb_user', 'woocommerce');

$data = ['email' => 'peter.blosom@gmail.com', 'status' => '1', 'password' => 'tagged'];

// $user->attempt($data);
// $user->login($user->user());
// $user->logout();

$web = tauth('admins');
$web->attempt(['email' => 'tamedevelopers@gmail.com', 'is_active' => '1', 'password' => 'admin']);

dd(
    $web,
    $web->check(),
    $user,
    $user->check(),
);

