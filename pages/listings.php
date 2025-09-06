<?php
require __DIR__.'/../views/header.php';
$pdo = rkd_pdo();
$rows = $pdo->query("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id ORDER BY l.id DESC")->fetchAll();
$user = $_SESSION['user'] ?? null;
?>
<div class="card"><h2>İlanlar</h2></div>
<?php foreach($rows as $r): ?>
  <div class="card">
    <?php if(!empty($r['image'])): ?>
      <img src="<?=htmlspecialchars($r['image'])?>" alt="" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:12px;margin-bottom:10px">
    <?php endif; ?>
    <div class="row">
      <span class="badge"><?=htmlspecialchars($r['category'])?></span>
      <span class="badge"><?=htmlspecialchars($r['subcategory'])?></span>
    </div>
    <h3 style="margin:.5rem 0 0.25rem"><?=htmlspecialchars($r['title'])?></h3>
    <p style="opacity:.9"><?=nl2br(htmlspecialchars($r['description']))?></p>
    <div class="row" style="gap:10px">
      <span class="price"><?=number_format((float)$r['price'],2,',','.')?> TL</span>
      <span class="badge">satıcı: @<?=htmlspecialchars($r['username'])?></span>
      <?php if($user && (int)$user['id'] !== (int)$r['user_id']): ?>
        <form method="post" action="/purchase.php" style="margin-left:auto">
          <input type="hidden" name="listing_id" value="<?= (int)$r['id'] ?>">
          <button class="btn" type="submit" style="background:#22c55e;color:#001b22;border:0">Satın Al</button>
        </form>
      <?php elseif(!$user): ?>
        <a class="btn" href="/?p=login" style="margin-left:auto">Giriş yap</a>
      <?php else: ?>
        <span class="badge" style="margin-left:auto">Kendi ilanın</span>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
<?php require __DIR__.'/../views/footer.php'; ?>
