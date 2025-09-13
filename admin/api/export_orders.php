<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
$pdo = db();
$status=$_GET['status']??''; $q=trim($_GET['q']??'');
$where=[]; $params=[];
if($status!==''){ $where[]="o.status=?"; $params[]=$status; }
if($q!==''){ $where[]="(o.player_id LIKE ? OR o.whatsapp LIKE ? OR o.account_email LIKE ? OR s.name LIKE ? OR p.label LIKE ? OR o.id=?)"; for($i=0;$i<5;$i++) $params[]="%$q%"; $params[] = ctype_digit($q)?(int)$q:-1; }
$wsql = $where ? ("WHERE ".implode(" AND ",$where)) : "";
$sql = "SELECT o.id,o.created_at,s.name AS service,p.label AS package,o.price,o.status,o.player_id,o.server,o.account_email,o.whatsapp,o.note
        FROM orders o
        LEFT JOIN services s ON s.id=o.service_id
        LEFT JOIN service_packages p ON p.id=o.package_id
        $wsql
        ORDER BY o.id DESC";
$st=$pdo->prepare($sql); $st->execute($params); $rows=$st->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="orders_export.csv"');
$out=fopen('php://output','w');
fputcsv($out, array_keys($rows[0]??['id','created_at','service','package','price','status','player_id','server','account_email','whatsapp','note']));
foreach($rows as $r){ fputcsv($out,$r); }
fclose($out);
