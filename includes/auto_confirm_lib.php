<?php
declare(strict_types=1);
mb_internal_encoding('UTF-8');

// ------------- CONFIG -------------
// إذا كان لديك ملف إعدادات يعرّف ثوابت الاتصال والسر، قم بتضمينه هنا:
$__cfg = __DIR__.'/includes/config.php';
if (!file_exists($__cfg)) {
  // احتياطي: جرّب ملف آخر لو كنتَ تستخدم اسمًا مختلفًا
  $__cfg = __DIR__.'/../config.php';
}
if (file_exists($__cfg)) require_once $__cfg;

// لو لم يكن لديك ثابت السر، عرّفه هنا أو في config.php
if (!defined('AUTO_CONFIRM_SECRET')) {
  define('AUTO_CONFIRM_SECRET', 'GBX_SECRET_2025');
}

// دالة اتصال PDO عامة (تستعمل اتصالك إن كان $pdo موجودًا)
function db(): PDO {
  global $pdo;
  if ($pdo instanceof PDO) return $pdo;
  static $conn = null;
  if ($conn instanceof PDO) return $conn;

  // إن كانت ثوابت DB_* معرّفة في config.php استعملها:
  if (defined('DB_HOST')) {
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    $conn = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $conn;
  }

  // وإلاّ حاول الاعتماد على includes/db.php في مشروعك
  $db_file = __DIR__.'/db.php';
  if (file_exists($db_file)) { require_once $db_file; }
  if (function_exists('db')) return \db();
  throw new RuntimeException('No DB connection available.');
}

// ------------ أدوات مساعدة ------------
function normalize_digits(string $s): string {
  // تحويل الأرقام العربية/الفارسية إلى إنجليزية
  $map = [];
  for ($i=0;$i<10;$i++){
    $map[mb_chr(0x0660+$i)] = (string)$i;
    $map[mb_chr(0x06F0+$i)] = (string)$i;
  }
  $s = strtr($s, $map);
  $s = str_replace(["\u{200F}","\u{202B}","\u{202A}","\u{202C}","\u{00A0}","،"], ["","","",""," ",","], $s);
  return trim(preg_replace('/\s+/u', ' ', $s));
}
function detect_provider(string $from, string $text): string {
  $f = mb_strtolower($from); $t = mb_strtolower($text);
  if (str_contains($f,'libyana') || str_contains($t,'ليبيانا')) return 'libyana';
  if (str_contains($f,'almadar') || str_contains($t,'المدار') || str_contains($t,'المشترك الكريم')) return 'madar';
  return 'unknown';
}
function parse_sms_fields(string $provider, string $text): array {
  $text = normalize_digits($text);
  $amount = null; $sender = null;
  if ($provider==='madar') {
    // المشترك الكريم ... تم تحويل X د.ل ... من الرقم NNN
    if (preg_match('/تم تحويل\s+(\d+(?:[.,]\d{1,3})?)\s*د\.ل.*?من الرقم\s+(\d{9,15})/u', $text, $m)){
      $amount = (float)str_replace(',', '.', $m[1]); $sender = $m[2];
    }
  } elseif ($provider==='libyana') {
    // تم تحويل X دينار من الرقم NNN ...
    if (preg_match('/تم تحويل\s+(\d+(?:[.,]\d{1,3})?)\s*دينار.*?من الرقم\s+(\d{9,15})/u', $text, $m)){
      $amount = (float)str_replace(',', '.', $m[1]); $sender = $m[2];
    }
  }
  if ($sender===null && preg_match('/من الرقم\s+(\d{9,15})/u', $text, $m)) $sender = $m[1];
  if ($amount===null && preg_match('/\b(\d+(?:[.,]\d{1,3})?)\s*(?:د\.ل|دينار)/u', $text, $m)) $amount = (float)str_replace(',', '.', $m[1]);

  if ($sender!==null) {
    if (preg_match('/^2189(\d{8})$/', $sender, $m2)) $sender = '09'.$m2[1];
    $sender = preg_replace('/\D+/', '', $sender);
  }
  return [$amount, $sender];
}
function last9(?string $msisdn): ?string {
  if (!$msisdn) return null; $d = preg_replace('/\D+/', '', $msisdn); if (strlen($d)<9) return null; return substr($d,-9);
}

// إنشاء جدول لوج اختياري
function ensure_inbound_table(): void {
  $pdo = db();
  $pdo->exec("CREATE TABLE IF NOT EXISTS inbound_sms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider VARCHAR(20) DEFAULT NULL,
    sender_last9 VARCHAR(9) DEFAULT NULL,
    amount DECIMAL(10,3) DEFAULT NULL,
    sent_to VARCHAR(20) DEFAULT NULL,
    text MEDIUMTEXT,
    matched TINYINT(1) DEFAULT 0,
    topup_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(provider), INDEX(sender_last9), INDEX(matched)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
function log_inbound(string $provider, ?string $l9, ?float $amount, ?string $sent_to, string $text, array $match): int {
  $pdo = db();
  $st = $pdo->prepare("INSERT INTO inbound_sms(provider,sender_last9,amount,sent_to,text,matched,topup_id) VALUES(?,?,?,?,?,?,?)");
  $st->execute([$provider,$l9,$amount,$sent_to,$text, ($match['matched']??false)?1:0, $match['topup_id'] ?? null]);
  return (int)$pdo->lastInsertId();
}

// المطابقة والاعتماد — مبني تحديدًا على جدولك `topups`
function find_and_approve(float $amount, string $sender_last9, ?string $provider=null): array {
  $pdo = db();
  // ابحث عن أحدث pending يطابق المبلغ + آخر 9 من sender_phone
  $sql = "SELECT id, user_id FROM topups
          WHERE status='pending' AND amount = :amt
            AND RIGHT(sender_phone,9) = :l9
          ORDER BY id DESC LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([':amt'=>$amount, ':l9'=>$sender_last9]);
  $row = $st->fetch();
  if (!$row) return ['matched'=>false,'msg'=>'no pending match'];

  // اعتمد العملية: status, method (=provider), confirmed_at, updated_at
  $pdo->beginTransaction();
  try {
    $up = $pdo->prepare("UPDATE topups
                         SET status='approved',
                             method = COALESCE(:p, method),
                             confirmed_at = NOW(),
                             updated_at = NOW()
                         WHERE id=:id");
    $up->execute([':p'=>$provider, ':id'=>$row['id']]);

    $pdo->commit();
    return ['matched'=>true,'topup_id'=>$row['id']];
  } catch (Throwable $e) {
    $pdo->rollBack();
    return ['matched'=>false,'msg'=>'db error: '.$e->getMessage()];
  }
}

// نقطة الدخول العمومية (Webhook)
function handle_incoming(array $req): array {
  ensure_inbound_table();

  $secret = $req['secret'] ?? '';
  if ($secret !== AUTO_CONFIRM_SECRET) return ['ok'=>false,'error'=>'unauthorized'];

  $from    = trim((string)($req['from'] ?? ''));
  $text    = trim((string)($req['message'] ?? $req['text'] ?? ''));
  $sent_to = trim((string)($req['sent_to'] ?? ''));

  if ($from==='' && $text==='') {
    return ['ok'=>true,'matched'=>false,'reason'=>'missing_fields','note'=>'no from/text in payload'];
  }

  $provider = detect_provider($from, $text);
  [$amount, $sender] = parse_sms_fields($provider, $text);
  $l9 = last9($sender);

  if ($amount === null || $l9 === null) {
    $sms_id = log_inbound($provider, $l9, $amount, $sent_to, $text, ['matched'=>false]);
    return ['ok'=>true,'sms_id'=>$sms_id,'matched'=>false,'reason'=>'missing_fields','provider'=>$provider,'parsed_amount'=>$amount,'parsed_sender'=>$l9];
  }

  $match = find_and_approve((float)$amount, $l9, $provider);
  $sms_id = log_inbound($provider, $l9, $amount, $sent_to, $text, $match);

  return array_merge(['ok'=>true,'sms_id'=>$sms_id,'provider'=>$provider,'amount'=>$amount,'sender'=>$l9], $match);
}
