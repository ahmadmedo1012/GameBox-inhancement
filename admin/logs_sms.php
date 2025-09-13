<?php

/**

 * Admin — /admin/logs_sms.php

 * Shows unmatched SMS and allows "Match Now" or "Ignore".

 * TODO: Add your admin auth check here.

 */

require_once __DIR__ . '/../includes/auto_confirm_lib.php';



// TODO: AUTH — replace with your admin guard

// if (!is_admin()) { http_response_code(403); exit('Forbidden'); }



$msg=null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_now_id'])) {

  $id = (int)$_POST['match_now_id'];

  $res = gbx_try_match_sms_log($id);

  $msg = $res['ok'] ? ($res['matched'] ? 'تمت المطابقة ✅' : 'لا يوجد طلب مطابق ❌') : ('خطأ: '.($res['msg'] ?? ''));

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ignore_id'])) {

  db()->prepare("UPDATE sms_inbox SET match_status='ignored' WHERE id=?")->execute([(int)$_POST['ignore_id']]);

  $msg = 'تم التعليم كمُتجاهَل';

}



$rows = db()->query("SELECT * FROM sms_inbox WHERE match_status='unmatched' ORDER BY received_at DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

?><!doctype html>

<html lang="ar" dir="rtl"><head><link rel="stylesheet" href="/assets/css/site.bundle.min.css">
<script>try{var m=localStorage.getItem("ui-theme")||"dark";document.documentElement.setAttribute("data-theme",m);}catch(e){document.documentElement.setAttribute("data-theme","dark");}</script>


<meta charset="utf-8"/><meta name="viewport" content="width=device-width, initial-scale=1"/>

<title>SMS Logs</title>

<style>

body{font-family:system-ui,'Cairo',sans-serif;background:#0b1220;color:#eee;margin:0}

.container{max-width:980px;margin:0 auto;padding:16px}

.card{background:#121a2b;border:1px solid #1f2a44;border-radius:14px;padding:12px;margin:10px 0}

.row{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}

.badge{padding:3px 8px;border-radius:10px;background:#1f2a44}

.btn{border:0;border-radius:10px;padding:8px 12px;margin-inline-start:6px;cursor:pointer}

.btn-primary{background:#3b82f6;color:#fff}

.btn-ghost{background:#24304d;color:#cbd5e1}

.alert{background:#0f172a;border:1px solid #334155;padding:10px;border-radius:10px;margin:10px 0}

pre{white-space:pre-wrap;word-break:break-word}

</style>

<!-- gbxv2 enable + styles -->
<script>document.documentElement.classList.add('gbxv2-enhanced');</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">






</head><body><div class="container">

<h2>رسائل SMS غير المطابقة</h2>

<?php if ($msg): ?><div class="alert"><?= htmlspecialchars($msg,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>

<?php foreach($rows as $r): ?>

  <div class="card">

    <div class="row">

      <div>

        <div class="badge">#<?= (int)$r['id'] ?> • <?= htmlspecialchars($r['received_at']) ?></div>

        <div>من: <b><?= htmlspecialchars($r['sender_msisdn'] ?: '-') ?></b> ➜ إلى: <b><?= htmlspecialchars($r['our_msisdn'] ?: '-') ?></b></div>

        <div>مبلغ: <b><?= $r['parsed_amount'] !== null ? number_format($r['parsed_amount'],2) : '-' ?></b> • مرسل متوقَّع: <b><?= htmlspecialchars($r['parsed_sender'] ?: '-') ?></b></div>

        <pre><?= htmlspecialchars($r['raw_text'] ?: '[بدون نص]') ?></pre>

      </div>

      <div>

        <form method="post" style="display:inline">

          <input type="hidden" name="match_now_id" value="<?= (int)$r['id'] ?>"/>

          <button class="btn btn-primary">مطابقة الآن</button>

        </form>

        <form method="post" style="display:inline">

          <input type="hidden" name="ignore_id" value="<?= (int)$r['id'] ?>"/>

          <button class="btn btn-ghost">تجاهُل</button>

        </form>

      </div>

    </div>

  </div>

<?php endforeach; ?>

<?php if (empty($rows)): ?><div class="alert">لا توجد رسائل غير مطابقة 🎉</div><?php endif; ?>

</div><!-- gbxv2 scripts -->




<script defer src="/assets/js/site.bundle.min.js"></script>
</body></html>

