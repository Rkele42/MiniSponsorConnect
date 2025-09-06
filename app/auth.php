<?php
declare(strict_types=1);
require_once __DIR__.'/db.php';

function rkd_user_by_username(PDO $pdo, string $u): ?array {
  $st = $pdo->prepare('SELECT id,username,passhash,is_admin FROM users WHERE username=?');
  $st->execute([$u]);
  $row = $st->fetch();
  return $row ?: null;
}

function rkd_register(string $username, string $password, bool $admin=false): array {
  $pdo = rkd_pdo();
  $username = trim($username);
  if (strlen($username) < 3) throw new RuntimeException('Kullanıcı adı en az 3 karakter olmalı.');
  if (strlen($password) < 6) throw new RuntimeException('Şifre en az 6 karakter olmalı.');
  if (rkd_user_by_username($pdo, $username)) throw new RuntimeException('Bu kullanıcı adı zaten alınmış.');

  $pdo->prepare('INSERT INTO users(username,passhash,is_admin,created_at) VALUES(?,?,?,?)')
      ->execute([$username, password_hash($password, PASSWORD_BCRYPT), $admin?1:0, rkd_now()]);
  $uid = (int)$pdo->lastInsertId();
  $pdo->prepare('INSERT OR IGNORE INTO balances(user_id,amount) VALUES(?,0)')->execute([$uid]);

  return ['id'=>$uid,'username'=>$username,'is_admin'=>$admin?1:0];
}

function rkd_login(string $username, string $password): array {
  $pdo = rkd_pdo();
  $u = rkd_user_by_username($pdo, trim($username));
  if (!$u || !password_verify($password, $u['passhash'])) {
    throw new RuntimeException('Kullanıcı adı veya şifre hatalı.');
  }
  $_SESSION['user'] = ['id'=>(int)$u['id'],'username'=>$u['username'],'is_admin'=>(int)$u['is_admin']];
  return $_SESSION['user'];
}

function rkd_logout(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
  }
  session_destroy();
  header('Location: /'); exit;
}
