<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!function_exists('current_user')) {
  function current_user(){ return $_SESSION['user'] ?? null; }
}
if (!function_exists('require_login')) {
  function require_login(){
    if(!current_user()){
      header('Location: login.php?next='.urlencode($_SERVER['REQUEST_URI']));
      exit;
    }
  }
}
if (!function_exists('login_user')) {
  function login_user($row){
    $_SESSION['user'] = [
      'id'=>$row['id'],
      'name'=>$row['name'],
      'phone'=>$row['phone'],
      'email'=>$row['email']
    ];
  }
}
if (!function_exists('logout_user')) {
  function logout_user(){ $_SESSION['user'] = null; session_destroy(); }
}
