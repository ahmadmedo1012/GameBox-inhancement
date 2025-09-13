<?php
require_once __DIR__ . '/../gbx_admin_bootstrap.php';
$pdo = gbx_pdo();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM gbx_support_tickets WHERE id=?"); $stmt->execute([$id]); $t = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$t){ echo "Not found"; exit; }
$re = $pdo->prepare("SELECT * FROM gbx_support_replies WHERE ticket_id=? ORDER BY id ASC"); $re->execute([$id]); $replies = $re->fetchAll(PDO::FETCH_ASSOC);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $msg = trim($_POST['message'] ?? '');
  $status = $_POST['status'] ?? $t['status'];
  if($msg!==''){
    $admin_id = gbx_admin_id();
    $pdo->prepare("INSERT INTO gbx_support_replies (ticket_id, admin_id, message, created_at) VALUES (?,?,?, NOW())")->execute([$id,$admin_id,$msg]);
  }
  $pdo->prepare("UPDATE gbx_support_tickets SET status=?, updated_at=NOW() WHERE id=?")->execute([$status,$id]);
  header("Location: view.php?id=".$id); exit;
}
?>
<link rel="stylesheet" href="/assets/css/site.bundle.min.css">
<h2>تذكرة #<?php echo $t['id'] ?></h2>
<p><strong><?php echo htmlspecialchars($t['subject']) ?></strong></p>
<p><?php echo nl2br(htmlspecialchars($t['message'])) ?></p>
<form method="post" class="gbx-card" style="padding:10px">
  <label>الحالة
    <select name="status">
      <?php foreach(['open','pending','closed'] as $s): ?>
        <option value="<?php echo $s ?>" <?php if($s===$t['status']) echo 'selected' ?>><?php echo $s ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>رد المسؤول<textarea name="message" rows="4" style="width:100%"></textarea></label>
  <button class="gbx-btn">حفظ</button>
</form>
<h3>الردود</h3>
<?php foreach($replies as $r): ?>
  <div class="gbx-card" style="padding:10px;margin:8px 0">
    <div><?php echo nl2br(htmlspecialchars($r['message'])) ?></div>
    <div style="opacity:.7"><?php echo htmlspecialchars($r['created_at']) ?></div>
  </div>
<?php endforeach; ?>
