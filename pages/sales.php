<?php
require __DIR__.'/../views/header.php';
if (!isset($_SESSION['user'])) { header('Location: /?p=login'); exit; }
$uid = (int)$_SESSION['user']['id'];
$rows = rkd_pdo()->prepare("
  SELECT o.*, l.title, l.image, b.username AS buyer_name
  FROM orders o
  JOIN listings l ON l.id=o.listing_id
  JOIN users b    ON b.id=o.buyer_id
  WHERE o.seller_id=?
  ORDER BY o.id DESC
");
$rows->execute([$uid]);
$rows = $rows->fetchAll();
?>
<div class="card"><h2>Satışlarım</h2></div>
<?php foreach($rows as $r): ?>
  <div class="card">
    <?php if(!empty($r['image'])): ?>
      <img src="<?=htmlspecialchars($r['image'])?>" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:12px;margin-bottom:10px">
    <?php endif; ?>
    <div class="row">
      <span class="badge">Alıcı: @<?=htmlspecialchars($r['buyer_name'])?></span>
      <span class="badge">Durum: <?=htmlspecialchars($r['status'])?></span>
    </div>
    <h3 style="margin:.5rem 0"><?=htmlspecialchars($r['title'])?></h3>
    <div class="row">
      <span class="price"><?=number_format((float)$r['price'],2,',','.')?> TL</span>
      <span class="badge"><?=htmlspecialchars($r['created_at'])?></span>
    </div>
  </div>
<?php endforeach; if(!$rows): ?>
  <div class="card">Henüz satış yok.</div>
<?php endif; ?>
<?php require __DIR__.'/../views/footer.php'; ?>
