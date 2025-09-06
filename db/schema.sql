PRAGMA journal_mode=WAL;

/* --- users --- */
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT UNIQUE NOT NULL,
  password_hash TEXT NOT NULL,
  is_admin INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

/* --- listings --- */
CREATE TABLE IF NOT EXISTS listings (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  platform TEXT NOT NULL,               -- Instagram/TikTok/Twitter...
  title TEXT NOT NULL,
  price REAL NOT NULL,
  description TEXT,
  status TEXT NOT NULL DEFAULT 'active',-- active | sold | hidden
  featured_until DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

/* --- balances (ileride para çekme vb. için) --- */
CREATE TABLE IF NOT EXISTS balances(
  user_id INTEGER PRIMARY KEY,
  amount REAL NOT NULL DEFAULT 0,
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

/* --- SEED: admin + örnek ilan --- */
INSERT OR IGNORE INTO users (id, username, password_hash, is_admin)
VALUES (1,'rkeles','$2y$10$3c9Qx2C3JmQWgkCw.3oKQeG1rXo5Jm1cJ7wTgq7m3l7v2o0u0F4qS',1);
/* şifre: demo123 (Bcrypt) */

INSERT INTO balances(user_id, amount) VALUES (1, 0)
ON CONFLICT(user_id) DO NOTHING;

INSERT INTO listings (user_id, platform, title, price, description)
VALUES
(1,'TikTok','Deneme ilanı',20.00,'İlk test ilanı – sistemi doğrulamak için.');
