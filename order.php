<link rel="stylesheet" href="/assets/css/style.min.css">
 
<?php
@ini_set('display_errors', 0);
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';
require_login();

$__header = __DIR__.'/partials/header.php';
if (!file_exists($__header)) $__header = __DIR__.'/header.php';
if (file_exists($__header)) require $__header;

$pdo = db();
$user = current_user();
$uid  = (int)$user['id'];

function has_col(PDO $pdo, string $table, string $col): bool {
  try { $q=$pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?"); $q->execute([$col]); return (bool)$q->fetchColumn(); }
  catch(Throwable $e){ return false; }
}
function filter_row_for_table(PDO $pdo, string $table, array $data): array {
  try { $cols = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN, 0); return array_intersect_key($data, array_flip($cols)); }
  catch(Throwable $e){ return []; }
}
function insert_row(PDO $pdo, string $table, array $data){
  $data = filter_row_for_table($pdo, $table, $data);
  if(!$data) return false;
  $keys = array_keys($data);
  $sql = "INSERT INTO `$table` (`".implode("`,`", $keys)."`) VALUES (".implode(",", array_fill(0, count($keys), "?")).")";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array_values($data));
  return $pdo->lastInsertId();
}

$slug = $_GET['slug'] ?? '';
$pid  = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;

$svcStmt = $pdo->prepare("SELECT * FROM services WHERE slug=? LIMIT 1");
$svcStmt->execute([$slug]);
$service = $svcStmt->fetch(PDO::FETCH_ASSOC);

$package = null;
if ($service && $pid) {
  $pkgStmt = $pdo->prepare("SELECT * FROM service_packages WHERE id=? AND service_id=? LIMIT 1");
  $pkgStmt->execute([$pid, $service['id']]);
  $package = $pkgStmt->fetch(PDO::FETCH_ASSOC);
}
if(!$service || !$package){
  echo '<section class="container page theme-animating"><div class="card p-4"><h2>الخدمة أو الباقة غير موجودة</h2></div></section>';
  $__footer = __DIR__.'/partials/footer.php'; if (!file_exists($__footer)) $__footer = __DIR__.'/footer.php'; if (file_exists($__footer)) require $__footer; exit;
}

// Derive profile
$prof = null;
$category = $service['category'] ?? ($service['type'] ?? null);
if (isset($service['requirements_profile']) && $service['requirements_profile']) $prof = $service['requirements_profile'];
if (!$prof) {
  if ($category === 'subscription') $prof = 'subscription_wapp';
  elseif ($category === 'game')     $prof = 'game_id_wapp';
  elseif ($category === 'app')      $prof = 'app_id_wapp';
}
$slug_s = strtolower($service['slug'] ?? ''); $name_s = strtolower($service['name'] ?? '');
if (!$prof) {
  if (strpos($slug_s,'tiktok')!==false || strpos($name_s,'tiktok')!==false) $prof = 'app_account_wapp';
  elseif (strpos($slug_s,'netflix')!==false || strpos($name_s,'netflix')!==false) $prof = 'subscription_wapp';
  elseif (strpos($slug_s,'spotify')!==false || strpos($name_s,'spotify')!==false) $prof = 'subscription_wapp';
  elseif (strpos($slug_s,'shahid')!==false || strpos($name_s,'shahid')!==false) $prof = 'subscription_wapp';
  elseif (strpos($slug_s,'youtube')!==false || strpos($name_s,'youtube')!==false) $prof = 'subscription_wapp';
  else $prof = 'app_id_wapp';
}
$map = [
  'game_id_wapp'      => 'لعبة: ID + واتساب',
  'app_id_wapp'       => 'تطبيق: ID + واتساب',
  'app_account_wapp'  => 'تطبيق بث مباشر/حساب: بريد + رمز + واتساب',
  'subscription_wapp' => 'اشتراك: واتساب فقط',
];
$prof_label = $map[$prof] ?? $prof;
$price = isset($package['price']) ? (float)$package['price'] : 0.0;

$err=''; $ok=''; $oid=0;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $player_id = trim($_POST['player_id'] ?? '');
  $server    = trim($_POST['server'] ?? '');
  $whatsapp  = trim($_POST['whatsapp'] ?? '');
  $acc_email = trim($_POST['account_email'] ?? '');
  $acc_pass  = trim($_POST['account_password'] ?? '');
  $note      = trim($_POST['note'] ?? '');
  $payment   = $_POST['payment_method'] ?? 'wallet';
  $network   = $_POST['network'] ?? 'madar';
  $sender    = trim($_POST['sender_phone'] ?? '');
  $amount    = isset($_POST['amount_sent']) ? (float)$_POST['amount_sent'] : null;

  if ($price <= 0 || is_null($package['price'])) {
    $price = isset($_POST['price_custom']) ? (float)$_POST['price_custom'] : 0.0;
  }

  if ($prof === 'game_id_wapp' || $prof === 'app_id_wapp') {
    if ($player_id==='') $err = 'يرجى إدخال المعرّف / ID.';
    if (!$err && $whatsapp==='') $err = 'يرجى إدخال رقم واتساب.';
  } elseif ($prof === 'app_account_wapp') {
    if ($acc_email==='' || $acc_pass==='') $err = 'يرجى إدخال بريد الحساب والرمز.';
    if (!$err && $whatsapp==='') $err = 'يرجى إدخال رقم واتساب.';
  } elseif ($prof === 'subscription_wapp') {
    if ($whatsapp==='') $err = 'يرجى إدخال رقم واتساب.';
  } else {
    $err = 'الخدمة غير مهيأة لنوع التنفيذ. تواصل مع الدعم.';
  }
  if(!$err && $price<=0) $err = 'لا يوجد سعر صالح.';

  $wallet_balance = 0.0;
  if(!$err && $payment==='wallet'){
    $bal = $pdo->prepare("SELECT balance FROM wallets WHERE user_id=?");
    $bal->execute([$uid]);
    $row = $bal->fetch(PDO::FETCH_ASSOC);
    if(!$row){ insert_row($pdo, 'wallets', ['user_id'=>$uid, 'balance'=>0, 'created_at'=>date('Y-m-d H:i:s')]); }
    else { $wallet_balance = (float)$row['balance']; }
    if ($wallet_balance < $price) $err = 'رصيد المحفظة غير كافٍ.';
  }

  if(!$err){
    $orderData = [
      'user_id'=>$uid,'service_id'=>(int)$service['id'],'package_id'=>(int)$package['id'],
      'price'=>$price,'status'=>($payment==='wallet') ? 'paid' : 'awaiting_transfer',
      'player_id'=>$player_id ?: null,'server'=>$server ?: null,'whatsapp'=>$whatsapp ?: null,
      'account_email'=>$acc_email ?: null,'account_password'=>$acc_pass ?: null,
      'note'=>$note,'network'=>$network ?: null,'sender_phone'=>$sender ?: null,'amount_sent'=>$amount,
      'created_at'=>date('Y-m-d H:i:s'),
    ];
    if (has_col($pdo,'orders','category')) $orderData['category'] = $category ?: null;
    if (has_col($pdo,'orders','requirements_profile')) $orderData['requirements_profile'] = $prof ?: null;

    $oid = insert_row($pdo, 'orders', $orderData);
    if($oid){
      if($payment==='wallet'){
        $newBal = max(0, $wallet_balance - $price);
        try{
          $pdo->beginTransaction();
          $pdo->prepare("UPDATE wallets SET balance=? WHERE user_id=?")->execute([$newBal, $uid]);
          insert_row($pdo, 'wallet_ledger', [
            'user_id'=>$uid,'direction'=>'debit','amount'=>$price,
            'reference_type'=>'order','reference_id'=>$oid,'memo'=>'خصم قيمة الطلب','created_at'=>date('Y-m-d H:i:s'),
          ]);
          insert_row($pdo, 'wallet_events', [
            'user_id'=>$uid,'type'=>'order_paid','payload'=>json_encode(['order_id'=>$oid,'amount'=>$price], JSON_UNESCAPED_UNICODE),'created_at'=>date('Y-m-d H:i:s'),
          ]);
          $pdo->commit();
        }catch(Throwable $e){ $pdo->rollBack(); }
      } else {
        insert_row($pdo, 'wallet_events', [
          'user_id'=>$uid,'type'=>'order_transfer','payload'=>json_encode(['order_id'=>$oid,'network'=>$network,'sender'=>$sender,'amount'=>$amount], JSON_UNESCAPED_UNICODE),'created_at'=>date('Y-m-d H:i:s'),
        ]);
      }
      $ok = "تم تسجيل طلبك (#$oid) بنجاح ✅";
    } else { $err = 'تعذّر إنشاء الطلب. حاول مجددًا.'; }
  }
}

$svc_desc = $service['short_desc'] ?? ($service['description'] ?? '');
?>
<section class="container page">
  <div class="head">
    <h2>تأكيد الطلب</h2>
    <p class="muted"><?= htmlspecialchars($service['name']) ?> — <?= htmlspecialchars($package['label']) ?></p>
    <?php if($svc_desc): ?><p class="muted"><?= htmlspecialchars($svc_desc) ?></p><?php endif; ?>
  </div>

  <?php if($err): ?><div class="form" style="border-color:#f66;background:rgba(255,0,0,.06)"><?= htmlspecialchars($err) ?></div><?php endif; ?>
  <?php if($ok):  ?><div class="form" style="border-color:#6f6;background:rgba(0,255,0,.06)"><?= htmlspecialchars($ok) ?></div><?php endif; ?>

  <div class="grid" style="display:grid;grid-template-columns:1.2fr 1fr;gap:16px">
    <div>
      <div class="card card-royal lift">
        <div class="card__body">
          <form id="orderForm" method="post" novalidate>
            <div class="field">
              <label class="label">الخدمة</label>
              <input class="input" type="text" value="<?= htmlspecialchars($service['name']) ?>" readonly>
            </div>
            <div class="field mt-2">
              <label class="label">الباقة</label>
              <input class="input" type="text" value="<?= htmlspecialchars($package['label']) ?>" readonly>
            </div>

            <input type="hidden" name="service" value="<?= (int)$service['id'] ?>">
            <input type="hidden" name="package" value="<?= (int)$package['id'] ?>">

            <?php if ($prof==='game_id_wapp' || $prof==='app_id_wapp'): ?>
              <div class="field mt-3">
                <label class="label">المعرّف / ID</label>
                <input class="input" type="text" name="player_id" placeholder="مثال: 123456789">
              </div>
              <?php if($prof==='game_id_wapp'): ?>
              <div class="field mt-2">
                <label class="label">السيرفر / المنطقة (اختياري)</label>
                <input class="input" type="text" name="server" placeholder="مثال: Asia-1">
              </div>
              <?php endif; ?>
              <div class="field mt-2">
                <label class="label">رقم واتساب</label>
                <input class="input" type="tel" name="whatsapp" inputmode="tel" placeholder="09XXXXXXXX">
              </div>
            <?php elseif ($prof==='app_account_wapp'): ?>
              <div class="field mt-3">
                <label class="label">البريد الإلكتروني للحساب</label>
                <input class="input" type="email" name="account_email" placeholder="name@email.com">
              </div>
              <div class="field mt-2">
                <label class="label">الرمز / كلمة المرور</label>
                <input class="input" type="password" name="account_password" placeholder="••••••••">
              </div>
              <div class="field mt-2">
                <label class="label">رقم واتساب</label>
                <input class="input" type="tel" name="whatsapp" inputmode="tel" placeholder="09XXXXXXXX">
              </div>
            <?php else: ?>
              <div class="field mt-3">
                <label class="label">رقم واتساب</label>
                <input class="input" type="tel" name="whatsapp" inputmode="tel" placeholder="09XXXXXXXX">
              </div>
            <?php endif; ?>

            <div class="field mt-3">
              <label class="label">طريقة الدفع</label>
              <select class="select" name="payment_method" id="pm">
                <option value="wallet">من المحفظة</option>
                <option value="transfer">تحويل رصيد (ليبيانا/المدار)</option>
              </select>
            </div>
            <div id="transferBox" class="form mt-2" style="display:none">
              <div class="field">
                <label class="label">الشبكة</label>
                <select class="select" name="network">
                  <option value="madar">المدار</option>
                  <option value="libyana">ليبيانا</option>
                </select>
              </div>
              <div class="field mt-2">
                <label class="label">رقم المُرسل</label>
                <input class="input" type="tel" name="sender_phone" inputmode="tel" placeholder="09XXXXXXXX">
              </div>
              <?php if ($price <= 0): ?>
              <div class="field mt-2">
                <label class="label">قيمة التحويل (<?= defined('CURRENCY')?CURRENCY:'LYD' ?>)</label>
                <input class="input" type="number" step="0.1" name="price_custom" placeholder="0.0">
              </div>
              <?php endif; ?>
              <div class="field mt-2">
                <label class="label">المبلغ المُرسل</label>
                <input class="input" type="number" step="0.1" name="amount_sent" placeholder="0.0">
              </div>
              <p class="note">أرسل الرصيد إلى: مدار <b><?= defined('MADAR_NUMBER')?MADAR_NUMBER:'0910000000' ?></b> أو ليبيانا <b><?= defined('LIBYANA_NUMBER')?LIBYANA_NUMBER:'0920000000' ?></b>.</p>
            </div>

            <div class="field mt-3">
              <label class="label">ملاحظة (اختياري)</label>
              <textarea class="textarea" name="note" rows="3" placeholder="أي تفاصيل إضافية تساعدنا على تنفيذ طلبك أسرع"></textarea>
            </div>

            <div class="actions mt-3" style="display:flex;gap:10px">
              <button class="btn-royal" type="submit">إرسال الطلب</button>
              <a class="btn" href="javascript:history.back()">رجوع</a>
            </div>
          </form>
        </div>
      </div>
    </div>

    <aside>
      <div class="card">
        <div class="card__body">
          <div class="title">تفاصيل الباقة</div>
          <ul class="list">
            <li><strong>السعر:</strong> <?= number_format((float)$price,2) ?> <?= defined('CURRENCY')?CURRENCY:'LYD' ?></li>
            <?php if(isset($package['short_desc']) && $package['short_desc']): ?>
              <li><?= htmlspecialchars($package['short_desc']) ?></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </aside>
  </div>
</section>

<script>
(function(){
  const pm=document.getElementById('pm'), t=document.getElementById('transferBox');
  if(pm && t){ t.style.display = pm.value==='transfer' ? 'block' : 'none'; pm.addEventListener('change', ()=>{ t.style.display = pm.value==='transfer' ? 'block' : 'none'; }); }
})();
</script>

<?php
$__footer = __DIR__.'/partials/footer.php';
if (!file_exists($__footer)) $__footer = __DIR__.'/footer.php';
if (file_exists($__footer)) require $__footer;
?>
<script defer src="/assets/js/ui.min.js"></script>
