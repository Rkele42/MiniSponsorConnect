<?php
declare(strict_types=1);
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /?p=login'); exit; }

$listing_id = (int)($_POST['listing_id'] ?? 0);
if ($listing_id <= 0) { header('Location:/?p=orders&err=' . urlencode('Geçersiz istek')); exit; }

$pdo = rkd_pdo();

try {
  $order_id = rkd_purchase($pdo, (int)$user['id'], $listing_id);
  header('Location: /?p=orders&ok=1');
  exit;
} catch (Throwable $e) {
  header('Location: /?p=orders&err=' . urlencode($e->getMessage()));
  exit;
}
