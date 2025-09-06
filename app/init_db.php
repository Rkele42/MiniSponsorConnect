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

echo "DB hazır: ".RKD_DB_PATH.PHP_EOL;
