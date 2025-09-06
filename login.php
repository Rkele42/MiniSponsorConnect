<?php
require __DIR__ . '/app/auth.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $msg = 'Kullanıcı adı ve şifre zorunlu.';
    } else {
        if (login($username, $password)) {
            header('Location: /dashboard.php');
            exit;
        } else {
            $msg = 'Hatalı kullanıcı adı veya şifre.';
        }
    }
}
?>
<!doctype html><html lang="tr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Giriş</title></head><body>
<?php if ($msg): ?><p><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post" action="">
  <label>Kullanıcı adı: <input name="username" required></label><br>
  <label>Şifre: <input type="password" name="password" required></label><br>
  <button type="submit">Giriş</button>
</form>
<p><a href="/register.php">Hesabın yok mu? Kaydol</a></p>
</body></html>
