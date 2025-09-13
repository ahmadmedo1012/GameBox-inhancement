<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if(!current_user()){ http_response_code(401); echo json_encode(['ok'=>false,'error'=>'login_required'], JSON_UNESCAPED_UNICODE); exit; }
$pdo=db(); $uid=(int)current_user()['id']; $service_id=(int)($_POST['service_id']??0);
if($service_id<=0){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'bad_service'], JSON_UNESCAPED_UNICODE); exit; }
$pdo->exec("CREATE TABLE IF NOT EXISTS favorites (user_id INT NOT NULL, service_id INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (user_id, service_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$st=$pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND service_id=?"); $st->execute([$uid,$service_id]); $exists=(bool)$st->fetchColumn();
if($exists){ $pdo->prepare('DELETE FROM favorites WHERE user_id=? AND service_id=?')->execute([$uid,$service_id]); echo json_encode(['ok'=>true,'state'=>'removed'], JSON_UNESCAPED_UNICODE); }
else{ $pdo->prepare('INSERT INTO favorites (user_id,service_id) VALUES (?,?)')->execute([$uid,$service_id]); echo json_encode(['ok'=>true,'state'=>'added'], JSON_UNESCAPED_UNICODE); }
