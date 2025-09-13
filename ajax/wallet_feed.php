<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$user = function_exists('current_user') ? current_user() : null;
if(!$user){ http_response_code(401); echo json_encode(['ok'=>false,'error'=>'login_required'], JSON_UNESCAPED_UNICODE); exit; }
$uid = (int)$user['id'];
$pdo = db();

function tq($pdo,$sql,$params=[]){ try{$st=$pdo->prepare($sql);$st->execute($params);return $st;}catch(Throwable $e){ return false; } }
function texists($pdo,$name){
  $st = tq($pdo,"SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?",[$name]);
  return $st ? (bool)$st->fetchColumn() : false;
}
function columns($pdo,$table){
  $cols=[]; $st = tq($pdo,"SHOW COLUMNS FROM `$table`"); if($st){ foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r){ $cols[]=$r['Field']; } }
  return $cols;
}
function first_nonnull($row, $candidates){
  foreach($candidates as $c){ if(isset($row[$c]) && $row[$c]!==null){ return $row[$c]; } }
  return null;
}

$type   = $_GET['type'] ?? '';
$since  = (int)($_GET['since'] ?? 0);
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page-1) * $limit;

// -------- Balance (ledger → users → wallets) --------
$balance = 0.0;
$rows = 0;
if($st=tq($pdo,"SELECT COUNT(*) FROM wallet_ledger WHERE user_id=?",[$uid])){ $rows=(int)$st->fetchColumn(); }
if($st=tq($pdo,"SELECT COALESCE(SUM(CASE WHEN direction='credit' THEN amount ELSE 0 END),0) - COALESCE(SUM(CASE WHEN direction='debit' THEN amount ELSE 0 END),0) FROM wallet_ledger WHERE user_id=?",[$uid])){ $balance=(float)$st->fetchColumn(); }
if($rows===0 || $balance==0.0){
  if($st=tq($pdo,"SELECT wallet_balance FROM users WHERE id=?",[$uid])){ $v=$st->fetchColumn(); if($v!==false && $v!==null) $balance=(float)$v; }
  if($balance==0.0 && ($st=tq($pdo,"SELECT balance FROM users WHERE id=?",[$uid]))){ $v=$st->fetchColumn(); if($v!==false && $v!==null) $balance=(float)$v; }
  if($balance==0.0 && texists($pdo,'wallets')){
    if($st=tq($pdo,"SELECT * FROM wallets WHERE user_id=? ORDER BY id DESC LIMIT 1",[$uid])){
      if($r=$st->fetch(PDO::FETCH_ASSOC)){
        foreach(['balance','amount','wallet','credit'] as $k){ if(isset($r[$k])){ $balance=(float)$r[$k]; break; } }
      }
    }
  }
}

// -------- Unread events --------
$unread=0; $unread_list=[];
if($st=tq($pdo,"SELECT COUNT(*) FROM wallet_events WHERE user_id=? AND is_read=0",[$uid])){ $unread=(int)$st->fetchColumn(); }
if($st=tq($pdo,"SELECT id,type,amount,status,created_at,meta FROM wallet_events WHERE user_id=? AND is_read=0 ORDER BY id DESC LIMIT 10",[$uid])){ $unread_list=$st->fetchAll(PDO::FETCH_ASSOC); }

// -------- Events feed --------
$where=" WHERE user_id=? "; $params=[$uid];
if($type!==''){ $where.=" AND type=? "; $params[]=$type; }
if($since>0){ $where.=" AND id>? "; $params[]=$since; }
$events=[];
if($st=tq($pdo,"SELECT id,type,amount,status,created_at,meta,currency FROM wallet_events $where ORDER BY id DESC LIMIT $limit OFFSET $offset",$params)){
  foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r){
    $meta = $r['meta'] ? json_decode($r['meta'], true) : null;
    $title=''; $msg='';
    switch($r['type']){
      case 'refill_request':  $title='تم تسجيل طلب شحن'; $msg='قيد المراجعة'; break;
      case 'refill_approved': $title='تمت الموافقة على الشحن'; $msg='زِيد رصيدك'; break;
      case 'refill_rejected': $title='تم رفض طلب الشحن'; $msg = isset($meta['reason'])?('السبب: '.$meta['reason']):'نعتذر، تعذر القبول'; break;
      case 'order_paid':      $title='تم خصم قيمة الطلب'; $msg = isset($meta['service'])?('الخدمة: '.$meta['service']):''; break;
      case 'order_refunded':  $title='استرجاع مبلغ'; $msg = isset($meta['service'])?('الخدمة: '.$meta['service']):''; break;
      default: $title='إشعار'; $msg=''; break;
    }
    $events[]=['id'=>$r['id'],'type'=>$r['type'],'amount'=>(float)$r['amount'],'status'=>$r['status'],'currency'=>$r['currency'],'created_at'=>$r['created_at'],'meta'=>$meta,'title'=>$title,'message'=>$msg];
  }
}

// -------- Overview: last topups (wallet_topups → topups) --------
$lt_html='';
if(texists($pdo,'wallet_topups')){
  if($st=tq($pdo,"SELECT id,amount,status,created_at FROM wallet_topups WHERE user_id=? ORDER BY id DESC LIMIT 5",[$uid])){
    foreach($st->fetchAll(PDO::FETCH_ASSOC) as $t){
      $lt_html .= '<div>⛽ '.number_format((float)$t['amount'],2).' — <span class="tag">'.$t['status'].'</span> <span class="note">'.$t['created_at'].'</span></div>';
    }
  }
}
if($lt_html===''){
  if(texists($pdo,'topups')){
    $cols = columns($pdo,'topups');
    if(in_array('user_id',$cols)){
      if($st=tq($pdo,"SELECT * FROM topups WHERE user_id=? ORDER BY id DESC LIMIT 5",[$uid])){
        foreach($st->fetchAll(PDO::FETCH_ASSOC) as $t){
          $amt = (float)first_nonnull($t,['amount','value','money']);
          $stt = (string)first_nonnull($t,['status','state','approved']);
          $dt  = (string)first_nonnull($t,['created_at','created','date','time']);
          $lt_html .= '<div>⛽ '.number_format($amt,2).' — <span class="tag">'.$stt.'</span> <span class="note">'.$dt.'</span></div>';
        }
      }
    } else {
      // Try by user's phone
      $userPhone = null;
      if($st=tq($pdo,"SELECT phone FROM users WHERE id=?",[$uid])){ $p=$st->fetchColumn(); if($p) $userPhone=$p; }
      if(!$userPhone && $st=tq($pdo,"SELECT mobile FROM users WHERE id=?",[$uid])){ $p=$st->fetchColumn(); if($p) $userPhone=$p; }
      $phoneCols = array_values(array_intersect($cols, ['phone','msisdn','number','from_number','from_phone','sender']));
      if($userPhone && count($phoneCols)>0){
        $col = $phoneCols[0];
        $sql = "SELECT * FROM topups WHERE `$col`=? ORDER BY id DESC LIMIT 5";
        if($st = tq($pdo,$sql,[$userPhone])){
          foreach($st->fetchAll(PDO::FETCH_ASSOC) as $t){
            $amt = (float)first_nonnull($t,['amount','value','money']);
            $stt = (string)first_nonnull($t,['status','state','approved']);
            $dt  = (string)first_nonnull($t,['created_at','created','date','time']);
            $lt_html .= '<div>⛽ '.number_format($amt,2).' — <span class="tag">'.$stt.'</span> <span class="note">'.$dt.'</span></div>';
          }
        }
      }
    }
  }
}

// -------- Overview: last orders --------
$lo_html='';
if(texists($pdo,'orders')){
  $cols = columns($pdo,'orders');
  if(in_array('user_id',$cols)){
    if($st=tq($pdo,"SELECT * FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 5",[$uid])){
      foreach($st->fetchAll(PDO::FETCH_ASSOC) as $o){
        $amt = (float)first_nonnull($o,['total_amount','amount','price','total']);
        $dt  = (string)first_nonnull($o,['created_at','created','date','time']);
        $lo_html .= '<div>🎮 '.number_format($amt,2).' <span class="note">#'.$o['id'].' — '.$dt.'</span></div>';
      }
    }
  }
}

echo json_encode(['ok'=>true,'balance'=>$balance,'unread'=>$unread,'unread_list'=>$unread_list,'events'=>$events,'last_topups_html'=>$lt_html,'last_orders_html'=>$lo_html], JSON_UNESCAPED_UNICODE);
