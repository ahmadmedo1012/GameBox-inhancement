<?php
declare(strict_types=1);
error_reporting(E_ALL);
@ini_set('display_errors','0');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if (session_status()===PHP_SESSION_NONE) { session_start(); }

if (!function_exists('esc')) {
  function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
}
$pdo = db();
function table_exists(PDO $pdo, string $t){ try{$pdo->query("SELECT 1 FROM `$t` LIMIT 1"); return true;} catch(Throwable $e){ return false; } }
function col_exists(PDO $pdo, string $t, string $c){ try{$st=$pdo->prepare("SHOW COLUMNS FROM `$t` LIKE ?"); $st->execute([$c]); return (bool)$st->fetch(); } catch(Throwable $e){ return false; } }

$hasTable = table_exists($pdo,'admin_users');
$userCol=null; $passCol=null;
if ($hasTable) {
  foreach (['email','username','user','login'] as $c) { if (col_exists($pdo,'admin_users',$c)) { $userCol=$c; break; } }
  foreach (['password_hash','password','pass'] as $c) { if (col_exists($pdo,'admin_users',$c)) { $passCol=$c; break; } }
  if ($userCol && $passCol) {
    try {
      $cnt = (int)$pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
      if ($cnt===0) {
        $defaultUser = ($userCol==='email') ? 'admin@gamebox.local' : 'admin';
        $hash = password_hash('admin12345', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO admin_users (`$userCol`,`$passCol`) VALUES (?,?)")->execute([$defaultUser,$hash]);
      }
    } catch(Throwable $e){}
  }
}

$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $user = trim($_POST['user'] ?? '');
  $pass = trim($_POST['password'] ?? '');
  $ok=false; $admin_id=null;
  if ($hasTable && $userCol && $passCol) {
    $st=$pdo->prepare("SELECT id, `$passCol` AS p FROM admin_users WHERE `$userCol`=? LIMIT 1");
    $st->execute([$user]);
    $row=$st->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $stored=(string)$row['p'];
      if (password_verify($pass,$stored) || $pass===$stored) { $ok=true; $admin_id=(int)$row['id']; }
    }
  } else {
    if (($user==='admin' || $user==='admin@gamebox.local') && $pass==='admin12345'){ $ok=true; $admin_id=1; }
  }
  if ($ok){ $_SESSION['admin_id']=$admin_id; header('Location: index.php'); exit; } else { $msg='بيانات الدخول غير صحيحة.'; }
}
?>
<!doctype html><html lang="ar" dir="rtl">
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
<title>تسجيل الدخول — GameBox Admin</title>
<body class="admin">
<div class="login-wrap card">
  <div class="card__body">
    <h2>لوحة GameBox</h2>
    <?php if($msg): ?><div class="alert err"><?= esc($msg) ?></div><?php endif; ?>
    <form method="post" class="form">
      <label>البريد أو اسم المستخدم</label>
      <input class="input" name="user" required placeholder="admin أو admin@gamebox.local">
      <label>كلمة المرور</label>
      <input class="input" type="password" name="password" required placeholder="********">
      <button class="btn primary">دخول</button>
      <div class="meta">افتراضي: admin / admin@gamebox.local — كلمة المرور: admin12345</div>
    </form>
  </div>
</div>

<script defer src="/assets/js/site.bundle.min.js"></script>
</body></html>
