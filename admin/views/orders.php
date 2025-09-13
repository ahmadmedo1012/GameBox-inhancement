<?php if (!function_exists('esc')) { function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); } } ?>
<?php
$pdo=$pdo??db();
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['order_id'], $_POST['status'])){
  try{$st=$pdo->prepare("UPDATE orders SET status=? WHERE id=?"); $st->execute([trim($_POST['status']),(int)$_POST['order_id']]); echo '<div class="alert ok">تم التحديث</div>';}catch(Throwable $e){ echo '<div class="alert err">'.esc($e->getMessage()).'</div>'; }
}
$status=$_GET['status']??''; $q=trim($_GET['q']??'');
$where=[]; $params=[];
if($status!==''){ $where[]="o.status=?"; $params[]=$status; }
if($q!==''){
  $where[]="(o.player_id LIKE ? OR o.server LIKE ? OR o.whatsapp LIKE ? OR o.account_email LIKE ? OR s.name LIKE ? OR p.label LIKE ? OR o.id=?)";
  for($i=0;$i<6;$i++) $params[]="%$q%"; $params[] = ctype_digit($q) ? (int)$q : -1;
}
$wsql = $where ? ("WHERE ".implode(" AND ",$where)) : "";
$rows=[];
try{
  $sql="SELECT o.*, s.name AS service_name, p.label AS package_label
        FROM orders o
        LEFT JOIN services s ON s.id=o.service_id
        LEFT JOIN service_packages p ON p.id=o.package_id
        $wsql
        ORDER BY o.id DESC LIMIT 500";
  $st=$pdo->prepare($sql); $st->execute($params); $rows=$st->fetchAll(PDO::FETCH_ASSOC);
}catch(Throwable $e){ echo '<div class="alert err">'.esc($e->getMessage()).'</div>'; }
?>
<div class="card"><div class="card__body">
  <h3>الطلبات</h3>
  <form class="form-row" method="get" action="index.php">
    <input type="hidden" name="view" value="orders">
    <select class="select" name="status">
      <?php foreach(['','pending','awaiting_transfer','paid','processing','completed','done','canceled','failed','refunded'] as $s): ?>
      <option value="<?= esc($s) ?>" <?= $status===$s?'selected':'' ?>><?= $s!==''?$s:'كل الحالات' ?></option>
      <?php endforeach; ?>
    </select>
    <input class="input" name="q" value="<?= esc($q) ?>" placeholder="بحث">
    <a class="btn" href="api/export_orders.php?status=<?= urlencode($status) ?>&q=<?= urlencode($q) ?>">CSV</a>
    <button class="btn primary">تطبيق</button>
  </form>
  <table class="table">
    <thead><tr><th>#</th><th>الخدمة/الباقة</th><th>السعر</th><th>الحالة</th><th>بيانات العميل</th><th>إجراء</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?><div class="meta"><?= esc($r['created_at']??'') ?></div></td>
        <td><?= esc($r['service_name']??('#'.$r['service_id'])) ?><div class="meta"><?= esc($r['package_label']??'') ?></div></td>
        <td><?= number_format((float)($r['price']??0),2) ?> <?= defined('CURRENCY')?CURRENCY:'LYD' ?></td>
        <td><span class="badge"><?= esc($r['status']??'') ?></span></td>
        <td class="small">
          <?php if(!empty($r['player_id'])): ?>ID: <b><?= esc($r['player_id']) ?></b><br><?php endif; ?>
          <?php if(!empty($r['server'])): ?>Server/Region: <?= esc($r['server']) ?><br><?php endif; ?>
          <?php if(!empty($r['account_email'])): ?>Email: <b><?= esc($r['account_email']) ?></b><br><?php endif; ?>
          <?php if(isset($r['account_password']) && $r['account_password']!==''): ?>Password: <i>محفوظ</i><br><?php endif; ?>
          <?php if(!empty($r['whatsapp'])): ?>WhatsApp: <b><?= esc($r['whatsapp']) ?></b><br><?php endif; ?>
          <?php if(!empty($r['note'])): ?><div class="meta">ملاحظة: <?= nl2br(esc($r['note'])) ?></div><?php endif; ?>
        </td>
        <td>
          <form method="post" style="display:flex;gap:8px">
            <input type="hidden" name="order_id" value="<?= (int)$r['id'] ?>">
            <select class="select" name="status">
              <?php foreach(['pending','awaiting_transfer','paid','processing','completed','done','canceled','failed','refunded'] as $s): ?>
              <option value="<?= $s ?>" <?= ($r['status']??'')===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn">حفظ</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>

</main>
<script defer src="/assets/app.js"></script>
</body></html>
