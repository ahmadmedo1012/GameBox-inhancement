<link rel="stylesheet" href="/assets/css/style.min.css">
<?php
require_once __DIR__.'/partials/header.php';
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';
$pdo = db();

$slug = trim($_GET['slug'] ?? '');
if($slug===''){ http_response_code(404); echo "<section class='container page'><h2>الخدمة غير موجودة</h2></section>"; require __DIR__.'/partials/footer.php'; exit; }
$st = $pdo->prepare("SELECT * FROM services WHERE slug=? LIMIT 1"); $st->execute([$slug]); $svc = $st->fetch();
if(!$svc){ http_response_code(404); echo "<section class='container page'><h2>الخدمة غير موجودة</h2></section>"; require __DIR__.'/partials/footer.php'; exit; }

$pk = $pdo->prepare("SELECT * FROM service_packages WHERE service_id=? ORDER BY price ASC, id ASC");
$pk->execute([$svc['id']]); $packages = $pk->fetchAll();

$fav=false; if(current_user()){ $uid=(int)current_user()['id']; try{$f=$pdo->prepare('SELECT 1 FROM favorites WHERE user_id=? AND service_id=?'); $f->execute([$uid,$svc['id']]); $fav=(bool)$f->fetchColumn();}catch(Throwable $e){ $fav=false; } }
?>
<section class="page container">
  <div class="section">
    <div class="head">
      <h2><?= htmlspecialchars($svc['name']) ?></h2>
      <div class="actions">
        <button class="fav-btn <?= $fav?'active':'' ?>" data-service="<?= (int)$svc['id'] ?>" aria-label="أضف للمفضلة">♥</button>
        <a class="btn" href="games.php">رجوع</a>
      </div>
    </div>

    <div class="form">
      <div class="field">
        <label class="label">اختر الباقة</label>
        <div class="grid" style="grid-template-columns:repeat(3,1fr)">
          <?php $first=true; foreach($packages as $p): ?>
          <label class="card" style="cursor:pointer">
            <input type="radio" name="pkg" value="<?= (int)$p['id'] ?>" data-price="<?= (float)$p['price'] ?>" style="position:absolute;opacity:0" <?= $first?'checked':'' ?>>
            <?php $first=false; ?>
            <div class="body">
              <div class="title"><?= htmlspecialchars($p['label']) ?></div>
              <div class="meta"><?= (float)$p['price'] ?> <?= CURRENCY ?></div>
              <div class="note"><?= htmlspecialchars($p['short_desc'] ?? '') ?></div>
            </div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="actions">
        <button id="buyNow" class="btn-royal">اشترِ الآن</button>
        <a class="btn" href="games.php">استمرار التصفح</a>
      </div>
    </div>
  </div>
</section>

<!-- Bottom Sheet (Mobile Purchase Summary) -->
<div class="sheet-backdrop" id="sheetBackdrop"></div>
<aside class="sheet" id="buySheet" aria-hidden="true">
  <div class="sheet-head">
    <strong>تأكيد الطلب</strong>
    <button id="sheetClose" class="theme-toggle" aria-label="إغلاق">✖</button>
  </div>
  <div class="sheet-body">
    <div class="row" style="display:flex;justify-content:space-between">
      <div>الخدمة</div><div><b><?= htmlspecialchars($svc['name']) ?></b></div>
    </div>
    <div class="row" style="display:flex;justify-content:space-between">
      <div>الباقة</div><div id="sheetPkg">—</div>
    </div>
    <div class="row" style="display:flex;justify-content:space-between">
      <div>السعر</div><div id="sheetPrice">—</div>
    </div>
  </div>
  <div class="sheet-actions">
    <a id="sheetConfirm" class="btn btn-primary btn block" href="#">تأكيد والدفع</a>
  </div>
</aside>

<?php require __DIR__.'/partials/footer.php'; ?>

<script defer src="/assets/js/ui.min.js"></script>
