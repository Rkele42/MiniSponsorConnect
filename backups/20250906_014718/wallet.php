<?php
require_once __DIR__."/../app/db.php";
require_once __DIR__."/../app/helpers.php";

$pdo = rkd_pdo();
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header("Location: /?p=login"); exit; }
$uid = (int)$user['id'];

/* -------------------- Ayarlar (emanet IBAN) -------------------- */
$settings=[];
foreach($pdo->query("SELECT key, value FROM site_settings") as $r){ $settings[$r['key']]=$r['value']; }
$deposit_iban = trim($settings['deposit_iban'] ?? '');
$deposit_name = trim($settings['company_name'] ?? ($settings['deposit_name'] ?? ''));

/* -------------------- Kullanıcı IBAN/Hesap Sahibi keşfi -------------------- */
function find_user_bank_info(PDO $pdo, int $uid): array {
  $tables=[]; foreach($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'") as $r){ $tables[]=$r['name']; }
  // users dışındakileri önce dene
  $ordered = array_values(array_unique(array_merge(
    array_filter($tables, fn($t)=>strtolower($t)!=='users'),
    array_filter($tables, fn($t)=>strtolower($t)==='users')
  )));

  $ibanRe = ['/(^|_)iban($|_)/i','/iban/i'];
  $nameRe = ['/^account_?name$/i','/^iban_?name$/i','/holder_?name/i','/owner_?name/i','/hesap/i','/full_?name/i','/name$/i'];
  $nameExclude = ['username','user_name','display_name','brand','brand_name','company_name','deposit_name','country_name'];

  foreach($ordered as $t){
    $cols=[]; foreach($pdo->query("PRAGMA table_info($t)") as $r){ $cols[]=$r['name']; }
    if(!$cols) continue;

    $idCol   = in_array('user_id',$cols,true) ? 'user_id' : (strtolower($t)==='users' && in_array('id',$cols,true) ? 'id' : null);
    if(!$idCol) continue;

    // first col like
    $ibanCol=null; foreach($ibanRe as $re){ foreach($cols as $c){ if(preg_match($re,$c)){ $ibanCol=$c; break 2; } } }
    if(!$ibanCol) continue;

    $nameCol=null;
    foreach($cols as $c){
      $skip=false; foreach($nameExclude as $ex){ if(strcasecmp($c,$ex)===0){ $skip=true; break; } }
      if($skip) continue;
      foreach($nameRe as $re){ if(preg_match($re,$c)){ $nameCol=$c; break 2; } }
    }
    if(!$nameCol) continue;

    $st=$pdo->prepare("SELECT $ibanCol AS iban, $nameCol AS account_name FROM $t WHERE $idCol=? LIMIT 1");
    $st->execute([$uid]);
    if($row=$st->fetch(PDO::FETCH_ASSOC)){
      $iban = trim((string)($row['iban'] ?? ''));
      $name = trim((string)($row['account_name'] ?? ''));
      if($iban!=='' && $name!=='') return ['iban'=>$iban,'name'=>$name];
      if($iban!=='' || $name!=='') $best=['iban'=>$iban,'name'=>$name];
    }
  }
  return $best ?? ['iban'=>'','name'=>''];
}
$bank = find_user_bank_info($pdo,$uid);
$user_iban = $bank['iban'];
$user_name = $bank['name'];

/* -------------------- POST: Bakiye yükleme bildirimi -------------------- */
$msg_ok = null; $msg_err = null;
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='topup_notify') {
  $amt  = (float)($_POST['amount'] ?? 0);
  $note = trim($_POST['note'] ?? '');
  if ($amt > 0) {
    $st=$pdo->prepare("INSERT INTO topups(user_id,amount,note,status,created_at) VALUES(?,?,?,?,?)");
    $st->execute([$uid,$amt,$note,'pending',rkd_now()]);
    $msg_ok = "Bildirim alındı. Onay sonrası bakiyenize yansıyacak.";
  } else { $msg_err = "Geçerli bir tutar giriniz."; }
}

/* -------------------- POST: Çekim talebi -------------------- */
$min = 200.0;  $fee = 10.0;
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='withdraw_request') {
  $amt = (float)($_POST['amount'] ?? 0);
  if ($user_iban==='' || $user_name==='') {
    $msg_err = "Önce profilinizden IBAN ve Hesap Sahibinizi doldurunuz.";
  } elseif ($amt < $min) {
    $msg_err = "Minimum {$min} TL çekim yapabilirsiniz.";
  } else {
    $bal = get_balance($pdo,$uid);
    if ($amt + $fee > $bal) {
      $msg_err = "Yetersiz bakiye. (Tutar + {$fee} TL komisyon)";
    } else {
      add_balance($pdo,$uid,-($amt + $fee));
      $st=$pdo->prepare("INSERT INTO withdrawals(user_id,amount,fee,status,created_at) VALUES(?,?,?,?,?)");
      $st->execute([$uid,$amt,$fee,'pending',rkd_now()]);
      $msg_ok = "Çekim talebiniz oluşturuldu.";
    }
  }
}

/* -------------------- Listeler -------------------- */
$my_topups = in_array('topups', $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN)) ? $pdo->query("SELECT * FROM topups WHERE user_id=$uid ORDER BY id DESC LIMIT 50")->fetchAll() : [];
$my_wds    = in_array('withdrawals', $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN)) ? $pdo->query("SELECT * FROM withdrawals WHERE user_id=$uid ORDER BY id DESC LIMIT 50")->fetchAll() : [];

require __DIR__."/../views/header.php";
?>
<?php if($msg_ok): ?><div class="alert success"><?=htmlspecialchars($msg_ok)?></div><?php endif; ?>
<?php if($msg_err): ?><div class="alert"><?=htmlspecialchars($msg_err)?></div><?php endif; ?>

<div class="card">
  <h2 style="margin-top:0">Bakiye Yükleme (Havale/EFT)</h2>
  <div class="alert" style="background:#0b1220;border-color:#f59e0b;color:#e5e7eb">
    <p class="help"><strong>ÖNEMLİ:</strong> Ödeme yaparken transfer <em>açıklamasına</em> kullanıcı adınızı yazmayı unutmayın (örn: @<?= htmlspecialchars(isset($user['username']) ? $user['username'] : 'kullanici') ?>).</p>
    <p class="help"><strong>Alıcı Adı:</strong> <?= htmlspecialchars($deposit_name !== '' ? $deposit_name : '—') ?></p>
    <p class="help"><strong>IBAN:</strong> <?= htmlspecialchars($deposit_iban !== '' ? $deposit_iban : '—') ?></p>
  </div>

  <h3 style="margin:1rem 0 .5rem">Bakiye Yükleme Bildirimi</h3>
  <form method="post" class="form-grid">
    <input type="hidden" name="action" value="topup_notify">
    <div><label>Tutar (TL)</label><input type="number" step="0.01" min="1" name="amount" required></div>
    <div><label>Açıklama (opsiyonel)</label><input type="text" name="note" value="@<?= htmlspecialchars(isset($user['username']) ? $user['username'] : '') ?>" placeholder="Dekont no / açıklama"></div>
    <div style="align-self:end"><button class="btn">Bildirim Gönder</button></div>
  </form>
</div>

<div class="card">
  <h2 style="margin-top:0">Para Çekme Talebi</h2>
  <div class="alert" style="background:#0b1220;border-color:#374151;color:#e5e7eb">
    <p class="help"><strong>IBAN:</strong> <?= htmlspecialchars($user_iban !== '' ? $user_iban : '—') ?></p>
    <p class="help"><strong>Hesap Sahibi:</strong> <?= htmlspecialchars($user_name !== '' ? $user_name : '—') ?></p>
    <?php if($user_iban==='' || $user_name===''): ?>
      <p class="help">IBAN/isim değiştirmek için <a href="/?p=profile">profilinizi</a> güncelleyiniz.</p>
    <?php endif; ?>
  </div>

  <form method="post" class="form-grid">
    <input type="hidden" name="action" value="withdraw_request">
    <div><label>Tutar (TL)</label><input type="number" step="0.01" min="1" name="amount" required></div>
    <div style="align-self:end"><button class="btn">Talep Oluştur</button></div>
  </form>
  <p class="help">Minimum <?=number_format($min,2,',','.')?> TL, komisyon <?=number_format($fee,2,',','.')?> TL (bakiyeden düşülür).</p>
</div>

<div class="card">
  <h3 style="margin-top:0">Bakiye Yükleme Bildirimleri</h3>
  <?php if(!$my_topups): ?><p class="help">Henüz bildirim yok.</p><?php endif; ?>
  <?php foreach($my_topups as $t): ?>
    <div class="row" style="justify-content:space-between;margin:.25rem 0">
      <span class="badge">#<?=$t['id']?></span>
      <span class="price"><?=number_format((float)$t['amount'],2,',','.')?> TL</span>
      <span class="badge">durum: <?=htmlspecialchars($t['status'])?></span>
    </div>
    <?php if($t['note']): ?><p class="help">Not: <?=htmlspecialchars($t['note'])?></p><?php endif; ?>
    <div style="height:1px;background:#1f2937;margin:10px 0"></div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h3 style="margin-top:0">Çekim Talepleri</h3>
  <?php if(!$my_wds): ?><p class="help">Henüz talep yok.</p><?php endif; ?>
  <?php foreach($my_wds as $w): ?>
    <div class="row" style="justify-content:space-between;margin:.25rem 0">
      <span class="badge">#<?=$w['id']?></span>
      <span class="price" style="padding:4px 8px;border-radius:6px;background:#0ea5e9;color:#001825;">
        <?=number_format((float)$w['amount'],2,',','.')?> TL
      </span>
      <span class="badge">komisyon: <?=number_format((float)$w['fee'],2,',','.')?> TL</span>
      <span class="badge">durum: <?=htmlspecialchars($w['status'])?></span>
    </div>
    <div style="height:1px;background:#1f2937;margin:10px 0"></div>
  <?php endforeach; ?>
</div>

<?php require __DIR__."/../views/footer.php"; ?>
