  <?php 
 require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php'; ?>
<!DOCTYPE html><html lang="ar" dir="rtl"><head><link rel="stylesheet" href="/assets/css/site.bundle.min.css">
<script>try{var m=localStorage.getItem("ui-theme")||"dark";document.documentElement.setAttribute("data-theme",m);}catch(e){document.documentElement.setAttribute("data-theme","dark");}</script>

<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= SITE_NAME ?> - <?= SITE_TAGLINE ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap" rel="stylesheet">


<link rel="icon" href="assets/favicon.png">

</head>
<script defer src="assets/js/app.js"></script>
<body>

<header class="topbar">

<?php
// Non-destructive user detection (frontend-only guards)
$__gbx_name = null;
if(function_exists('current_user')){
  $cu = @current_user();
  if(is_array($cu)){
    $__gbx_name = $cu['name'] ?? ($cu['username'] ?? ($cu['full_name'] ?? null));
  } elseif(is_string($cu)) {
    $__gbx_name = $cu;
  }
}
if(!$__gbx_name && isset($_SESSION)){
  $u = $_SESSION['user'] ?? $_SESSION['username'] ?? $_SESSION['name'] ?? null;
  if(is_array($u)){ $__gbx_name = $u['name'] ?? ($u['username'] ?? null); }
  elseif(is_string($u)){ $__gbx_name = $u; }
}
?>
<div class="ui-brandbar" dir="rtl">
  <div class="ui-brand"><span class="ui-logo-text">gamebox</span></div>
  <div class="ui-userbar">
    <?php if($__gbx_name): ?>
      <span class="badge badge--green" herf="ui-user">مرحبًا، <?= htmlspecialchars($__gbx_name, ENT_QUOTES, 'UTF-8'); ?></span>
      <span class="ui-auth"><a class="btn red" href="logout.php" class="ui-primary">تسجيل خروج</a></span>
    <?php else: ?>
      <span class="ui-auth">
        <a class="btn-royal" href="login.php">تسجيل الدخول</a>


        <a class="btn green" href="register.php" class="ui-primary">إنشاء حساب</a>
      </span>
    <?php endif; ?>
  </div>
</div>

<div class="container">
  <a class="brand" href="index.php"><span class="logo">🎮</span><strong><?= SITE_NAME ?></strong></a>
  <nav class="nav">
  <button id="themeToggle" class="theme-toggle" aria-label="تبديل النمط">🌙</button>


    <a href="index.php">الرئيسية</a>
    <a href="games.php">الألعاب</a>
    <a href="apps.php">التطبيقات</a>
    <a href="subscriptions.php">الاشتراكات</a>
    <a href="wallet.php">المحفظة</a>
    <a class="cta" href="contact.php">تواصل</a>
    <?php if(current_user()): ?>
      <span class="badge badge--royal">مرحباً، <?= htmlspecialchars(current_user()['name']) ?></span>
      <?php if(current_user()): ?>
  <a href="account.php">حسابي</a>
<?php endif; ?>
      <a href="logout.php">خروج</a>
    <?php else: ?>
      <a href="login.php">دخول</a>
      <a href="register.php">حساب جديد</a>
    <?php endif; ?>
  </nav>
</div></header>


<main class="page"
<script defer src="/assets/js/ui.min.js"></script>
