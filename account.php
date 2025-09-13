<link rel="stylesheet" href="/assets/css/style.min.css">
  <?php
// صفحة حساب المستخدم الشاملة
require_once __DIR__.'/partials/header.php';
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';
require_login();

$pdo = db();
$u   = current_user();
$uid = (int)$u['id'];

$err=''; $ok='';

// تأكيد وجود المحفظة للمستخدم
$balStmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id=?");
$balStmt->execute([$uid]);
$balance = $balStmt->fetchColumn();
if ($balance === false) {
  $pdo->prepare("INSERT INTO wallets (user_id,balance) VALUES (?,0)")->execute([$uid]);
  $balance = 0;
}

/* === إحصائيات سريعة === */
$ordersCount = (int)$pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id=?")
  ->execute([$uid]) ? $pdo->query("SELECT COUNT(*) FROM orders WHERE user_id={$uid}")->fetchColumn() : 0;

$topupsCount = (int)$pdo->prepare("SELECT COUNT(*) FROM topups WHERE user_id=?")
  ->execute([$uid]) ? $pdo->query("SELECT COUNT(*) FROM topups WHERE user_id={$uid}")->fetchColumn() : 0;

$ordersStats = $pdo->prepare("SELECT status, COUNT(*) c FROM orders WHERE user_id=? GROUP BY status");
$ordersStats->execute([$uid]);
$ordersStats = array_column($ordersStats->fetchAll(), 'c', 'status');

$topupsStats = $pdo->prepare("SELECT status, COUNT(*) c FROM topups WHERE user_id=? GROUP BY status");
$topupsStats->execute([$uid]);
$topupsStats = array_column($topupsStats->fetchAll(), 'c', 'status');

/* === فلاتر وترقيم صفحات === */
function sanitize_status($s, $allowed){
  return in_array($s, $allowed, true) ? $s : '';
}

$o_status = isset($_GET['o_status']) ? sanitize_status($_GET['o_status'], ['awaiting_review','paid','completed','rejected']) : '';
$t_status = isset($_GET['t_status']) ? sanitize_status($_GET['t_status'], ['pending','approved','rejected']) : '';

$o_page   = max(1, (int)($_GET['op'] ?? 1));
$t_page   = max(1, (int)($_GET['tp'] ?? 1));
$LIMIT    = 10;
$o_off    = ($o_page-1)*$LIMIT;
$t_off    = ($t_page-1)*$LIMIT;

/* === جلب طلبات الخدمات (مع الخدمة/الباقة/الـslug لإعادة الطلب) === */
$ordersWhere = "WHERE o.user_id=?";
$params = [$uid];
if ($o_status) { $ordersWhere .= " AND o.status=?"; $params[] = $o_status; }

$countSql = "SELECT COUNT(*) FROM orders o $ordersWhere";
$stc = $pdo->prepare($countSql); $stc->execute($params); $ordersTotal = (int)$stc->fetchColumn();

$sql = "SELECT o.*, s.name sname, s.slug sslug, p.label plabel
        FROM orders o 
        JOIN services s ON s.id=o.service_id
        JOIN service_packages p ON p.id=o.package_id
        $ordersWhere
        ORDER BY o.id DESC
        LIMIT $LIMIT OFFSET $o_off";
$sto = $pdo->prepare($sql); $sto->execute($params);
$orders = $sto->fetchAll();

/* === جلب طلبات التعبئة === */
$topupsWhere = "WHERE user_id=?";
$paramsT = [$uid];
if ($t_status) { $topupsWhere .= " AND status=?"; $paramsT[] = $t_status; }

$stct = $pdo->prepare("SELECT COUNT(*) FROM topups $topupsWhere");
$stct->execute($paramsT); $topupsTotal = (int)$stct->fetchColumn();

$stt = $pdo->prepare("SELECT * FROM topups $topupsWhere ORDER BY id DESC LIMIT $LIMIT OFFSET $t_off");
$stt->execute($paramsT);
$topups = $stt->fetchAll();

/* === مساعدات مظهر للحالات === */
function order_badge($status){
  // درجات ألوان لطيفة عبر الـstyle inline (لتجنب تعديل CSS العام الآن)
  $map = [
    'awaiting_review' => 'background:rgba(255,209,102,.12);border:1px solid rgba(255,209,102,.4);color:#ffeabf',
    'paid'            => 'background:rgba(102,153,255,.12);border:1px solid rgba(102,153,255,.4);color:#cfe0ff',
    'completed'       => 'background:rgba(50,211,154,.14);border:1px solid rgba(50,211,154,.45);color:#aef5e6',
    'rejected'        => 'background:rgba(255,102,102,.12);border:1px solid rgba(255,102,102,.4);color:#ffcccc',
  ];
  $style = $map[$status] ?? '';
  return "<span class='badge' style=\"$style\">$status</span>";
}
function topup_badge($status){
  $map = [
    'pending'  => 'background:rgba(255,209,102,.12);border:1px solid rgba(255,209,102,.4);color:#ffeabf',
    'approved' => 'background:rgba(50,211,154,.14);border:1px solid rgba(50,211,154,.45);color:#aef5e6',
    'rejected' => 'background:rgba(255,102,102,.12);border:1px solid rgba(255,102,102,.4);color:#ffcccc',
  ];
  $style = $map[$status] ?? '';
  return "<span class='badge' style=\"$style\">$status</span>";
}

/* === روابط تنقل التصفية والترقيم === */
function qs(array $add){
  $base = $_GET;
  foreach($add as $k=>$v){
    if($v===null){ unset($base[$k]); } else { $base[$k]=$v; }
  }
  $qs = http_build_query($base);
  return '?'.($qs);
}
?>
<section class="section">

  <div class="head">
    <h2>حسابي</h2>
    <div class="actions">
      <a class="btn-gold" href="games.php">تصفح الخدمات</a>
      <a class="btn btn-primary" href="wallet.php">تعبئة المحفظة</a>
    </div>
  </div>

  <!-- ملخص سريع -->
  <div class="grid">
    <div class="card"><div class="body">
      <div class="title">رصيد المحفظة</div>
      <div class="meta" style="font-size:20px;font-weight:800"><?= $balance ?> <?= CURRENCY ?></div>
    </div></div>
    <div class="card"><div class="body">
      <div class="title">طلبات الخدمات</div>
      <div class="meta"><?= $ordersCount ?></div>
      <div class="note">بانتظار المراجعة: <b><?= (int)($ordersStats['awaiting_review']??0) ?></b> | مكتملة: <b><?= (int)($ordersStats['completed']??0) ?></b></div>
    </div></div>
    <div class="card"><div class="body">
      <div class="title">طلبات تعبئة المحفظة</div>
      <div class="meta"><?= $topupsCount ?></div>
      <div class="note">قيد التأكيد: <b><?= (int)($topupsStats['pending']??0) ?></b> | مؤكدة: <b><?= (int)($topupsStats['approved']??0) ?></b></div>
    </div></div>
    <div class="card"><div class="body">
      <div class="title">بياناتي</div>
      <div class="note"><?= htmlspecialchars($u['name']) ?> — <?= htmlspecialchars($u['phone']) ?><?= $u['email']? ' — '.htmlspecialchars($u['email']) : '' ?></div>
      <a class="btn" href="#profile" onclick="document.getElementById('profileBox').classList.toggle('hidden');return false;">تعديل</a>
    </div></div>
  </div>

  <!-- تعديل بسيط للملف الشخصي (اختياري للمستخدم) -->
  <div id="profileBox" class="form hidden" style="margin-top:12px">
    <?php
    if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])){
      $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $email=trim($_POST['email']??'');
      if($name=='' || $phone==''){ $err='الاسم والهاتف مطلوبان.'; }
      else{
        try{
          $st=$pdo->prepare("UPDATE users SET name=?, phone=?, email=? WHERE id=?");
          $st->execute([$name,$phone,$email?:null,$uid]);
          $_SESSION['user']['name']=$name; $_SESSION['user']['phone']=$phone; $_SESSION['user']['email']=$email?:null;
          $ok='تم تحديث بياناتك بنجاح.';
          // تحديث السطر المعروض أعلى الصفحة
          $u = current_user();
        }catch(Throwable $e){ $err='الهاتف/البريد مستخدم بالفعل.'; }
      }
    }
    if($err) echo '<div class="form" style="border-color:#f66;background:rgba(255,0,0,.06);margin-bottom:10px">'.$err.'</div>';
    if($ok)  echo '<div class="form" style="border-color:#6f6;background:rgba(0,255,0,.06);margin-bottom:10px">'.$ok.'</div>';
    ?>
    <form method="post" class="grid" style="grid-template-columns:repeat(3,1fr);gap:12px">
      <input type="hidden" name="update_profile" value="1">
      <div><label class="label">الاسم</label><input class="input" name="name" value="<?= htmlspecialchars($u['name']) ?>"></div>
      <div><label class="label">الهاتف</label><input class="input" name="phone" value="<?= htmlspecialchars($u['phone']) ?>"></div>
      <div><label class="label">البريد</label><input class="input" type="email" name="email" value="<?= htmlspecialchars($u['email']??'') ?>"></div>
      <div style="grid-column:1/-1" class="actions"><button class="btn btn-primary">حفظ</button></div>
    </form>
  </div>

  <!-- طلبات الخدمات -->
  <div class="section">
    <div class="head">
      <h2>طلباتي للخدمات</h2>
      <div class="actions" style="gap:6px">
        <a class="btn<?= $o_status==''?' btn-primary':'' ?>" href="<?= qs(['o_status'=>null, 'op'=>1]) ?>">الكل</a>
        <a class="btn<?= $o_status=='awaiting_review'?' btn-primary':'' ?>" href="<?= qs(['o_status'=>'awaiting_review','op'=>1]) ?>">بانتظار المراجعة (<?= (int)($ordersStats['awaiting_review']??0) ?>)</a>
        <a class="btn<?= $o_status=='paid'?' btn-primary':'' ?>" href="<?= qs(['o_status'=>'paid','op'=>1]) ?>">مدفوعة</a>
        <a class="btn<?= $o_status=='completed'?' btn-primary':'' ?>" href="<?= qs(['o_status'=>'completed','op'=>1]) ?>">مكتملة (<?= (int)($ordersStats['completed']??0) ?>)</a>
        <a class="btn<?= $o_status=='rejected'?' btn-primary':'' ?>" href="<?= qs(['o_status'=>'rejected','op'=>1]) ?>">مرفوضة</a>
      </div>
    </div>

    <div class="form">
      <?php if(!$orders): ?>
        <p class="note">لا توجد طلبات في هذا القسم بعد.</p>
        <a class="btn" href="games.php">ابدأ بالتصفح</a>
      <?php else: ?>
      <table class="table responsive">
        <tr><th>#</th><th>الخدمة</th><th>الباقة</th><th>السعر</th><th>الدفع</th><th>الحالة</th><th>التاريخ</th><th>إجراء</th></tr>
        <?php foreach($orders as $o): ?>
        <tr>
          <td><?= $o['id'] ?></td>
          <td><?= htmlspecialchars($o['sname']) ?></td>
          <td><?= htmlspecialchars($o['plabel']) ?></td>
          <td><?= $o['price'] ?> <?= CURRENCY ?></td>
          <td><?= $o['payment'] ?></td>
          <td><?= order_badge($o['status']) ?></td>
          <td><?= $o['created_at'] ?></td>
          <td>
            <a class="btn" href="service.php?slug=<?= urlencode($o['sslug']) ?>">عرض الباقات</a>
            <a class="btn btn-primary" href="order.php?slug=<?= urlencode($o['sslug']) ?>&pid=<?= (int)$o['package_id'] ?>">إعادة الطلب</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>

      <!-- ترقيم صفحات -->
      <?php
        $o_pages = max(1, (int)ceil($ordersTotal / $LIMIT));
        if($o_pages > 1):
      ?>
      <div class="actions" style="justify-content:flex-start;margin-top:10px">
        <?php if($o_page>1): ?><a class="btn" href="<?= qs(['op'=>$o_page-1]) ?>">السابق</a><?php endif; ?>
        <span class="note">صفحة <?= $o_page ?> / <?= $o_pages ?></span>
        <?php if($o_page<$o_pages): ?><a class="btn" href="<?= qs(['op'=>$o_page+1]) ?>">التالي</a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- طلبات تعبئة المحفظة -->
  <div class="section">
    <div class="head">
      <h2>طلبات تعبئة المحفظة</h2>
      <div class="actions" style="gap:6px">
        <a class="btn<?= $t_status==''?' btn-primary':'' ?>" href="<?= qs(['t_status'=>null,'tp'=>1]) ?>">الكل</a>
        <a class="btn<?= $t_status=='pending'?' btn-primary':'' ?>" href="<?= qs(['t_status'=>'pending','tp'=>1]) ?>">قيد التأكيد (<?= (int)($topupsStats['pending']??0) ?>)</a>
        <a class="btn<?= $t_status=='approved'?' btn-primary':'' ?>" href="<?= qs(['t_status'=>'approved','tp'=>1]) ?>">مؤكدة (<?= (int)($topupsStats['approved']??0) ?>)</a>
        <a class="btn<?= $t_status=='rejected'?' btn-primary':'' ?>" href="<?= qs(['t_status'=>'rejected','tp'=>1]) ?>">مرفوضة</a>
      </div>
    </div>

    <div class="form">
      <?php if(!$topups): ?>
        <p class="note">لا توجد إشعارات تعبئة بعد. يمكنك تعبئة محفظتك الآن.</p>
        <a class="btn btn-primary" href="wallet.php">تعبئة المحفظة</a>
      <?php else: ?>
      <table class="table responsive">
        <tr><th>#</th><th>الشبكة</th><th>رقم المُرسل</th><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr>
        <?php foreach($topups as $t): ?>
        <tr>
          <td><?= $t['id'] ?></td>
          <td><?= $t['network'] ?></td>
          <td><?= htmlspecialchars($t['sender_phone']) ?></td>
          <td><?= $t['amount'] ?> <?= CURRENCY ?></td>
          <td><?= topup_badge($t['status']) ?></td>
          <td><?= $t['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
      </table>

      <!-- ترقيم صفحات -->
      <?php
        $t_pages = max(1, (int)ceil($topupsTotal / $LIMIT));
        if($t_pages > 1):
      ?>
      <div class="actions" style="justify-content:flex-start;margin-top:10px">
        <?php if($t_page>1): ?><a class="btn" href="<?= qs(['tp'=>$t_page-1]) ?>">السابق</a><?php endif; ?>
        <span class="note">صفحة <?= $t_page ?> / <?= $t_pages ?></span>
        <?php if($t_page<$t_pages): ?><a class="btn" href="<?= qs(['tp'=>$t_page+1]) ?>">التالي</a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

</section>

<?php require __DIR__.'/partials/footer.php'; ?>

<script defer src="/assets/js/ui.min.js"></script>
