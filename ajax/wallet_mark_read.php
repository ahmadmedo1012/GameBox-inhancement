<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
$user = function_exists('current_user') ? current_user() : null;
if(!$user){ http_response_code(401); echo json_encode(['ok'=>false,'error':'login_required'], JSON_UNESCAPED_UNICODE); exit; }
$uid = (int)$user['id'];
$pdo = db();
$st = $pdo->prepare("UPDATE wallet_events SET is_read=1 WHERE user_id=?");
$st->execute([$uid]);
echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
