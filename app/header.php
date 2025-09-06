<?php require_once __DIR__ . '/auth.php'; $u = current_user(); ?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" href="/assets/style.css">
<title>Mini SponsorConnect</title>
</head><body>
<nav class="nav">
  <div class="brand"><a href="/" style="color:#eaf2ff">Mini SponsorConnect</a></div>
  <div class="actions">
    <?php if ($u): ?>
      <a class="btn" href="/listings.php">İlanlar</a>
      <a class="btn" href="/listings_my.php">Benim İlanlarım</a>
      <a class="btn btn-primary" href="/listing_add.php">İlan Ekle</a>
      <a class="btn" href="/dashboard.php">Panel</a>
      <a class="btn" href="/logout.php">Çıkış</a>
    <?php else: ?>
      <a class="btn" href="/login.php">Giriş</a>
      <a class="btn btn-primary" href="/register.php">Kayıt Ol</a>
    <?php endif; ?>
  </div>
</nav>
<div class="container">
