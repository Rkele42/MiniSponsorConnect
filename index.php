<?php
require_once __DIR__.'/app/db.php';

$p = $_GET['p'] ?? 'home';

$map = [
  'home'     => __DIR__.'/pages/home.php',
  'listings' => __DIR__.'/pages/listings.php',
  'login'    => __DIR__.'/pages/login.php',
  'register' => __DIR__.'/pages/register.php',
  'messages'=>__DIR__.'/pages/messages.php',
  'admin'     => __DIR__.'/pages/admin.php',
  'wallet'   => __DIR__.'/pages/wallet.php',
  'logout'  => __DIR__.'/pages/logout.php',
  'orders'=>__DIR__.'/pages/orders.php',
  'sales' =>__DIR__.'/pages/sales.php',
  'profile'  => __DIR__.'/pages/profile.php',   // << EKLENDİ
  'sell'     => __DIR__.'/pages/sell.php',
];

if ($p === 'health') { header('content-type:text/plain'); echo "ok"; exit; }

$file = $map[$p] ?? $map['home'];
require $file;
