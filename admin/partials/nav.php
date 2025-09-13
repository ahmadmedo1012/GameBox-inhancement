<?php $v = $_GET['view'] ?? 'dashboard'; ?>
<!doctype html><html lang="ar" dir="rtl">
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<title>GameBox Admin</title>
<body class="admin">
<header class="topbar glass">
  <button id="toggleSidebar" class="btn icon" aria-label="القائمة"><span class="ico">☰</span></button>
  <div class="brand">GameBox <span class="muted">Admin</span></div>
  <div class="spacer"></div>
  <button id="toggleTheme" class="btn">الوضع</button>
  <button id="toggleSound" class="btn icon" title="الصوت">🔊</button>
  <div class="notif">
    <button id="notifBell" class="btn icon"><span class="ico">🔔</span><span id="notifBadge" class="badge pulse" hidden>0</span></button>
    <div id="notifDrop" class="notif-drop" hidden>
      <div class="notif-head">الإشعارات
        <button class="btn small" id="notifMarkAll">تحديد الكل كمقروء</button>
      </div>
      <div id="notifList" class="notif-list"><div class="empty">لا إشعارات</div></div>
    </div>
  </div>
  <a href="logout.php" class="btn">خروج</a>
</header>
<aside id="sidebar" class="sidebar glass">
  <a class="nav-item <?= $v==='dashboard'?'active':'' ?>" href="index.php?view=dashboard">الواجهة</a>
  <a class="nav-item <?= $v==='analytics'?'active':'' ?>" href="index.php?view=analytics">التحليلات</a>
  <a class="nav-item <?= $v==='orders'?'active':'' ?>" href="index.php?view=orders">الطلبات</a>
  <a class="nav-item <?= $v==='services'?'active':'' ?>" href="index.php?view=services">الخدمات</a>
  <a class="nav-item <?= $v==='packages'?'active':'' ?>" href="index.php?view=packages">الباقات</a>
  <a class="nav-item <?= $v==='users'?'active':'' ?>" href="index.php?view=users">المستخدمون</a>
  <a class="nav-item <?= $v==='topups'?'active':'' ?>" href="index.php?view=topups">شحن المحفظة</a>
  <a class="nav-item <?= $v==='notifications'?'active':'' ?>" href="index.php?view=notifications">الإشعارات</a>
  <a class="nav-item <?= $v==='content'?'active':'' ?>" href="index.php?view=content">المحتوى</a>
  <a class="nav-item <?= $v==='settings'?'active':'' ?>" href="index.php?view=settings">الإعدادات</a>
</aside>
<main class="content">
<div id="toast"></div>
