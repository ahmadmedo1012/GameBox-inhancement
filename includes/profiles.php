
<?php
// GameBox helpers for service requirement profiles

if (!function_exists('gb_profile_label')) {
  function gb_profile_label($key){
    $map = [
      'game_id_wapp'       => 'لعبة: ID + واتساب',
      'app_id_wapp'        => 'تطبيق: ID + واتساب',
      'app_account_wapp'   => 'تطبيق بث مباشر/حساب: بريد + رمز + واتساب',
      'subscription_wapp'  => 'اشتراك: واتساب فقط',
    ];
    return $map[$key] ?? $key;
  }
}

if (!function_exists('gb_requirements_for')) {
  function gb_requirements_for(array $service){
    $cat = $service['category'] ?? ($service['type'] ?? null);
    $prof = $service['requirements_profile'] ?? null;
    if (!$prof) {
      if ($cat === 'subscription') $prof = 'subscription_wapp';
      elseif ($cat === 'game')    $prof = 'game_id_wapp';
      else                        $prof = 'app_id_wapp'; // default for apps
    }
    if (!$cat) {
      // derive category from profile
      if (in_array($prof, ['game_id_wapp'], true)) $cat = 'game';
      elseif ($prof === 'subscription_wapp') $cat = 'subscription';
      else $cat = 'app';
    }
    return [$cat, $prof];
  }
}
