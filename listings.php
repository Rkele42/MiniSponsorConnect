<?php
require __DIR__ . '/app/auth.php';
require __DIR__ . '/app/db.php';

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$q = trim($_GET['q'] ?? '');
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($q !== '') {
  $where = "WHERE l.title LIKE :kw OR l.description LIKE :kw";
  $params[':kw'] = '%'.$q.'%';
}

$sqlCount = "SELECT COUNT(*) FROM listings l $where";
$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$sqlList = "SELECT l.id,l.title,l.description,l.image,l.created_at,u.username
            FROM listings l JOIN users u ON u.id=l.user_id
            $where
            ORDER BY l.id DESC
            LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sqlList);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

require __DIR__ . '/app/header.php';
?>
<div class="card">
  <h1>İlanlar</h1>

  <form class="search" method="get" action="/listings.php">
    <input name="q" value="<?= htmlspecialchars($q,ENT_QUOTES,'UTF-8') ?>" placeholder="Ara: başlık veya açıklama...">
    <button class="btn btn-primary" type="submit">Ara</button>
    <?php if ($q !== ''): ?>
      <a class="btn" href="/listings.php">Temizle</a>
    <?php endif; ?>
  </form>

  <?php if ($total === 0): ?>
    <p class="muted">Sonuç bulunamadı.</p>
  <?php endif; ?>

  <?php foreach ($rows as $r): ?>
    <div class="card" style="margin-top:12px">
      <h3 style="margin:0 0 6px">
        <a href="/listing.php?id=<?= (int)$r['id'] ?>"><?= htmlspecialchars($r['title'],ENT_QUOTES,'UTF-8') ?></a>
      </h3>
      <p class="muted" style="margin:0 0 6px"><?= htmlspecialchars($r['description'],ENT_QUOTES,'UTF-8') ?></p>
      <?php if ($r['image']): ?><img src="/<?= htmlspecialchars($r['image'],ENT_QUOTES,'UTF-8') ?>" alt="" style="max-width:100%;border-radius:12px"><?php endif; ?>
      <p class="muted" style="margin:6px 0 0">Sahip: <?= htmlspecialchars($r['username'],ENT_QUOTES,'UTF-8') ?> • <?= htmlspecialchars($r['created_at'],ENT_QUOTES,'UTF-8') ?></p>
    </div>
  <?php endforeach; ?>

  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php $qp = $q!=='' ? '&q='.urlencode($q) : ''; ?>
    <?php if ($page > 1): ?>
      <a class="btn" href="/listings.php?page=<?= $page-1 ?><?= $qp ?>">⬅️ Önceki</a>
    <?php else: ?>
      <span class="btn">⬅️ Önceki</span>
    <?php endif; ?>

    <span class="btn current">Sayfa <?= $page ?>/<?= $totalPages ?> • Toplam <?= $total ?></span>

    <?php if ($page < $totalPages): ?>
      <a class="btn" href="/listings.php?page=<?= $page+1 ?><?= $qp ?>">Sonraki ➡️</a>
    <?php else: ?>
      <span class="btn">Sonraki ➡️</span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/app/footer.php'; ?>
