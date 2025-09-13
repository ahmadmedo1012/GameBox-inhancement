<?php if (!function_exists('esc')) { function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); } } ?>
<?php
$pdo=$pdo??db();
function col_exists($pdo,$t,$c){ try{$st=$pdo->prepare("SHOW COLUMNS FROM `$t` LIKE ?"); $st->execute([$c]); return (bool)$st->fetch(); } catch(Throwable $e){ return false; } }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id<=0){ echo '<div class="alert err">مستخدم غير موجود</div></main>
<script defer src="/assets/js/site.bundle.min.js"></script>
</body></html>'; exit; }
$st=$pdo->prepare("SELECT * FROM users WHERE id=?"); $st->execute([$id]); $user=$st->fetch(PDO::FETCH_ASSOC);
if(!$user){ echo '<div class="alert err">مستخدم غير موجود</div><script src="assets/admin.js"></script></main></body></html>'; exit; }
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_user'])){
  $allowed=['username','email','phone','whatsapp','status','note'];
  $sets=[]; $vals=[]; foreach($allowed as $c){ if(isset($_POST[$c]) && col_exists($pdo,'users',$c)){ $sets[]="`$c`=?"; $vals[] = trim((string)$_POST[$c]); } }
  if($sets){ $vals[]=$id; $sql="UPDATE users SET ".implode(',',$sets)." WHERE id=?"; $u=$pdo->prepare($sql); $u->execute($vals); $msg='تم التحديث.'; $st->execute([$id]); $user=$st->fetch(PDO::FETCH_ASSOC); } else { $err='لا توجد أعمدة قابلة للتحديث.'; }
}
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['toggle_ban'])){
  if(col_exists($pdo,'users','is_banned')){ $val = isset($user['is_banned']) ? (int)!$user['is_banned'] : 1; $pdo->prepare("UPDATE users SET is_banned=? WHERE id=?")->execute([$val,$id]); $msg = $val?'تم الحظر.':'تم إلغاء الحظر.'; }
  elseif(col_exists($pdo,'users','status')){ $new = (($user['status']??'')==='banned')?'active':'banned'; $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new,$id]); $msg = ($new==='banned')?'تم الحظر.':'تم إلغاء الحظر.'; }
  else { $err='لا يوجد حقل للحظر.'; }
  $st->execute([$id]); $user=$st->fetch(PDO::FETCH_ASSOC);
}
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['wallet_amount'])){
  $amount=(float)$_POST['wallet_amount']; $note=trim((string)($_POST['wallet_note']??''));
  try{
    $pdo->beginTransaction();
    $hasLedger=true; try{$pdo->query("SELECT 1 FROM wallet_ledger LIMIT 1");}catch(Throwable $e){ $hasLedger=false; }
    if($hasLedger){
      $dir = $amount>=0 ? 'credit':'debit'; $amt = abs($amount);
      $pdo->prepare("INSERT INTO wallet_ledger (user_id,direction,amount,reference_type,reference_id,memo,created_at) VALUES (?,?,?,?,?,?,NOW())")
          ->execute([$id,$dir,$amt,'admin_adjustment',0,$note]);
    }
    $hasWallets=true; try{$pdo->query("SELECT 1 FROM wallets LIMIT 1");}catch(Throwable $e){ $hasWallets=false; }
    if($hasWallets){
      $pdo->prepare("INSERT INTO wallets (user_id,balance,updated_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE balance=GREATEST(0,balance+VALUES(balance)),updated_at=NOW()" )
          ->execute([$id,$amount]);
    }
    $pdo->commit(); $msg='تم تعديل رصيد المحفظة.';
  }catch(Throwable $e){ if($pdo->inTransaction()) $pdo->rollBack(); $err='تعذر تعديل المحفظة: '.$e->getMessage(); }
}
$wallet_balance=0.0; try{ $stb=$pdo->prepare("SELECT balance FROM wallets WHERE user_id=?"); $stb->execute([$id]); $row=$stb->fetch(PDO::FETCH_ASSOC); if($row && isset($row['balance'])) $wallet_balance=(float)$row['balance']; }catch(Throwable $e){}
?>
<div class="card"><div class="card__body">
  <h3>تعديل المستخدم #<?= (int)$id ?></h3>
  <?php if($msg): ?><div class="alert ok"><?= esc($msg) ?></div><?php endif; ?>
  <?php if($err): ?><div class="alert err"><?= esc($err) ?></div><?php endif; ?>
  <form method="post" class="card">
    <div class="card__body">
      <input type="hidden" name="update_user" value="1">
      <div class="form-row">
        <div><label>الاسم</label><input class="input" name="username" value="<?= esc($user['username']??'') ?>"></div>
        <div><label>البريد</label><input class="input" name="email" value="<?= esc($user['email']??'') ?>"></div>
      </div>
      <div class="form-row">
        <div><label>الهاتف</label><input class="input" name="phone" value="<?= esc($user['phone']??'') ?>"></div>
        <div><label>واتساب</label><input class="input" name="whatsapp" value="<?= esc($user['whatsapp']??'') ?>"></div>
      </div>
      <div class="form-row">
        <div><label>الحالة</label><input class="input" name="status" value="<?= esc($user['status']??'') ?>"></div>
        <div><label>ملاحظة</label><input class="input" name="note" value="<?= esc($user['note']??'') ?>"></div>
      </div>
      <button class="btn">حفظ البيانات</button>
      <button class="btn danger" name="toggle_ban" value="1" onclick="return confirm('تأكيد الحظر/إلغاء الحظر؟');">حظر/إلغاء</button>
    </div>
  </form>

  <div class="card__body">
    <h4>محفظة المستخدم</h4>
    <div class="meta">الرصيد الحالي: <b><?= number_format((float)$wallet_balance,2) ?></b> <?= defined('CURRENCY')?CURRENCY:'LYD' ?></div>
    <form method="post" onsubmit="return confirm('تأكيد تعديل الرصيد؟');" class="form-row">
      <div><label>المبلغ (+ إضافة / - خصم)</label><input class="input" type="number" step="0.01" name="wallet_amount" required></div>
      <div><label>ملاحظة</label><input class="input" name="wallet_note" placeholder="سبب التعديل (اختياري)"></div>
      <div style="align-self:end"><button class="btn primary">تنفيذ</button></div>
    </form>
  </div>
</div></div>
<script src="assets/admin.js"></script>
</main></body></html>
