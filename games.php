<link rel="stylesheet" href="/assets/css/style.min.css">
<?php
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/db.php';
require __DIR__.'/partials/header.php';

$q   = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort= isset($_GET['sort']) ? $_GET['sort'] : 'name';

$rows = [];
try {
  $pdo = function_exists('db') ? db() : null;
  if ($pdo) {
    // المحاولة 1: فئة الألعاب game
    $sql  = "SELECT id, name, slug, image FROM services WHERE category = :cat";
    $pars = [':cat' => 'game'];
    if ($q !== '') { $sql .= " AND name LIKE :q"; $pars[':q'] = '%'.$q.'%'; }
    if ($sort === 'new')      $sql .= " ORDER BY id DESC";
    else if ($sort === 'name')$sql .= " ORDER BY name ASC";
    else                      $sql .= " ORDER BY name ASC";
    $st = $pdo->prepare($sql); $st->execute($pars); $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    // المحاولة 2 (توافق قديم): إن لم توجد فئات game بعد، نجلب كل ما ليس App/Subscription + القيَم الفارغة/NULL
    if (!$rows) {
      $sql2 = "SELECT id, name, slug, image FROM services
               WHERE (category IS NULL OR category = '' OR category NOT IN ('app','subscription'))";
      $pars2 = [];
      if ($q !== '') { $sql2 .= " AND name LIKE :q"; $pars2[':q'] = '%'.$q.'%'; }
      if ($sort === 'new')       $sql2 .= " ORDER BY id DESC";
      else /* name/default */    $sql2 .= " ORDER BY name ASC";
      $st2 = $pdo->prepare($sql2); $st2->execute($pars2); $rows = $st2->fetchAll(PDO::FETCH_ASSOC);
    }
  }
} catch (Throwable $e) {
  // لتجنب الصفحة البيضاء على الاستضافة المجانية، لا نطبع تفاصيل الخطأ هنا
}
?>

<!-- Fallback CSS صغير (يمكن نقله لاحقًا لـ style.css) -->
<style>
.grid.cards-grid{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
@media(min-width:420px){.grid.cards-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(min-width:640px){.grid.cards-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.grid.cards-grid .card{border-radius:14px;overflow:hidden;border:1px solid rgba(0,0,0,.10);background:#fff;transition:transform .18s,box-shadow .18s}
html[data-theme="dark"] .grid.cards-grid .card{border-color:rgba(255,255,255,.14);background:rgba(255,255,255,.04)}
.grid.cards-grid .card:active{transform:translateY(1px) scale(.995)}
.grid.cards-grid .card .media{display:block;width:100%;aspect-ratio:16/9;object-fit:cover}
.card-compact .title{font-size:clamp(13px,3.4vw,15px);line-height:1.25;font-weight:800}
.card-compact .meta{font-size:clamp(11px,3vw,13px);opacity:.8}
.grid.cards-grid .card .row{display:flex;align-items:center;justify-content:space-between;gap:8px}
.results-info{margin:6px 0 10px;font-size:13px;opacity:.8}
.input{padding:10px 12px;border:1px solid rgba(0,0,0,.15);border-radius:12px}
.select{padding:10px 12px;border:1px solid rgba(0,0,0,.15);border-radius:12px;background:#fff}
html[data-theme="dark"] .input,html[data-theme="dark"] .select{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.18);color:inherit}
.btn{padding:10px 12px;border-radius:12px;border:1px solid rgba(0,0,0,.15);background:#0ea5e9;color:#fff}
html[data-theme="dark"] .btn{border-color:rgba(255,255,255,.18)}
.section{margin:10px 0}
.hero h1{margin:0 0 6px}
.hero-desc{margin:0 0 8px;opacity:.85}
</style>

<section class="page container" dir="rtl" data-page-listing>
  <div class="section hero">
    <h1>الألعاب</h1>
    <p class="hero-desc">تصفّح واختر الباقة المناسبة — البحث فوري ومنظم.</p>
    <form method="get" class="actions" style="display:flex;gap:8px;flex-wrap:wrap">
      <input class="input" type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="ابحث بالاسم…" data-instant-search>
      <select class="select" name="sort" data-sort>
        <option value="name" <?= $sort==='name'?'selected':'' ?>>أبجديًا</option>
        <option value="new"  <?= $sort==='new'?'selected':''  ?>>الأحدث</option>
      </select>
      <button class="btn-royal">تصفية</button>
      <span class="results-info" data-count></span>
    </form>
  </div>

  <div class="section">
    <div class="grid cards-grid card-compact" data-cards>
      <?php if (empty($rows)): ?>
        <div class="empty muted" style="padding:22px;text-align:center;border:1px dashed rgba(125,125,125,.35);border-radius:12px">لا توجد نتائج.</div>
      <?php else: foreach ($rows as $s): 
        $key = mb_strtolower($s['name']);
      ?>
        <a class="card" href="service.php?slug=<?= urlencode($s['slug']) ?>" data-id="<?= (int)$s['id'] ?>" data-key="<?= htmlspecialchars($key) ?>">
          <img decoding="async" class="media" loading="lazy"
               src="<?= htmlspecialchars($s['image'] ?: 'https://source.unsplash.com/600x400/?'.urlencode($s['slug'])) ?>"
               alt="<?= htmlspecialchars($s['name']) ?>">
          <div class="p-2" style="padding:10px">
            <div class="row">
              <div class="title"><?= htmlspecialchars($s['name']) ?></div>
<button class="btn-royal" onclick="location.href='service.php?slug=<?= urlencode($s['slug']) ?>'">عرض الباقات</button>            </div>
          </div>
        </a>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<script>
// === Instant Search & Sort (client-side) ===
(function(){
  if(window.__gb_instant_search_v1) return; window.__gb_instant_search_v1 = true;
  const norm = (s)=> (s||'').toString().toLowerCase()
    .replace(/[\u064B-\u0652]/g,'')
    .replace(/[إأآا]/g,'ا').replace(/ى/g,'ي').replace(/ؤ/g,'و').replace(/ئ/g,'ي').replace(/ة/g,'ه')
    .trim();
  function setup(scope){
    const input = scope.querySelector('[data-instant-search]');
    const sortSel = scope.querySelector('[data-sort]');
    const grid = scope.querySelector('[data-cards]');
    const cards = grid ? Array.from(grid.querySelectorAll('[data-key]')) : [];
    const counter = scope.querySelector('[data-count]');
    if(!grid || !input) return;
    function apply(){
      const q = norm(input.value);
      const mode = (sortSel && sortSel.value) || 'name';
      let visible = 0;
      cards.forEach(el=>{
        const key = el.dataset.key;
        const show = !q || key.indexOf(q) !== -1;
        el.style.display = show ? '' : 'none';
        if(show) visible++;
      });
      if(sortSel){
        const getKey = (el)=> el.dataset.key || '';
        const byName = (a,b)=> getKey(a).localeCompare(getKey(b));
        const byNew  = (a,b)=> (parseInt(b.dataset.id||'0') - parseInt(a.dataset.id||'0'));
        const arr = cards.filter(el => el.style.display !== 'none');
        if(mode==='name') arr.sort(byName);
        else if(mode==='new') arr.sort(byNew);
        arr.forEach(el=> grid.appendChild(el));
      }
      if(counter){ counter.textContent = visible + ' نتيجة'; }
    }
    input.addEventListener('input', apply);
    if(sortSel) sortSel.addEventListener('change', apply);
    apply();
  }
  document.querySelectorAll('[data-page-listing]').forEach(setup);
})();
</script>

<?php require __DIR__.'/partials/footer.php'; ?>

<script defer src="/assets/js/ui.min.js"></script>
