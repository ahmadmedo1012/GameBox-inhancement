<link rel="stylesheet" href="/assets/css/style.min.css">
<?php require __DIR__.'/partials/header.php'; require __DIR__.'/includes/db.php'; ?>

<section class="section"><div class="head"><h2>تسجيل الدخول</h2></div>

<?php

$err=''; $next = $_GET['next'] ?? 'index.php';

if($_SERVER['REQUEST_METHOD']==='POST'){

  $user=trim($_POST['user']??''); $pass=$_POST['password']??'';

  if($user==''||$pass==''){ $err='أدخل الهاتف/البريد وكلمة المرور.'; }

  else{

    $pdo=db();

    $stmt=$pdo->prepare("SELECT * FROM users WHERE phone=? OR email=?");

    $stmt->execute([$user,$user]);

    $u=$stmt->fetch();

    if($u && password_verify($pass,$u['password_hash'])){

      login_user($u); header('Location: '.$next); exit;

    }else{$err='بيانات الدخول غير صحيحة.';}

  }

}

if($err) echo '<div class="form" style="border-color:#f66;background:rgba(255,0,0,.06)">'.$err.'</div>';

?>

<form class="form" method="post" style="max-width:420px">

  <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">

  <div class="field"><label class="label">الهاتف أو البريد</label><input class="input" name="user"></div>

  <div class="field"><label class="label">كلمة المرور</label><input class="input" type="password" name="password"></div>

  <div class="actions"><button class="btn btn-primary">دخول</button></div>

</form>

</section>

<?php require __DIR__.'/partials/footer.php'; ?>


<script defer src="/assets/js/ui.min.js"></script>
