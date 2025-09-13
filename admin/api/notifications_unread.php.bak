<?php
require_once __DIR__ . '/../gbx_bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$user_id = gbx_current_user_id();
$pdo = gbx_pdo();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM gbx_user_notifications WHERE user_id=? AND seen_at IS NULL");
$stmt->execute([$user_id]);
echo json_encode(['unread'=>(int)$stmt->fetchColumn()]);
