<?php
require __DIR__ . '/app/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $msg = 'Tüm alanlar zorunludur.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, $hash]);
            $msg = 'Kayıt başarılı 🎉';
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $msg = 'Kullanıcı adı veya e-posta zaten kayıtlı.';
            } else {
                $msg = 'Hata: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }
    }
}
?>
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kayıt</title></head>
<body>
<?php if ($msg): ?><p><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post" action="">
    <label>Kullanıcı adı: <input name="username" required></label><br>
    <label>E-posta: <input type="email" name="email" required></label><br>
    <label>Şifre: <input type="password" name="password" required></label><br>
    <button type="submit">Kaydol</button>
</form>
</body>
</html>
