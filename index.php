<link rel="stylesheet" href="/assets/css/style.min.css">
<?php require __DIR__.'/partials/header.php'; require __DIR__.'/includes/db.php'; $pdo=db(); ?>
<section class="hero">
  <div class="hero-wrap">
    <div class="hero-left">
      <span class="badge">تجربة انسيابية ✨</span>
      <h1>اشحن ألعابك واشتراكاتك بثوانٍ عبر <span style="color:#aef5e6"><?= SITE_NAME ?></span></h1>
      <p>حوّل رصيد <b>ليبيانا/مدار</b> ثم أرسل الطلب. الدفع بالمحفظة أو تحويل مباشر.</p>
      <div class="hero-actions">
        <a class="btn-royal" href="wallet.php">تعبئة المحفظة</a>
        <a class="btn-gold" href="games.php">تصفح الألعاب</a>
      </div>
    </div>
    <div class="hero-right">
      <img decoding="async" loading="lazy" src="https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=1600&auto=format&fit=crop" alt="">
    </div>
  </div>
</section>

<section class="section">
  <div class="head">
    <h2>الأكثر شعبية</h2>
    <a class="btn" href="games.php">عرض الكل</a>
  </div>
  <div class="grid">
    <?php
    $rows=$pdo->query("SELECT f.position,s.* FROM featured f JOIN services s ON s.id=f.service_id ORDER BY f.position ASC")->fetchAll();
    if(!$rows){ $rows=$pdo->query("SELECT * FROM services ORDER BY id DESC LIMIT 6")->fetchAll(); }
    foreach($rows as $s): ?>
      <article class="card">
        <img decoding="async" loading="lazy" src="<?= $s['image'] ?>" alt="<?= htmlspecialchars($s['name']) ?>">
        <div class="body">
          <span class="tag"><?= $s['type']==='game'?'لعبة':($s['type']==='app'?'تطبيق':'اشتراك') ?></span>
          <div class="title"><?= htmlspecialchars($s['name']) ?></div>
          <div class="row">
            <span class="price">باقات</span>
            <a class="btn-royal" href="service.php?slug=<?= urlencode($s['slug']) ?>">عرض الباقات</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php require __DIR__.'/partials/footer.php'; ?>

<script defer src="/assets/js/ui.min.js"></script>
