set -e
mkdir -p app assets views pages db uploads

# --- app/db.php: PDO bağlantısı (SQLite) ---
cat > app/db.php <<'PHP'
<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

const RKD_DB_PATH = __DIR__ . '/../db/rkdijital.sqlite';

function rkd_pdo(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $pdo = new PDO('sqlite:' . RKD_DB_PATH, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
function rkd_now(): string { return (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'); }
PHP

# --- app/init_db.php: tablo oluşturma ve varsayılan veriler ---
cat > app/init_db.php <<'PHP'
<?php
require __DIR__.'/db.php';
$pdo = rkd_pdo();

$pdo->exec("PRAGMA foreign_keys = ON");

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  passhash TEXT NOT NULL,
  is_admin INTEGER NOT NULL DEFAULT 0,
  created_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS balances(
  user_id INTEGER PRIMARY KEY,
  amount REAL NOT NULL DEFAULT 0,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS listings(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  category TEXT NOT NULL,
  subcategory TEXT NOT NULL,
  title TEXT NOT NULL,
  description TEXT,
  price REAL NOT NULL,
  featured TEXT DEFAULT NULL, /* null|standart|parlak|çerçeve|rozet|vitrin */
  created_at TEXT NOT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS orders(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  buyer_id INTEGER NOT NULL,
  listing_id INTEGER NOT NULL,
  seller_id INTEGER NOT NULL,
  price REAL NOT NULL,
  status TEXT NOT NULL DEFAULT 'pending', /* pending|paid|delivered|refunded|cancelled */
  created_at TEXT NOT NULL,
  FOREIGN KEY(buyer_id) REFERENCES users(id),
  FOREIGN KEY(seller_id) REFERENCES users(id),
  FOREIGN KEY(listing_id) REFERENCES listings(id)
);
CREATE TABLE IF NOT EXISTS messages(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  from_id INTEGER NOT NULL,
  to_id INTEGER NOT NULL,
  body TEXT NOT NULL,
  created_at TEXT NOT NULL,
  FOREIGN KEY(from_id) REFERENCES users(id),
  FOREIGN KEY(to_id) REFERENCES users(id)
);
CREATE TABLE IF NOT EXISTS visits(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  path TEXT NOT NULL,
  ua TEXT,
  ip TEXT,
  created_at TEXT NOT NULL
);");

# örnek kullanıcılar
$now = rkd_now();
$pdo->prepare("INSERT OR IGNORE INTO users(username,passhash,is_admin,created_at) VALUES
  ('rkeles', :p1, 1, :now),
  ('demo',   :p2, 0, :now)")->execute([
    ':p1'=>password_hash('admin123', PASSWORD_BCRYPT),
    ':p2'=>password_hash('demo123', PASSWORD_BCRYPT),
    ':now'=>$now,
  ]);

# örnek ilan
$uid = (int)$pdo->query("SELECT id FROM users WHERE username='rkeles'")->fetchColumn();
$pdo->prepare("INSERT INTO listings(user_id,category,subcategory,title,description,price,featured,created_at)
VALUES(?,?,?,?,?,?,?,?)")->execute([
  $uid,'Influencer Marketing','Instagram İşbirliği',
  'Deneme İlanı','Örnek açıklama', 20.0,'standart',$now
]);

# bakiyeler
$pdo->prepare("INSERT OR IGNORE INTO balances(user_id,amount) VALUES(?,0),(?,0)")
    ->execute([$uid, (int)$pdo->query("SELECT id FROM users WHERE username='demo'")->fetchColumn()]);

echo \"DB hazır: \".RKD_DB_PATH.PHP_EOL;
PHP

# --- assets/style.css: minik koyu tema ---
cat > assets/style.css <<'CSS'
:root { --bg:#0f172a; --panel:#111827; --text:#e5e7eb; --muted:#9ca3af; --accent:#06b6d4; --good:#22c55e; }
*{box-sizing:border-box} body{margin:0;font:16px/1.4 system-ui;background:var(--bg);color:var(--text)}
a{color:var(--accent);text-decoration:none}
.header{display:flex;gap:12px;align-items:center;justify-content:space-between;background:#0b1220;padding:12px 16px;border-bottom:1px solid #182036;position:sticky;top:0}
.brand{font-weight:800;letter-spacing:.3px}
.nav a{padding:8px 12px;border-radius:10px;background:#0f1a2b}
.container{max-width:980px;margin:18px auto;padding:0 16px}
.card{background:var(--panel);border:1px solid #1f2937;border-radius:16px;padding:16px;margin-bottom:16px}
.btn{background:#0ea5e9;border:none;color:#001825;padding:8px 12px;border-radius:10px;font-weight:700}
.price{background:#67e8f9;color:#001b22;padding:6px 10px;border-radius:10px;font-weight:800}
.row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
.badge{padding:4px 8px;border-radius:999px;background:#0f1a2b;color:var(--muted);font-size:.85rem}
CSS

# --- views/header/footer ---
cat > views/header.php <<'PHP'
<?php
require_once __DIR__.'/../app/db.php';
$pdo = rkd_pdo();
# ziyaret kaydı
$pdo->prepare("INSERT INTO visits(path,ua,ip,created_at) VALUES(?,?,?,?)")
    ->execute([$_SERVER['REQUEST_URI'] ?? '/', $_SERVER['HTTP_USER_AGENT'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '', rkd_now()]);
$user = $_SESSION['user'] ?? null;
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
  <div class="row">
    <?php if($user): ?>
      <span class="badge">@<?=htmlspecialchars($user['username'])?></span>
      <a class="btn" href="/?p=logout">Çıkış</a>
    <?php else: ?>
      <a class="btn" href="/?p=login">Giriş</a>
      <a class="btn" href="/?p=register">Kayıt Ol</a>
    <?php endif; ?>
  </div>
</header>
<main class="container">
PHP

cat > views/footer.php <<'PHP'
</main>
<footer class="container" style="opacity:.7;padding:24px 0">
  <div>© <?=date('Y')?> RKDİJİTAL • Basit iskelet</div>
</footer>
</body></html>
PHP

# --- pages/home.php ---
cat > pages/home.php <<'PHP'
<?php require __DIR__.'/../views/header.php'; ?>
<div class="card">
  <h2>Hoş geldiniz 👋</h2>
  <p>Bu, RKDİJİTAL için minimal başlangıç iskeletidir. Soldaki menüden ilanlara gidebilirsiniz.</p>
</div>
<?php require __DIR__.'/../views/footer.php'; ?>
PHP

# --- pages/listings.php (okuma-only başlangıç) ---
cat > pages/listings.php <<'PHP'
<?php require __DIR__.'/../views/header.php'; ?>
<?php $rows = rkd_pdo()->query("SELECT l.*, u.username FROM listings l JOIN users u ON u.id=l.user_id ORDER BY l.id DESC")->fetchAll(); ?>
<div class="card"><h2>İlanlar</h2></div>
<?php foreach($rows as $r): ?>
  <div class="card">
    <div class="row">
      <span class="badge"><?=htmlspecialchars($r['category'])?></span>
      <span class="badge"><?=htmlspecialchars($r['subcategory'])?></span>
    </div>
    <h3 style="margin:.5rem 0 0.25rem"><?=htmlspecialchars($r['title'])?></h3>
    <p style="opacity:.9"><?=nl2br(htmlspecialchars($r['description']))?></p>
    <div class="row">
      <span class="price"><?=number_format((float)$r['price'],2,',','.')?> TL</span>
      <span class="badge">satıcı: @<?=htmlspecialchars($r['username'])?></span>
      <a class="btn" href="#">Satın Al (yakında)</a>
    </div>
  </div>
<?php endforeach; ?>
<?php require __DIR__.'/../views/footer.php'; ?>
PHP

# --- basit login/register placeholders (işlevsellik sonra) ---
cat > pages/login.php <<'PHP'
<?php require __DIR__.'/../views/header.php'; ?>
<div class="card"><h2>Giriş</h2><p>Giriş formu yakında.</p></div>
<?php require __DIR__.'/../views/footer.php'; ?>
PHP

cat > pages/register.php <<'PHP'
<?php require __DIR__.'/../views/header.php'; ?>
<div class="card"><h2>Kayıt</h2><p>Kayıt formu yakında.</p></div>
<?php require __DIR__.'/../views/footer.php'; ?>
PHP

cat > pages/messages.php <<'PHP'
<?php require __DIR__.'/../views/header.php'; ?>
<div class="card"><h2>Mesajlar</h2><p>Mesajlaşma modülü yakında.</p></div>
<?php require __DIR__.'/../views/footer.php'; ?>
PHP

# --- index.php: mini router ---
cat > index.php <<'PHP'
<?php
require __DIR__.'/app/db.php';

$p = $_GET['p'] ?? 'home';
$map = [
  'home'     => __DIR__.'/pages/home.php',
  'listings' => __DIR__.'/pages/listings.php',
  'login'    => __DIR__.'/pages/login.php',
  'register' => __DIR__.'/pages/register.php',
  'messages' => __DIR__.'/pages/messages.php',
];

if ($p === 'health') { header('content-type:text/plain'); echo "ok"; exit; }

$file = $map[$p] ?? $map['home'];
require $file;
PHP

# DB oluştur
php app/init_db.php

echo "Kurulum tamamlandı."
