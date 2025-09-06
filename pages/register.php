<?php
require_once __DIR__.'/../app/auth.php';

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  try {
    $u = rkd_register($_POST['username']??'', $_POST['password']??'');
    $_SESSION['user'] = $u;
    header('Location: /'); exit;
  } catch (Throwable $e) { $err = $e->getMessage(); }
}

require __DIR__.'/../views/header.php';
?>
<div class="card">
  <h2>Kayıt Ol</h2>
  <?php if($err): ?><p style="color:#fca5a5"><?=htmlspecialchars($err)?></p><?php endif; ?>
  <form method="post" class="row" style="align-items:stretch;gap:8px">
    <input name="username" placeholder="Kullanıcı adı (min 3)" required>
    <input name="password" type="password" placeholder="Şifre (min 6)" required>
    <button class="btn" type="submit">Kayıt Ol</button>
  </form>
</div>
<?php require __DIR__.'/../views/footer.php'; ?>
