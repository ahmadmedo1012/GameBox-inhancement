<?php
// Place this at the VERY TOP of partials/header.php TEMPORARILY to reveal fatals.
@ini_set('display_errors', '1');
@ini_set('display_startup_errors', '1');
@ini_set('log_errors', '1');
@ini_set('error_log', __DIR__.'/../php-error.log');
error_reporting(E_ALL);

register_shutdown_function(function() {
  $e = error_get_last();
  if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    echo "<pre style='padding:12px;background:#111;color:#eee;border:3px dashed #f55'>";
    echo "FATAL: {$e['message']}\nFile: {$e['file']}\nLine: {$e['line']}\n";
    echo "</pre>";
  }
});
