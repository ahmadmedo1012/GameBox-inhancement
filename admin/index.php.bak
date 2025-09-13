<?php
declare(strict_types=1);
error_reporting(E_ALL);
@ini_set('display_errors','0');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if (session_status()===PHP_SESSION_NONE) { session_start(); }

if (!function_exists('esc')) {
  function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
}
function view_allowed($v){
  return in_array($v,[
    'dashboard','analytics','orders','services','packages','users','user_edit',
    'topups','notifications','content','settings'
  ], true);
}
$pdo = db();

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php'); exit;
}

$view = $_GET['view'] ?? 'dashboard';
if (!view_allowed($view)) $view = 'dashboard';

include __DIR__ . '/partials/nav.php';
include __DIR__ . '/views/' . $view . '.php';
