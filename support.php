<?php
require_once __DIR__ . '/gbx_bootstrap.php';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$success=false; $ref=null; $err=null;
if($method==='POST'){
  $name=trim($_POST['name']??''); $contact=trim($_POST['contact']??'');
  $subject=trim($_POST['subject']??''); $message=trim($_POST['message']??'');
  $hp=trim($_POST['website']??''); if($hp!==''){ http_response_code(400); exit; }
  if($name&&$contact&&$subject&&$message){
    try{
      $pdo=gbx_pdo();
      $stmt=$pdo->prepare("INSERT INTO gbx_support_tickets (user_id,name,contact,subject,message,status,created_at,updated_at) VALUES (NULL,?,?,?,?, 'open', NOW(), NOW())");
      $stmt->execute([$name,$contact,$subject,$message]);
      $ref=$pdo->lastInsertId(); $success=true;
    }catch(Exception $e){ $err='حدث خطأ غير متوقع'; }
  } else { $err='الرجاء تعبئة جميع الحقول المطلوبة'; }
}
?><!doctype html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<script>try{var t=localStorage.getItem('gbx-theme')||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);}catch(e){}</script>
<link rel="stylesheet" href="assets/css/site.bundle.min.css"><title>الدعم والشكاوى</title></head><body>
<header class="gbx-sticky"><div style="padding:10px 14px;display:flex;justify-content:space-between;align-items:center"><strong>الدعم والشكاوى</strong><button class="gbx-btn" onclick="GBX.toggleTheme()">تبديل الوضع</button></div></header>
<main style="padding:14px;max-width:900px;margin:auto">
<?php if($success): ?>
  <div class="gbx-card" style="padding:14px;margin:10px 0;"><h3>تم إرسال التذكرة بنجاح</h3><p>رقمك المرجعي: <strong>#<?php echo htmlspecialchars($ref) ?></strong></p><a class="gbx-btn" href="/">عودة للرئيسية</a></div>
<?php else: ?>
  <?php if($err): ?><div class="gbx-card" style="padding:14px;margin:10px 0;background:#ef44441a;color:#ef4444"><?php echo $err ?></div><?php endif; ?>
  <form method="post" class="gbx-card" enctype="multipart/form-data" style="padding:14px;display:grid;gap:10px">
    <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
    <label>الاسم<input name="name" required style="width:100%;padding:10px;border-radius:12px;border:1px solid #ccc"></label>
    <label>بريد/هاتف للتواصل<input name="contact" required style="width:100%;padding:10px;border-radius:12px;border:1px solid #ccc"></label>
    <label>الموضوع<input name="subject" required style="width:100%;padding:10px;border-radius:12px;border:1px solid #ccc"></label>
    <label>الرسالة<textarea name="message" required rows="6" style="width:100%;padding:10px;border-radius:12px;border:1px solid #ccc"></textarea></label>
    <button class="gbx-btn" type="submit">إرسال</button>
  </form>
<?php endif; ?>
</main>
<script src="assets/js/site.bundle.min.js"></script></body></html>
