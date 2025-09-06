<?php
require __DIR__ . '/app/auth.php';
require_login();
$user = current_user();
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panel</title></head><body>
<h1>Merhaba, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?> 👋</h1>
<p>E-posta: <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
<p><a href="/logout.php">Çıkış yap</a></p>
</body></html>
