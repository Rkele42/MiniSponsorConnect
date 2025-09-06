<?php
require __DIR__.'/../views/header.php';
if (!isset($_SESSION['user'])) { header('Location: /?p=login'); exit; }
$pdo = rkd_pdo();
$uid = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email        = trim($_POST['email'] ?? '');
  $phone        = trim($_POST['phone'] ?? '');
  $whatsapp     = trim($_POST['whatsapp'] ?? '');
  $iban         = trim($_POST['iban'] ?? '');
  $account_name = trim($_POST['account_name'] ?? '');
  $now = rkd_now();

  $stmt = $pdo->prepare("INSERT INTO user_profiles
    (user_id,email,phone,whatsapp,iban,account_name,updated_at)
    VALUES(?,?,?,?,?,?,?)
    ON CONFLICT(user_id) DO UPDATE SET
      email=excluded.email, phone=excluded.phone, whatsapp=excluded.whatsapp,
      iban=excluded.iban, account_name=excluded.account_name, updated_at=excluded.updated_at");
  $stmt->execute([$uid,$email,$phone,$whatsapp,$iban,$account_name,$now]);
  echo '<div class="card" style="background:#0d1b2a;color:#a7f3d0">Profil güncellendi.</div>';
}

$prof = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id=?");
$prof->execute([$uid]);
$prof = $prof->fetch() ?: ['email'=>'','phone'=>'','whatsapp'=>'','iban'=>'','account_name'=>''];
?>
<div class="card">
  <h2>Profilim</h2>
  <form method="post" class="row" style="gap:14px;flex-direction:column;max-width:560px">
    <label>E-posta
      <input name="email" value="<?=htmlspecialchars($prof['email'])?>" style="width:100%;padding:10px;border-radius:10px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb">
    </label>
    <label>Telefon
      <input name="phone" value="<?=htmlspecialchars($prof['phone'])?>" style="width:100%;padding:10px;border-radius:10px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb">
    </label>
    <label>WhatsApp
      <input name="whatsapp" value="<?=htmlspecialchars($prof['whatsapp'])?>" style="width:100%;padding:10px;border-radius:10px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb">
    </label>
    <label>IBAN
      <input name="iban" value="<?=htmlspecialchars($prof['iban'])?>" placeholder="TR.." style="width:100%;padding:10px;border-radius:10px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb">
    </label>
    <label>Hesap Sahibi Adı
      <input name="account_name" value="<?=htmlspecialchars($prof['account_name'])?>" style="width:100%;padding:10px;border-radius:10px;border:1px solid #1f2937;background:#0b1220;color:#e5e7eb">
    </label>
    <div class="row" style="justify-content:flex-start">
      <button class="btn" type="submit">Kaydet</button>
      <a class="btn" href="/?p=listings" style="background:#0f1a2b;color:#e5e7eb">İlanlara Dön</a>
    </div>
  </form>
</div>
<?php require __DIR__.'/../views/footer.php'; ?>
