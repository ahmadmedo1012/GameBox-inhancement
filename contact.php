<link rel="stylesheet" href="/assets/css/style.min.css">
<?php include __DIR__ . '/partials/header.php'; ?>

<section class="section">
  <div class="head"><h2>تواصل معنا</h2></div>
  <div class="form">
    <p class="note">أفضل طريقة: واتساب <a href="https://wa.me/<?= preg_replace('/\D/','',WHATSAPP_NUMBER) ?>" target="_blank"><?= WHATSAPP_NUMBER ?></a></p>
    <p class="note">فيسبوك: <a href="<?= FACEBOOK_URL ?>" target="_blank">صفحتنا</a></p>
  </div>
</section>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script defer src="/assets/js/ui.min.js"></script>
