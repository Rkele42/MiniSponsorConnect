<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* ------------------ Balance helpers ------------------ */
function ensure_balance(PDO $pdo, int $uid): void {
  $pdo->prepare('INSERT OR IGNORE INTO balances(user_id,amount) VALUES(?,0)')->execute([$uid]);
}
function get_balance(PDO $pdo, int $uid): float {
  ensure_balance($pdo,$uid);
  $st = $pdo->prepare('SELECT amount FROM balances WHERE user_id=?');
  $st->execute([$uid]);
  $r = $st->fetch();
  return (float)($r['amount'] ?? 0);
}
function add_balance(PDO $pdo, int $uid, float $delta): void {
  ensure_balance($pdo,$uid);
  $pdo->prepare('UPDATE balances SET amount = amount + ? WHERE user_id=?')->execute([$delta,$uid]);
}

/* ------------------ Top-up record helper ------------------ */
function record_topup(PDO $pdo, int $uid, float $amount, string $method='sim', ?string $ref=null): void {
  $pdo->prepare("INSERT INTO topups(user_id,amount,method,ref,status,created_at) VALUES(?,?,?,?, 'done', ?)")
      ->execute([$uid,$amount,$method,$ref,rkd_now()]);
}

/* ------------------ Purchase flow ------------------ */
/*
 * rkd_purchase:
 *  - buyer bakiyesinden price düş
 *  - seller bakiyesine (price - fee) ekle (fee = 10%)
 *  - orders tablosuna kayıt (status='paid')
 *  - dönen: order_id
 */
function rkd_purchase(PDO $pdo, int $buyer_id, int $listing_id): int {
  $pdo->beginTransaction();
  try {
    $q = $pdo->prepare("SELECT l.*, u.id AS seller_id FROM listings l JOIN users u ON u.id=l.user_id WHERE l.id=?");
    $q->execute([$listing_id]);
    $L = $q->fetch();
    if (!$L) throw new RuntimeException('İlan bulunamadı.');
    $price = (float)$L['price'];
    if ($price <= 0) throw new RuntimeException('Geçersiz fiyat.');
    $seller_id = (int)$L['seller_id'];
    if ($seller_id === $buyer_id) throw new RuntimeException('Kendi ilanınızı satın alamazsınız.');

    $bal = get_balance($pdo, $buyer_id);
    if ($bal < $price) throw new RuntimeException('Bakiyeniz yetersiz.');

    $fee = round($price * 0.10, 2);
    $net = round($price - $fee, 2);

    add_balance($pdo, $buyer_id, -$price);
    add_balance($pdo, $seller_id, +$net);

    $st = $pdo->prepare("INSERT INTO orders(buyer_id, listing_id, seller_id, price, status, created_at)
                         VALUES(?,?,?,?, 'paid', ?)");
    $st->execute([$buyer_id, $listing_id, $seller_id, $price, rkd_now()]);
    $order_id = (int)$pdo->lastInsertId();

    $pdo->commit();
    return $order_id;
  } catch(Throwable $e) {
    $pdo->rollBack();
    throw $e;
  }
}
