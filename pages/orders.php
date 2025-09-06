<?php
require_once __DIR__.'/../app/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /?p=login'); exit; }

$pdo = rkd_pdo();
$rows = $pdo->prepare("
  SELECT o.*, l.title, u.username AS seller_name
  FROM orders o
  JOIN listings l ON l.id=o.listing_id
  JOIN users u ON u.id=o.seller_id
  WHERE o.buyer_id=?
  ORDER BY o.id DESC
");
$rows->execute([(int)$user['id']]);
$orders = $rows->fetchAll();

require __DIR__.'/../views/header.php';
?>
<div class="card">
  <h2>Siparişlerim</h2>
  <?php if (isset($_GET['ok'])): ?><div class="alert">Satın alma işlemi başarılı.</div><?php endif; ?>
  <?php if (isset($_GET['err'])): ?><div class="alert" style="border-color:#7f1d1d;background:#1f0f10;color:#fecaca">Hata: <?=htmlspecialchars($_GET['err'])?></div><?php endif; ?>
</div>
<?php if(!$orders): ?>
  <div class="card"><p>Henüz bir sipariş yok.</p></div>
<?php else: foreach($orders as $o): ?>
  <div class="card">
    <div class="row">
      <span class="badge">#<?= (int)$o['id'] ?></span>
      <span class="badge"><?= htmlspecialchars($o['status']) ?></span>
      <span class="badge">Satıcı: @<?= htmlspecialchars($o['seller_name']) ?></span>
      <span class="price"><?= number_format((float)$o['price'],2,',','.') ?> TL</span>
    </div>
    <h3 style="margin:.5rem 0 0.25rem"><?= htmlspecialchars($o['title']) ?></h3>
    <p class="help">Tarih: <?= htmlspecialchars($o['created_at']) ?></p>
  </div>
<?php endforeach; endif; ?>
<?php require __DIR__.'/../views/footer.php'; ?>
