<?php
/* Wallet bootstrap fix — minimal & safe.
   - Place this file at: /includes/wallet_bootstrap_fix.php
   - Add ONE line at the very top of wallet.php:
       <?php require_once __DIR__ . '/includes/wallet_bootstrap_fix.php'; ?>
*/
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Load config
foreach ([__DIR__.'/config.php', __DIR__.'/../config.php', __DIR__.'/../config (4).php'] as $c) {
  if (file_exists($c)) { require_once $c; break; }
}

// Safe constants
if (!defined('CURRENCY')) { define('CURRENCY', 'د.ل'); }
if (!defined('MADAR_NUMBER'))   { define('MADAR_NUMBER', '0942119637'); }
if (!defined('LIBYANA_NUMBER')) { define('LIBYANA_NUMBER', '0919650089'); }

// Safe variables
if (!isset($msg)) { $msg = null; }
if (!isset($balance)) { $balance = 0.0; }
if (!isset($topups) || !is_array($topups)) { $topups = []; }

// Ensure PDO
if (!isset($pdo) || !($pdo instanceof PDO)) {
  if (defined('DB_HOST')) {
    try {
      $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
      $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    } catch (Throwable $e) { /* ignore */ }
  }
}

// Resolve uid
if (!isset($uid) || !$uid) {
  $uid = 0;
  if (isset($_SESSION['user']['id']))    $uid = (int)$_SESSION['user']['id'];
  elseif (isset($_SESSION['user_id']))   $uid = (int)$_SESSION['user_id'];
  elseif (isset($_SESSION['uid']))       $uid = (int)$_SESSION['uid'];
}

// Live balance (if possible)
if ($uid && isset($pdo) && ($pdo instanceof PDO)) {
  try {
    $st = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM topups WHERE user_id = ? AND status = 'approved'");
    $st->execute([$uid]);
    $balance = (float)$st->fetchColumn();
  } catch (Throwable $e) { /* keep old $balance */ }
}
?>