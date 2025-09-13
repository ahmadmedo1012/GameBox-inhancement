<link rel="stylesheet" href="/assets/css/style.min.css">
<?php
// === minimal safety defaults (لا تغييرات أخرى) ===
if (!defined('CURRENCY')) { define('CURRENCY', 'د.ل'); }
if (!isset($msg)) { $msg = null; }
if (!isset($balance)) { $balance = 0.0; }
if (!isset($topups) || !is_array($topups)) { $topups = []; }

// (اختياري لمنع تحذيرات مستقبلية لو تُستخدم هذه الثوابت):
if (!defined('MADAR_NUMBER'))   { define('MADAR_NUMBER', '0942119637'); }
if (!defined('LIBYANA_NUMBER')) { define('LIBYANA_NUMBER', '0919650089'); }
// =================================================



/**
 * GameBox Wallet (clean + fixed)
 * - Preserves basic layout (section/head/form/grid).
 * - Fixes broken requires.
 * - Creates pending topup (amount + sender_phone + network/method).
 * - Safe wallet balance (no undefined notices).
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

/* Core includes */
require __DIR__ . '/partials/header.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';
require_login();

/* Optional config (if exists) */
if (file_exists(__DIR__.'/includes/config.php')) require_once __DIR__.'/includes/config.php';
if (file_exists(__DIR__.'/config (3).php')) require_once __DIR__.'/config (3).php';

/* Fallbacks (won’t override your config) */
if (!defined('LIBYANA_NUMBER')) define('LIBYANA_NUMBER', '0942119637');
if (!defined('MADAR_NUMBER'))   define('MADAR_NUMBER',   '0919650089');
if (!defined('CURRENCY'))       define('CURRENCY',       'د.ل');

$pdo = db();
$uid = (int)(current_user()['id'] ?? 0);
$msg = '';

/* --- Create pending topup --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['create_topup']) || isset($_POST['amount'], $_POST['sender_phone'], $_POST['network']))) {
  $amount  = (float)($_POST['amount'] ?? 0);
  $network = trim((string)($_POST['network'] ?? ''));
  $sender  = preg_replace('/\s+/', '', (string)($_POST['sender_phone'] ?? ''));

  if ($amount <= 0 || !$network || !$sender) {
    $msg = 'يرجى تعبئة كل الحقول.';
  } elseif (!preg_match('/^(?:\+?218|0)?9\d{8}$/', $sender)) {
    $msg = 'رقم المرسل غير صحيح (مثال: 09XXXXXXXXX).';
  } else {
    try {
      $ref    = 'WT'.date('ymdHis').substr(bin2hex(random_bytes(3)), 0, 6);
      $method = ($network === 'ليبيانا') ? 'libyana' : (($network === 'مدار') ? 'madar' : 'unknown');
      $stmt = $pdo->prepare("INSERT INTO topups (user_id, network, sender_phone, amount, ref_code, status, method, created_at)
                             VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())");
      $stmt->execute([$uid, $network, $sender, $amount, $ref, $method]);
      $msg = 'تم تسجيل إشعار التحويل—بانتظار التأكيد ✅ (مرجع: '.$ref.')';
    } catch (Throwable $e) {
      $msg = 'تعذّر إنشاء الطلب. تأكد من وجود جدول topups والأعمدة المطلوبة.';
    }
  }
}

/* --- Wallet balance (safe) --- */
try {
  $bal = $pdo->prepare("SELECT balance FROM wallets WHERE user_id=? FOR UPDATE");
  $bal->execute([$uid]);
  $balance = $bal->fetchColumn();
  if ($balance === false) {
    $pdo->prepare("INSERT INTO wallets (user_id,balance) VALUES (?,0)")->execute([$uid]);
    $balance = 0;
  }
} catch (Throwable $e) {
  $balance = 0;
}

/* --- Recent topups --- */
$topups = [];
try {
  $q = $pdo->prepare("SELECT created_at, amount, sender_phone, status FROM topups WHERE user_id=? ORDER BY created_at DESC, id DESC LIMIT 10");
  $q->execute([$uid]);
  $topups = $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
  $topups = [];
}
?>
<section class="section">
  <div class="head"><h2>محفظتي</h2></div>

  <?php if($msg): ?>
    <div class="form" style="border-color:#6f6;background:rgba(0,255,0,.06)"><?= $msg ?></div>
  <?php endif; ?>

  <div class="form">
    <p class="note">رصيدك الحالي: <b><?= number_format((float)$balance, 2) ?></b> <?= CURRENCY ?></p>
    <ol class="note">
      <li>حوّل إلى: مدار <b><?= htmlspecialchars(MADAR_NUMBER) ?></b> أو ليبيانا <b><?= htmlspecialchars(LIBYANA_NUMBER) ?></b>.</li>
    </ol>

    <form method="post" class="grid" style="grid-template-columns:repeat(2,1fr);gap:12px">
      <input type="hidden" name="create_topup" value="1">
      <div>
        <label class="label">المبلغ (<?= CURRENCY ?>) *</label>
        <input class="input" type="number" step="0.50" min="1" name="amount" required>
      </div>
      <div>
        <label class="label">الشبكة *</label>
        <select class="select" name="network" required>
          <option value="ليبيانا">ليبيانا</option>
          <option value="مدار">مدار</option>
        </select>
      </div>
      <div style="grid-column:1/-1">
        <label class="label">رقم المرسل *</label>
        <input class="input" name="sender_phone" placeholder="09XXXXXXXXX" inputmode="numeric" pattern="(?:\+?218|0)?9\d{8}" required>
      </div>
      <div style="grid-column:1/-1" class="actions">
        <button class="btn-royal">تسجيل إشعار التحويل</button>
      </div>
    </form>
  </div>

  <div class="form" style="margin-top:14px">
    <h3 style="margin:0 0 8px">آخر الإشعارات</h3>
    <div class="table">
      <div class="tr" style="font-weight:700">
        <div>التاريخ</div><div>المبلغ</div><div>المرسل</div><div>الحالة</div>
      </div>
      <?php foreach($topups as $t): ?>
        <div class="tr">
          <div><?= htmlspecialchars($t['created_at'] ?? '-') ?></div>
          <div><?= number_format((float)$t['amount'],2) ?> <?= CURRENCY ?></div>
          <div><?= htmlspecialchars($t['sender_phone'] ?? '-') ?></div>
          <div><?= htmlspecialchars($t['status'] ?? '-') ?></div>
        </div>
      <?php endforeach; if(empty($topups)): ?>
        <div class="tr"><div>—</div><div>—</div><div>—</div><div>لا توجد بيانات بعد.</div></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require __DIR__.'/partials/footer.php'; ?>
<script defer src="/assets/js/ui.min.js"></script>
