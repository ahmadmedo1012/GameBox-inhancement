<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$user = function_exists('current_user') ? current_user() : null;
if(!$user){ http_response_code(401); echo json_encode(['ok'=>false,'error':'login_required'], JSON_UNESCAPED_UNICODE); exit; }
$uid = (int)$user['id']; $pdo = db();

$carrier = $_POST['carrier'] ?? '';
$phone   = trim($_POST['phone'] ?? '');
$amount  = (float)($_POST['amount'] ?? 0);
$txn_ref = trim($_POST['txn_ref'] ?? '');

if(!in_array($carrier, ['libyana','madar'])){ echo json_encode(['ok'=>false,'error'=>'carrier']); exit; }
if($amount <= 0){ echo json_encode(['ok'=>false,'error'=>'amount']); exit; }
if($phone === ''){ echo json_encode(['ok'=>false,'error'=>'phone']); exit; }

$pdo->exec("CREATE TABLE IF NOT EXISTS wallet_topups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  carrier ENUM('libyana','madar') NOT NULL,
  phone VARCHAR(32) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  txn_ref VARCHAR(64) NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  admin_note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  processed_at TIMESTAMP NULL,
  processed_by INT NULL,
  INDEX (user_id), INDEX (status), INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$ins = $pdo->prepare("INSERT INTO wallet_topups (user_id, carrier, phone, amount, txn_ref) VALUES (?,?,?,?,?)");
$ins->execute([$uid, $carrier, $phone, $amount, $txn_ref]);
$topup_id = (int)$pdo->lastInsertId();

$pdo->exec("CREATE TABLE IF NOT EXISTS wallet_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('refill_request','refill_approved','refill_rejected','order_paid','order_refunded','note') NOT NULL,
  amount DECIMAL(12,2) DEFAULT 0,
  currency VARCHAR(8) DEFAULT 'LYD',
  status VARCHAR(20) DEFAULT NULL,
  meta JSON NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (user_id), INDEX (created_at), INDEX (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$meta = json_encode(['topup_id'=>$topup_id, 'carrier'=>$carrier, 'phone'=>$phone], JSON_UNESCAPED_UNICODE);
$evt = $pdo->prepare("INSERT INTO wallet_events (user_id,type,amount,status,meta) VALUES (?,?,?,?,?)");
$evt->execute([$uid,'refill_request',$amount,'pending',$meta]);

echo json_encode(['ok'=>true,'id'=>$topup_id], JSON_UNESCAPED_UNICODE);
