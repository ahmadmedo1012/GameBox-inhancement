<?php
// لا تستخدم أي شرطة طويلة — داخل الأكواد. الأسعار غير المحددة = null
$SERVICES = [
  // ألعاب
  'pubg-mobile' => [
    'type'=>'game',
    'name'=>'PUBG Mobile',
    'image'=>'https://images.unsplash.com/photo-1542751110-97427bbecf20?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'60 UC','price'=>5],
      ['label'=>'325 UC','price'=>24],
      ['label'=>'660 UC','price'=>45],
      ['label'=>'1800 UC','price'=>110],
    ],
  ],
  'free-fire' => [
    'type'=>'game','name'=>'Free Fire',
    'image'=>'https://images.unsplash.com/photo-1559403011-0f3b3c7e7993?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'100 Diamonds','price'=>4],
      ['label'=>'310 Diamonds','price'=>11],
      ['label'=>'520 Diamonds','price'=>18],
    ],
  ],
  'mlbb' => [
    'type'=>'game','name'=>'Mobile Legends',
    'image'=>'https://images.unsplash.com/photo-1611996575749-79a3a250f33b?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'86 Diamonds','price'=>6],
      ['label'=>'172 Diamonds','price'=>12],
      ['label'=>'257 Diamonds','price'=>17],
    ],
  ],

  // تطبيقات
  'spotify' => [
    'type'=>'app','name'=>'Spotify Premium',
    'image'=>'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'شهر','price'=>null],
    ],
  ],
  'youtube-premium' => [
    'type'=>'app','name'=>'YouTube Premium',
    'image'=>'https://images.unsplash.com/photo-1551817958-20204d6ab8f7?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'شهر','price'=>null],
    ],
  ],
  'discord-nitro' => [
    'type'=>'app','name'=>'Discord Nitro',
    'image'=>'https://images.unsplash.com/photo-1518933165971-611dbc9c412d?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'شهري','price'=>null],
    ],
  ],

  // اشتراكات
  'netflix-4k' => [
    'type'=>'sub','name'=>'Netflix Premium 4K',
    'image'=>'https://images.unsplash.com/photo-1589402902458-44f9fb3f3f1b?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'شهر (4K)','price'=>45],
    ],
  ],
  'shahid-vip' => [
    'type'=>'sub','name'=>'Shahid VIP',
    'image'=>'https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=1200&auto=format&fit=crop',
    'packages'=>[
      ['label'=>'شهر','price'=>null],
    ],
  ],
];
