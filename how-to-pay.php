<link rel="stylesheet" href="/assets/css/style.min.css">
<?php include __DIR__ . '/partials/header.php'; ?>



<section class="section">

  <div class="head"><h2>طريقة الشحن</h2></div>

  <div class="form">

    <ol style="margin:0;padding-inline-start:22px;line-height:1.9">

      <li>اختر الخدمة من صفحات (الألعاب / التطبيقات / الاشتراكات) واضغط <b>اشحن الآن</b>.</li>

      <li>حوّل قيمة الباقة إلى أحد الأرقام:

        <ul class="list">

          <li>مدار: <b><?= MADAR_NUMBER ?></b></li>

          <li>ليبيانا: <b><?= LIBYANA_NUMBER ?></b></li>

        </ul>

      </li>

      <li>ارجع لصفحة <b>تأكيد الدفع</b> (نموذج الطلب) وادخل:

        <ul class="list">

          <li>رقم الهاتف الذي حوّلت منه</li>

          <li>قيمة المبلغ المُرسل</li>

          <li>بيانات الخدمة (معرّف اللاعب إن وجد…)</li>

        </ul>

      </li>

      <li>سيتم تنفيذ طلبك يدوياً أو تلقائياً وإشعارك عبر واتساب: <a href="https://wa.me/<?= preg_replace('/\D/','',WHATSAPP_NUMBER) ?>" target="_blank"><?= WHATSAPP_NUMBER ?></a></li>

    </ol>

    <p class="note">عند وجود أي ملاحظة اكتبها في خانة الملاحظات.</p>

    <div class="actions" style="margin-top:10px">

      <a class="btn btn-primary" href="order.php">اذهب لنموذج الطلب</a>

    </div>

  </div>

</section>



<?php include __DIR__ . '/partials/footer.php'; ?>


<script defer src="/assets/js/ui.min.js"></script>
