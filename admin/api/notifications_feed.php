<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
$pdo = db();
try{ $pdo->query("SELECT 1 FROM notifications LIMIT 1"); }catch(Throwable $e){ echo json_encode(['items'=>[], 'unread'=>0]); exit; }
$items=[]; try{ $st=$pdo->prepare("SELECT id,user_id,title,body,is_read,created_at FROM notifications ORDER BY id DESC LIMIT 50"); $st->execute(); $items=$st->fetchAll(PDO::FETCH_ASSOC); }catch(Throwable $e){ $items=[]; }
$unread=0; try{ $unread=(int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read=0")->fetchColumn(); }catch(Throwable $e){}
echo json_encode(['items'=>$items,'unread'=>$unread], JSON_UNESCAPED_UNICODE);
