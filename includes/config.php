<?php
const SITE_NAME       = 'GameBox';
const SITE_TAGLINE    = 'شحن ألعاب وتطبيقات واشتراكات بسرعة وموثوقية';
const WHATSAPP_NUMBER = '+218910089975';
const MADAR_NUMBER    = '0919650089';
const LIBYANA_NUMBER  = '0942119637';
const CURRENCY        = 'د.ل';

// ❗ عدّل هذه القيم حسب InfinityFree
const DB_HOST = 'sql100.infinityfree.com';
const DB_NAME = 'if0_39751309_ahmed';
const DB_USER = 'if0_39751309';
const DB_PASS = 'jcLCAdbRmjLE38';

// تفعيل الأخطاء أثناء التطوير
const DEV_DEBUG = true;
if (DEV_DEBUG) { ini_set('display_errors',1); error_reporting(E_ALL); }

/* ==== BEGIN GameBox AutoConfirm (do not edit above) ==== */
if (!defined('LIBYANA_NUMBER'))              define('LIBYANA_NUMBER', '0942119637');
if (!defined('MADAR_NUMBER'))                define('MADAR_NUMBER',   '0919650089');
if (!defined('AUTO_CONFIRM_SECRET'))         define('AUTO_CONFIRM_SECRET', 'GBX_SECRET_2025'); // غيّرها لو حبيت
if (!defined('AUTOCONFIRM_LOOKBACK_HOURS'))  define('AUTOCONFIRM_LOOKBACK_HOURS', 48);
if (!defined('AMOUNT_TOLERANCE'))            define('AMOUNT_TOLERANCE', 0.01);

/* أسماء مرسلي الشبكة المقبولة */
if (!defined('LIBYANA_SENDER_ALIASES')) {
  define('LIBYANA_SENDER_ALIASES', json_encode(['libyana','ليبيانا','LIBYANA','Libyana'], JSON_UNESCAPED_UNICODE));
}
if (!defined('ALMADAR_SENDER_ALIASES')) {
  define('ALMADAR_SENDER_ALIASES', json_encode(['almadar','المدار','ALMADAR','Almadar','Almadar Aljadida','ALMADAR ALJADIDA'], JSON_UNESCAPED_UNICODE));
}
/* ==== END GameBox AutoConfirm ==== */
