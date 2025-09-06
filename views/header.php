<?php
require_once __DIR__.'/../app/db.php';
require_once __DIR__.'/../app/helpers.php';
$pdo = rkd_pdo();
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$user = $_SESSION['user'] ?? null;

/* ziyaret kaydı */
$st = $pdo->prepare("INSERT INTO visits(path,ua,ip,created_at) VALUES(?,?,?,?)");
$st->execute([$_SERVER['REQUEST_URI'] ?? '/', $_SERVER['HTTP_USER_AGENT'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '', rkd_now()]);

$balance = 0.0;
$is_admin = false;
if ($user) {
  $balance = get_balance($pdo, (int)$user['id']);
  $q = $pdo->prepare('SELECT is_admin FROM users WHERE id=?');
  $q->execute([(int)$user['id']]);
  $is_admin = ((int)($q->fetch()['is_admin'] ?? 0) === 1);
}
?>
<!doctype html><html lang="tr"><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RKDİJİTAL</title>
<link rel="stylesheet" href="/assets/style.css">
<body>
<header class="header">
  <div class="row">
    <span class="brand">RKDİJİTAL</span>
    <nav class="nav row">
      <a href="/">Anasayfa</a>
      <a href="/?p=listings">İlanlar</a>
      <a href="/?p=messages">Mesajlar</a>
    </nav>
  </div>

  <div class="row" style="position:relative">
    <?php if($user): ?>
      <span class="badge">Bakiye: <?=number_format((float)$balance,2,',','.')?> TL</span>
      <?php if ($is_admin): ?>
        <a class="btn" href="/?p=admin" style="background:#0ea5e9;color:#001825">Admin</a>
      <?php endif; ?>

      <button id="userMenuBtn" class="btn" style="background:#0f1a2b;color:#e5e7eb;border:1px solid #243049;display:flex;align-items:center;gap:8px">
        <span style="font-weight:900">≡</span> @<?=htmlspecialchars($user['username'])?>
      </button>
      <div id="userMenu" class="dropdown" style="display:none;position:absolute;right:0;top:48px;background:#0b1220;border:1px solid #1f2937;border-radius:12px;min-width:220px;box-shadow:0 8px 24px rgba(0,0,0,.35);z-index:50">
        <a class="dropdown-item" href="/?p=profile"  style="display:block;padding:10px 14px;color:#e5e7eb">Profilim</a>
        <a class="dropdown-item" href="/?p=orders"   style="display:block;padding:10px 14px;color:#e5e7eb">Siparişlerim</a>
        <a class="dropdown-item" href="/?p=sales"    style="display:block;padding:10px 14px;color:#e5e7eb">Satışlarım</a>
        <a class="dropdown-item" href="/?p=sell"     style="display:block;padding:10px 14px;color:#e5e7eb">Satış Yap</a>
        <a class="dropdown-item" href="/?p=messages" style="display:block;padding:10px 14px;color:#e5e7eb">Mesajlarım</a>
        <a class="dropdown-item" href="/?p=wallet"   style="display:block;padding:10px 14px;color:#e5e7eb">Cüzdan</a>
        <div style="height:1px;background:#1f2937;margin:6px 0"></div>
        <a class="dropdown-item" href="/?p=logout"   style="display:block;padding:10px 14px;color:#e5e7eb">Çıkış</a>
      </div>
      <script>
        (function(){
          const btn=document.getElementById('userMenuBtn');
          const menu=document.getElementById('userMenu');
          document.addEventListener('click',e=>{
            if(btn && btn.contains(e.target)){
              menu.style.display = (menu.style.display==='none'||menu.style.display==='')?'block':'none';
            } else if(menu && !menu.contains(e.target)){
              menu.style.display='none';
            }
          });
        })();
      </script>
    <?php else: ?>
      <a class="btn" href="/?p=login">Giriş</a>
      <a class="btn" href="/?p=register">Kayıt Ol</a>
    <?php endif; ?>
  </div>
</header>
<main class="container">
