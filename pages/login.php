<?php
require_once __DIR__.'/../app/auth.php';

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
    rkd_login($_POST['username']??'', $_POST['password']??'');
    header('Location: /'); exit;
  } catch (Throwable $e) { $err = $e->getMessage(); }
}

require __DIR__.'/../views/header.php';
?>
<div class="card">
  <h2>Giriş Yap</h2>
  <?php if($err): ?><p style="color:#fca5a5"><?=htmlspecialchars($err)?></p><?php endif; ?>
  <form method="post" class="row" style="align-items:stretch;gap:8px">
    <input name="username" placeholder="Kullanıcı adı" required>
    <input name="password" type="password" placeholder="Şifre" required>
    <button class="btn" type="submit">Giriş</button>
  </form>
</div>
<?php require __DIR__.'/../views/footer.php'; ?>
