<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
$pdo = db();
function safe_col($pdo,$sql){ try{ return (int)$pdo->query($sql)->fetchColumn(); }catch(Throwable $e){ return 0; } }
function has_table($pdo,$t){ try{$pdo->query("SELECT 1 FROM `$t` LIMIT 1"); return true; }catch(Throwable $e){ return false; } }
function fetch_one($pdo,$sql){ try{$st=$pdo->query($sql); $r=$st->fetch(PDO::FETCH_ASSOC); return $r?:null; }catch(Throwable $e){ return null; } }
$orders_pending = safe_col($pdo,"SELECT COUNT(*) FROM orders WHERE status IN ('pending','awaiting_transfer','processing')");
$latest_order = fetch_one($pdo,"SELECT o.id,s.name AS service,o.created_at FROM orders o LEFT JOIN services s ON s.id=o.service_id ORDER BY o.id DESC LIMIT 1");
$table = has_table($pdo,'wallet_topups')?'wallet_topups':'topups';
$topups_pending = safe_col($pdo,"SELECT COUNT(*) FROM `$table` WHERE COALESCE(status,state,'pending') IN ('pending','awaiting')");
$latest_topup = fetch_one($pdo,"SELECT id,user_id,created_at FROM `$table` ORDER BY id DESC LIMIT 1");
$notif_table = has_table($pdo,'notifications');
$notifications_unread = $notif_table ? safe_col($pdo,"SELECT COUNT(*) FROM notifications WHERE is_read=0") : 0;
$latest_notification = $notif_table ? fetch_one($pdo,"SELECT id,title,created_at FROM notifications ORDER BY id DESC LIMIT 1") : null;
echo json_encode([
  'now'=>date('c'),
  'orders_pending'=>$orders_pending,
  'topups_pending'=>$topups_pending,
  'notifications_unread'=>$notifications_unread,
  'latest_order'=>$latest_order,
  'latest_topup'=>$latest_topup,
  'latest_notification'=>$latest_notification,
], JSON_UNESCAPED_UNICODE);
