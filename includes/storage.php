<?php
function _path($file){ return __DIR__ . '/../data/' . $file; }
function _read($file){
  $p = _path($file); if(!file_exists($p)) return [];
  $j = file_get_contents($p); return $j ? json_decode($j,true) : [];
}
function _write($file,$arr){
  $p=_path($file); if(!is_dir(dirname($p))) mkdir(dirname($p),0775,true);
  $tmp=$p.'.tmp'; file_put_contents($tmp,json_encode($arr,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
  rename($tmp,$p);
}

// المحافظ
function wallet_get($phone){
  $ws=_read('wallets.json'); foreach($ws as $w){ if($w['phone']===$phone) return $w; } return ['phone'=>$phone,'balance'=>0,'updated_at'=>date('c')];
}
function wallet_set_balance($phone,$amount){
  $ws=_read('wallets.json'); $found=false;
  foreach($ws as &$w){ if($w['phone']===$phone){ $w['balance']=$amount; $w['updated_at']=date('c'); $found=true; break; } }
  if(!$found){ $ws[]=['phone'=>$phone,'balance'=>$amount,'updated_at'=>date('c')]; }
  _write('wallets.json',$ws);
}
function wallet_add($phone,$delta){
  $w=wallet_get($phone); $new=max(0,($w['balance']??0)+$delta); wallet_set_balance($phone,$new); return $new;
}

// طلبات تعبئة المحفظة
function topup_add($phone,$amount,$network,$sender_phone){
  $items=_read('topups.json');
  $id = 'TP'.date('ymdHis').substr(md5($phone.mt_rand()),0,5);
  $items[]=['id'=>$id,'phone'=>$phone,'amount'=>floatval($amount),'network'=>$network,'sender_phone'=>$sender_phone,'status'=>'pending','created_at'=>date('c')];
  _write('topups.json',$items); return $id;
}
function topup_list(){ return array_reverse(_read('topups.json')); }
function topup_set_status($id,$status){
  $items=_read('topups.json'); foreach($items as &$t){ if($t['id']===$id){ $t['status']=$status; $t['updated_at']=date('c'); break; } }
  _write('topups.json',$items);
}
function topup_find($id){ foreach(_read('topups.json') as $t){ if($t['id']===$id) return $t; } return null; }

// الطلبات
function order_add($row){
  $orders=_read('orders.json'); 
  $row['id']='OR'.date('ymdHis').substr(md5(json_encode($row).mt_rand()),0,5);
  $row['created_at']=date('c');
  $orders[]=$row; _write('orders.json',$orders);
  return $row['id'];
}
function order_list(){ return array_reverse(_read('orders.json')); }
