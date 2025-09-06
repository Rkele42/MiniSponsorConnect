<?php
require_once __DIR__.'/../app/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
$user = $_SESSION['user'] ?? null;
if (!$user) { header('Location: /?p=login'); exit; }

$pdo = rkd_pdo();
$msg = null;

$CATS = [
  'Influencer Marketing' => ['Instagram İşbirliği','YouTube Entegrasyon','TikTok Video','Twitter (X) Mention'],
  'Sosyal Medya Hesap Satışı' => ['Instagram Hesap','YouTube Kanal','TikTok Hesap','Twitter (X) Hesap'],
  'Sosyal Medya Etkileşim Satışı' => ['Takipçi','Beğeni','Yorum','Görüntülenme']
];

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $cat   = trim($_POST['category'] ?? '');
  $sub   = trim($_POST['subcategory'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $desc  = trim($_POST['description'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $imagePath = null;

  // Görsel yükleme (tüm oranlar kabul) — kartta 16:9 crop gösterilecek
  if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['image']['tmp_name'];
    $info = @getimagesize($tmp); // doğrulama
    if (!$info) {
      $msg = 'Yüklenen dosya bir resim değil.';
    } else {
      $mime = is_array($info) && isset($info['mime']) ? $info['mime'] : '';
      $ext = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => null
      };
      if (!$ext) {
        $msg = 'Sadece JPG/PNG/WEBP kabul edilir.';
      } else {
        if (!is_dir(__DIR__.'/../uploads')) mkdir(__DIR__.'/../uploads', 0777, true);
        $imagePath = '/uploads/img_'.time().'_'.bin2hex(random_bytes(3)).'.'.$ext;
        if (!@move_uploaded_file($tmp, __DIR__.'/..'.$imagePath)) {
          $msg = 'Görsel kaydedilemedi.';
        }
      }
    }
  }

  if (!$msg) {
    if ($cat==='' || $sub==='' || $title==='' || $price<=0) {
      $msg = 'Lütfen gerekli alanları doldurun ve geçerli bir fiyat girin.';
    } elseif (!isset($CATS[$cat]) || !in_array($sub,$CATS[$cat], true)) {
      $msg = 'Geçersiz kategori / alt kategori.';
    } else {
      // image sütunu varsa kaydeder; yoksa NULL gider (aşağıdaki ALTER ile ekleyeceğiz)
      $stmt = $pdo->prepare("INSERT INTO listings(user_id,category,subcategory,title,description,price,featured,created_at,image)
                             VALUES(?,?,?,?,?,?,?,?,?)");
      $stmt->execute([
        (int)$user['id'], $cat, $sub, $title, $desc, $price, 'standart', rkd_now(), $imagePath
      ]);
      header('Location: /?p=listings');
      exit;
    }
  }
}
require __DIR__.'/../views/header.php';
?>
<div class="card">
  <h2>Satış Yap</h2>
  <p class="help">Her boyutta görsel yükleyebilirsiniz. İlan kartlarında görsel 16:9 oranında kırpılarak gösterilir (JPG/PNG/WEBP).</p>
  <?php if($msg): ?><div class="alert"><?=htmlspecialchars($msg)?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <div>
        <label>Kategori</label>
        <select name="category" id="cat" required onchange="syncSubs()">
          <option value="">Seçiniz</option>
          <?php foreach($CATS as $c=>$subs): ?>
            <option value="<?=$c?>" <?=isset($_POST['category'])&&$_POST['category']===$c?'selected':''?>><?=$c?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Alt Kategori</label>
        <select name="subcategory" id="sub" required>
          <option value="">Önce kategori seçin</option>
        </select>
      </div>
    </div>

    <label>Başlık</label>
    <input type="text" name="title" required value="<?=htmlspecialchars($_POST['title']??'')?>">

    <label>Açıklama</label>
    <textarea name="description"><?=htmlspecialchars($_POST['description']??'')?></textarea>

    <div class="form-grid">
      <div>
        <label>Fiyat (TL)</label>
        <input type="number" name="price" step="0.01" min="1" required value="<?=htmlspecialchars($_POST['price']??'')?>">
      </div>
      <div>
        <label>İlan Görseli (isteğe bağlı)</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
      </div>
    </div>

    <div style="margin-top:10px">
      <button class="btn" type="submit">İlanı Yayınla</button>
      <a class="btn" href="/?p=listings" style="background:#0f1a2b;color:#e5e7eb;border:1px solid #243049">İlanlara Dön</a>
    </div>
  </form>
</div>

<script>
const CATS = <?php echo json_encode($CATS, JSON_UNESCAPED_UNICODE); ?>;
function syncSubs(){
  const cat = document.getElementById('cat').value;
  const sub = document.getElementById('sub');
  sub.innerHTML = '<option value="">Alt kategori seçin</option>';
  if(CATS[cat]){
    CATS[cat].forEach(s=>{
      const o=document.createElement('option'); o.value=s; o.textContent=s;
      if("<?=htmlspecialchars($_POST['subcategory']??'')?>"===s) o.selected=true;
      sub.appendChild(o);
    });
  }
}
document.addEventListener('DOMContentLoaded', syncSubs);
</script>
<?php require __DIR__.'/../views/footer.php'; ?>
