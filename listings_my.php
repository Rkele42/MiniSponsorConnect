<?php
require __DIR__ . '/app/auth.php';
require_login();
require __DIR__ . '/app/db.php';
$u = current_user();

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare('SELECT COUNT(*) FROM listings WHERE user_id = ?');
$stmt->execute([$u['id']]);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare('SELECT id,title,description,image,created_at
                       FROM listings WHERE user_id = ?
                       ORDER BY id DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute([$u['id']]);
$rows = $stmt->fetchAll();

require __DIR__ . '/app/header.php';
?>
<div class="card">
  <h1>Benim İlanlarım</h1>
  <?php foreach ($rows as $r): ?>
    <div class="card" style="margin-top:12px">
      <h3 style="margin:0 0 6px">
        <a href="/listing.php?id=<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['title'],ENT_QUOTES,'UTF-8') ?></a>
      </h3>
      <p class="muted" style="margin:0 0 6px"><?= htmlspecialchars($r['description'],ENT_QUOTES,'UTF-8') ?></p>
      <?php if ($r['image']): ?><img src="/<?= htmlspecialchars($r['image'],ENT_QUOTES,'UTF-8') ?>" alt="" style="max-width:100%;border-radius:12px"><?php endif; ?>
      <p class="muted" style="margin:6px 0 0"><?= htmlspecialchars($r['created_at'],ENT_QUOTES,'UTF-8') ?></p>
      <p style="margin-top:10px">
        <a class="btn" href="/listing_delete.php?id=<?= (int)$r['id'] ?>" onclick="return confirm('Silinsin mi?')">Sil</a>
      </p>
    </div>
  <?php endforeach; ?>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a class="btn" href="/listings_my.php?page=<?= $page-1 ?>">⬅️ Önceki</a>
    <?php else: ?>
      <span class="btn">⬅️ Önceki</span>
    <?php endif; ?>

    <span class="btn current">Sayfa <?= $page ?>/<?= $totalPages ?></span>

    <?php if ($page < $totalPages): ?>
      <a class="btn" href="/listings_my.php?page=<?= $page+1 ?>">Sonraki ➡️</a>
    <?php else: ?>
      <span class="btn">Sonraki ➡️</span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/app/footer.php'; ?>
