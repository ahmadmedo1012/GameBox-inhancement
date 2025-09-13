<link rel="stylesheet" href="/assets/css/style.min.css">
<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');

// Show all errors to catch the blank page reason
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "== GameBox DB Diagnostics ==\n";

// Try multiple config paths
$paths = [
  __DIR__ . '/config.php',
  __DIR__ . '/includes/config.php',
  __DIR__ . '/config (4).php',
];
$loaded = null;
foreach ($paths as $p) {
  if (file_exists($p)) { require_once $p; $loaded = $p; break; }
}
echo "Config loaded from: " . ($loaded ?: 'NOT FOUND') . "\n";

if (!defined('DB_HOST')) {
  echo "ERROR: DB constants not defined.\n";
  exit;
}

try {
  $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
  $pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  echo "DB connect: OK\n";
} catch (Throwable $e) {
  echo "DB connect ERROR: " . $e->getMessage() . "\n";
  exit;
}

// Simple checks
function q(PDO $pdo, string $sql, array $args = []) {
  $st = $pdo->prepare($sql);
  $st->execute($args);
  return $st->fetchAll();
}

try {
  $now = q($pdo, "SELECT NOW() AS now");
  echo "NOW(): " . ($now[0]['now'] ?? '-') . "\n";
} catch (Throwable $e) {
  echo "NOW() ERROR: " . $e->getMessage() . "\n";
}

$tables = ['users','topups','wallets','inbound_sms'];
foreach ($tables as $t) {
  try {
    $r = q($pdo, "SHOW TABLES LIKE ?", [$t]);
    echo "Table " . $t . ": " . (empty($r) ? 'NOT FOUND' : 'OK') . "\n";
  } catch (Throwable $e) {
    echo "SHOW TABLES " . $t . " ERROR: " . $e->getMessage() . "\n";
  }
}

// Describe topups (to be sure columns exist)
try {
  $desc = q($pdo, "DESCRIBE topups");
  echo "\n-- topups columns --\n";
  foreach ($desc as $c) {
    echo $c['Field'] . " " . $c['Type'] . "\n";
  }
} catch (Throwable $e) {
  echo "DESCRIBE topups ERROR: " . $e->getMessage() . "\n";
}

// Quick counts
try {
  $r = q($pdo, "SELECT COUNT(*) AS c FROM topups");
  echo "\nCount topups: " . ($r[0]['c'] ?? 0) . "\n";
} catch (Throwable $e) {
  echo "COUNT topups ERROR: " . $e->getMessage() . "\n";
}

echo "\nOK — copy this output to me if something shows errors.\n";

<script defer src="/assets/js/ui.min.js"></script>
