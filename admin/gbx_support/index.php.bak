<?php
require_once __DIR__ . '/../gbx_admin_bootstrap.php';
$pdo = gbx_pdo();
$term = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM gbx_support_tickets WHERE 1";
$params=[];
if($term!==''){ $sql.=" AND (name LIKE ? OR subject LIKE ? OR contact LIKE ?)"; $params=['%'.$term.'%','%'.$term+'%','%'.$term+'%']; }
$sql.=" ORDER BY created_at DESC LIMIT 500";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?><link rel="stylesheet" href="/assets/css/site.bundle.min.css">
<h2>الدعم والشكاوى</h2>
<form method="get"><input name="q" placeholder="بحث" value="<?php echo htmlspecialchars($term) ?>"><button>بحث</button></form>
<table class="gbx-table">
<thead><tr><th>#</th><th>الاسم</th><th>اتصال</th><th>الموضوع</th><th>الحالة</th><th>أنشئت</th></tr></thead>
<tbody>
<?php foreach($rows as $r): ?>
<tr>
<td data-label="#"><a href="view.php?id=<?php echo (int)$r['id'] ?>">#<?php echo (int)$r['id'] ?></a></td>
<td data-label="الاسم"><?php echo htmlspecialchars($r['name']) ?></td>
<td data-label="اتصال"><?php echo htmlspecialchars($r['contact']) ?></td>
<td data-label="الموضوع"><?php echo htmlspecialchars($r['subject']) ?></td>
<td data-label="الحالة"><?php echo htmlspecialchars($r['status']) ?></td>
<td data-label="أنشئت"><?php echo htmlspecialchars($r['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
