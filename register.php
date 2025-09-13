<link rel="stylesheet" href="/assets/css/style.min.css">
<?php require __DIR__.'/partials/header.php'; require __DIR__.'/includes/db.php'; ?>

<section class="section"><div class="head"><h2>إنشاء حساب</h2></div>

<?php

$err='';

if($_SERVER['REQUEST_METHOD']==='POST'){

  $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $email=trim($_POST['email']??'');

  $pass=$_POST['password']??'';

  if($name==''||$phone==''||$pass==''){ $err='يرجى تعبئة الاسم/الهاتف/كلمة المرور.'; }

  else {

    try{

      $pdo=db();

      $stmt=$pdo->prepare("INSERT INTO users (name,phone,email,password_hash) VALUES (?,?,?,?)");

      $stmt->execute([$name,$phone,$email?:null,password_hash($pass,PASSWORD_BCRYPT)]);

      $uid=$pdo->lastInsertId();

      $pdo->prepare("INSERT INTO wallets (user_id,balance) VALUES (?,0)")->execute([$uid]);

      $u=$pdo->query("SELECT * FROM users WHERE id=".$pdo->quote($uid))->fetch();

      login_user($u);

      header('Location: index.php'); exit;

    }catch(Throwable $e){ $err='الهاتف/البريد مستخدم بالفعل.'; }

  }

}

if($err) echo '<div class="form" style="border-color:#ff6;background:rgba(255,255,0,.06)">'.$err.'</div>';

?>

<form class="form" method="post" style="max-width:520px">

  <div class="field"><label class="label">الاسم *</label><input class="input" name="name"></div>

  <div class="field"><label class="label">رقم الهاتف *</label><input class="input" name="phone" placeholder="09XXXXXXXX"></div>

  <div class="field"><label class="label">البريد (اختياري)</label><input class="input" type="email" name="email"></div>

  <div class="field"><label class="label">كلمة المرور *</label><input class="input" type="password" name="password"></div>

  <div class="actions"><button class="btn btn-primary">إنشاء حساب</button></div>

</form>

</section>

<?php require __DIR__.'/partials/footer.php'; ?>


<script defer src="/assets/js/ui.min.js"></script>
