 <?php if (!function_exists('esc')) { function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); } } ?>
<?php
$pdo=$pdo??db();
$table='topups'; 
try{ $pdo->query("SELECT 1 FROM topups LIMIT 1"); }
catch(Throwable $e){ $table='wallet_topups'; }
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['id'], $_POST['status'])){
  try {
    $id  = (int)$_POST['id'];
    $new = trim((string)$_POST['status']);

    $pdo->beginTransaction();

    // 1) fetch current row for user_id/amount and lock it
    $st = $pdo->prepare("SELECT id, user_id, amount, status FROM `$table` WHERE id=? FOR UPDATE");
    $st->execute([$id]);
    $t = $st->fetch(PDO::FETCH_ASSOC);
    if (!$t) { throw new Exception('Topup not found'); }

    $uid = (int)$t['user_id'];
    $old = (string)$t['status'];

    // 2) update status + timestamps
    if ($new === 'approved') {
      $pdo->prepare("UPDATE `$table` SET status='approved', confirmed_at=NOW(), updated_at=NOW() WHERE id=?")->execute([$id]);
    } else {
      $pdo->prepare("UPDATE `$table` SET status=?, updated_at=NOW() WHERE id=?")->execute([$new, $id]);
    }

    // 3) recompute wallet balance from approved topups (single source of truth)
    if ($uid > 0) {
      $sum = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM `$table` WHERE user_id=? AND status='approved'");
      $sum->execute([$uid]);
      $newBalance = (float)$sum->fetchColumn();

      // update or insert into wallets
      $u = $pdo->prepare("UPDATE wallets SET balance=? WHERE user_id=?");
      $u->execute([$newBalance, $uid]);
      if ($u->rowCount() === 0) {
        $pdo->prepare("INSERT INTO wallets (user_id, balance) VALUES (?, ?)")->execute([$uid, $newBalance]);
      }
    }

    $pdo->commit();
    $msg = 'تم تحديث الحالة وتحديث رصيد العميل بنجاح.';
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $msg = 'خطأ: '.$e->getMessage();
  }
}

$rows=[]; try{ $rows=$pdo->query("SELECT * FROM `$table` ORDER BY id DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC);}catch(Throwable $e){}
?>
<div class="card"><div class="card__body">
  <h3>شحن المحفظة</h3>
  <?php if($msg): ?><div class="alert ok"><?= esc($msg) ?></div><?php endif; ?>
  <table class="table"><thead><tr><th>#</th><th>user_id</th><th>amount</th><th>status</th><th>created_at</th><th>إجراء</th></tr></thead><tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= (int)$r['user_id'] ?></td>
      <td><?= number_format((float)($r['amount']??0),2) ?></td>
      <td><span class="badge"><?= esc($r['status']??($r['state']??'')) ?></span></td>
      <td><?= esc($r['created_at']??($r['date']??'')) ?></td>
      <td>
        <form method="post" style="display:flex;gap:8px">
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <select class="select" name="status">
            <?php foreach(['pending','awaiting','approved','rejected','done','accepted','ok'] as $s): ?>
              <option value="<?= $s ?>" <?= (($r['status']??$r['state']??'')===$s)?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn">حفظ</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div></div>

</main>
<script defer src="/assets/js/site.bundle.min.js"></script>
</body></html>