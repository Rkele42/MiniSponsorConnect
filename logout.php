<?php
require __DIR__ . '/app/auth.php';
logout();
header('Location: /login.php');
exit;
