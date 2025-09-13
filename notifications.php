<?php
require_once __DIR__ . '/gbx_bootstrap.php';
$user_id = gbx_current_user_id();
$pdo = gbx_pdo();
$stmt = $pdo->prepare("SELECT n.id, n.title, n.body, n.url, un.seen_at, n.created_at FROM gbx_notifications n JOIN gbx_user_notifications un ON un.notification_id=n.id WHERE un.user_id=? ORDER BY n.created_at DESC LIMIT 200");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html><html lang="ar" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<script>try{var t=localStorage.getItem('gbx-theme')||(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);}catch(e){}</script>
<link rel="stylesheet" href="assets/css/site.bundle.min.css"><title>الإشعارات</title></head><body>
<header class="gbx-sticky"><div style="padding:10px 14px;display:flex;justify-content:space-between;align-items:center"><strong>الإشعارات</strong><button class="gbx-btn" onclick="GBX.toggleTheme()">تبديل الوضع</button></div></header>
<main style="padding:14px;max-width:900px;margin:auto"><div class="gbx-grid">
<?php foreach($items as $it): ?>
  <article class="gbx-card" style="padding:14px">
    <h3 style="margin:.2rem 0"><?php echo htmlspecialchars($it['title']) ?></h3>
    <p><?php echo nl2br(htmlspecialchars($it['body'])) ?></p>
    <?php if(!empty($it['url'])): ?><a class="gbx-btn secondary" href="<?php echo htmlspecialchars($it['url']) ?>">فتح الرابط</a><?php endif; ?>
    <div style="opacity:.7;margin-top:8px"><?php echo htmlspecialchars($it['created_at']) ?></div>
  </article>
<?php endforeach; ?>
</div></main>
<script src="assets/js/site.bundle.min.js"></script></body></html>
