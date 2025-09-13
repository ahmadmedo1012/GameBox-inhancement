<?php if (!function_exists('esc')) { function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); } } ?>
<?php
$pdo=$pdo??db();
$q=trim($_GET['q']??'');
$where = $q!=='' ? "WHERE (username LIKE :q OR email LIKE :q OR phone LIKE :q OR whatsapp LIKE :q)" : "";
$st=$pdo->prepare("SELECT * FROM users $where ORDER BY id DESC LIMIT 400");
if($q!==''){ $like="%$q%"; $st->bindParam(':q',$like); }
$st->execute(); $rows=$st->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="card"><div class="card__body">
  <h3>المستخدمون</h3>
  <form class="form-row" method="get" action="index.php">
    <input type="hidden" name="view" value="users">
    <input class="input" name="q" value="<?= esc($q) ?>" placeholder="بحث: اسم / بريد / هاتف / واتساب">
    <button class="btn">بحث</button>
  </form>
  <table class="table"><thead><tr><th>#</th><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>واتساب</th><th>حالة</th><th>إجراء</th></tr></thead><tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= (int)$r['id'] ?></td>
      <td><?= esc($r['username']??'') ?></td>
      <td><?= esc($r['email']??'') ?></td>
      <td><?= esc($r['phone']??'') ?></td>
      <td><?= esc($r['whatsapp']??'') ?></td>
      <td><?php if(array_key_exists('is_banned',$r)): ?><span class="badge"><?= ((int)$r['is_banned'])?'محظور':'نشط' ?></span><?php elseif(array_key_exists('status',$r)): ?><span class="badge"><?= esc($r['status']??'') ?></span><?php else: ?><span class="badge">—</span><?php endif; ?></td>
      <td><a class="btn" href="index.php?view=user_edit&id=<?= (int)$r['id'] ?>">تعديل</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div></div>

</main>
<script defer src="/assets/js/site.bundle.min.js"></script>
</body></html>
