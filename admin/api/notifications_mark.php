<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
$pdo=db();
try{ $pdo->query("SELECT 1 FROM notifications LIMIT 1"); }catch(Throwable $e){ echo json_encode(['ok'=>false,'err'=>'no_table']); exit; }
if(isset($_GET['all'])){ $pdo->exec("UPDATE notifications SET is_read=1 WHERE is_read=0"); echo json_encode(['ok'=>true]); exit; }
$raw=file_get_contents('php://input'); $j=json_decode($raw,true)?:[];
$id=(int)($j['id']??0); $act=(string)($j['act']??'');
if($id<=0){ echo json_encode(['ok'=>false]); exit; }
if($act==='read'){ $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=?")->execute([$id]); echo json_encode(['ok'=>true]); }
elseif($act==='del'){ $pdo->prepare("DELETE FROM notifications WHERE id=?")->execute([$id]); echo json_encode(['ok'=>true]); }
else { echo json_encode(['ok'=>false]); }
