<?php
require __DIR__ . '/app/auth.php';
require __DIR__ . '/app/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT l.id,l.title,l.description,l.image,l.created_at,u.username,u.id AS owner_id
                       FROM listings l JOIN users u ON u.id=l.user_id WHERE l.id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) { http_response_code(404); echo "İlan bulunamadı"; exit; }

$me = current_user();
require __DIR__ . '/app/header.php';
?>
<div class="card">
  <h1><?= htmlspecialchars($item['title'],ENT_QUOTES,'UTF-8') ?></h1>
  <p class="muted">Sahip: <?= htmlspecialchars($item['username'],ENT_QUOTES,'UTF-8') ?> •
    <?= htmlspecialchars($item['created_at'],ENT_QUOTES,'UTF-8') ?></p>

  <?php if ($item['image']): ?>
    <img src="/<?= htmlspecialchars($item['image'],ENT_QUOTES,'UTF-8') ?>" alt="İlan görseli">
  <?php endif; ?>

  <div class="card" style="margin-top:14px">
    <p style="white-space:pre-wrap"><?= htmlspecialchars($item['description'],ENT_QUOTES,'UTF-8') ?></p>
  </div>

  <p style="margin-top:14px">
    <a class="btn" href="/listings.php">⬅️ İlanlara Dön</a>
    <?php if ($me && (int)$me['id'] === (int)$item['owner_id']): ?>
      <a class="btn" href="/listings_my.php">👤 Benim İlanlarım</a>
    <?php endif; ?>
  </p>
</div>
<?php require __DIR__ . '/app/footer.php'; ?>
