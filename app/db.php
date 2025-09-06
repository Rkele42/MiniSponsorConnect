<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

const RKD_DB_PATH = __DIR__ . '/../db/rkdijital.sqlite';

function rkd_pdo(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $pdo = new PDO('sqlite:' . RKD_DB_PATH, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
function rkd_now(): string { return (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'); }

/* ---- BALANCE HELPERS (idempotent) ---- */
if (!function_exists('rkd_ensure_balance')) {
  function rkd_ensure_balance(PDO $pdo, int $uid): void {
    $pdo->prepare('INSERT OR IGNORE INTO balances(user_id,amount) VALUES(?,0)')
        ->execute([$uid]);
  }
}
if (!function_exists('rkd_add_balance')) {
  function rkd_add_balance(PDO $pdo, int $uid, float $delta): void {
    rkd_ensure_balance($pdo, $uid);
    $pdo->prepare('UPDATE balances SET amount = amount + ? WHERE user_id=?')
        ->execute([$delta, $uid]);
  }
}
