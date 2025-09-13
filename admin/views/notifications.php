<?php if (!function_exists('esc')) { function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); } } ?>
<?php
$pdo=$pdo??db();
function has_table($pdo,$t){ try{$pdo->query("SELECT 1 FROM `$t` LIMIT 1"); return true; }catch(Throwable $e){ return false; } }
$ok = has_table($pdo,'notifications');
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && $ok){
  try{
    if(isset($_POST['create'])){
      $pdo->prepare("INSERT INTO notifications (user_id,title,body,is_read,created_at) VALUES (?,?,?,?,NOW())")
          ->execute([($_POST['user_id']!=='')?(int)$_POST['user_id']:null, trim($_POST['title']), trim($_POST['body']), 0]);
      $msg='تم الإنشاء.';
    }elseif(isset($_POST['mark'],$_POST['id'])){
      $is_read = $_POST['mark']==='read'?1:0;
      $pdo->prepare("UPDATE notifications SET is_read=? WHERE id=?")->execute([$is_read,(int)$_POST['id']]);
      $msg = $is_read ? 'وُضع كمقروء.' : 'وُضع كغير مقروء.';
    }elseif(isset($_POST['delete'],$_POST['id'])){
      $pdo->prepare("DELETE FROM notifications WHERE id=?")->execute([(int)$_POST['id']]);
      $msg='تم الحذف.';
    }
  }catch(Throwable $e){ $msg='خطأ: '.$e->getMessage(); }
}
$rows = $ok ? $pdo->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 300")->fetchAll(PDO::FETCH_ASSOC) : [];
?>
<div class="card"><div class="card__body">
  <h3>الإشعارات</h3>
  <?php if(!$ok): ?><div class="alert err">لا يوجد جدول notifications — اللوحة تعمل دون توقف لكن لن تظهر إشعارات.</div><?php endif; ?>
  <?php if($msg): ?><div class="alert ok"><?= esc($msg) ?></div><?php endif; ?>
  <details class="card"><summary class="card__body"><b>إنشاء إشعار</b></summary>
    <div class="card__body">
      <form method="post">
        <input type="hidden" name="create" value="1">
        <div class="form-row">
          <div><label>لـ (user_id اختياري)</label><input class="input" name="user_id"></div>
          <div><label>العنوان</label><input class="input" name="title" required></div>
        </div>
        <div><label>النص</label><textarea class="textarea" name="body" rows="3"></textarea></div>
        <button class="btn primary">إنشاء</button>
      </form>
    </div>
  </details>
  <table class="table"><thead><tr><th>#</th><th>user_id</th><th>العنوان</th><th>النص</th><th>مقروء؟</th><th>تاريخ</th><th>إجراء</th></tr></thead><tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= esc((string)($r['user_id']??'—')) ?></td>
      <td><?= esc($r['title']??'') ?></td>
      <td class="small"><?= nl2br(esc($r['body']??'')) ?></td>
      <td><?= isset($r['is_read'])&&(int)$r['is_read']?'✔':'—' ?></td>
      <td><?= esc($r['created_at']??'') ?></td>
      <td>
        <form method="post" style="display:flex;gap:6px">
          <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
          <button class="btn" name="mark" value="read">مقروء</button>
          <button class="btn" name="mark" value="unread">غير مقروء</button>
          <button class="btn danger" name="delete" value="1" onclick="return confirm('حذف؟');">حذف</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div></div>

</main>
<script defer src="/assets/app.js"></script>
</body></html>
