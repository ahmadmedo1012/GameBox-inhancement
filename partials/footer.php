<link rel="stylesheet" href="/assets/css/style.min.css">
   </main>



<footer class="footer">

  <div class="container footer-grid">

    <div class="footer-col">

      <h4><?= defined('SITE_NAME') ? SITE_NAME : 'GameBox' ?></h4>

      <p><?= defined('SITE_TAGLINE') ? SITE_TAGLINE : 'شحن الألعاب والتطبيقات والاشتراكات بسهولة عبر رصيد ليبيانا/المدار' ?></p>

    </div>



    <div class="footer-col">

      <h4>روابط سريعة</h4>

      <ul class="list">

        <li><a href="/index.php">الرئيسية</a></li>

        <li><a href="/games.php">الألعاب</a></li>

        <li><a href="/apps.php">التطبيقات</a></li>

        <li><a href="/subscriptions.php">الاشتراكات</a></li>

        <li><a href="/wallet.php">المحفظة</a></li>

        <li><a href="/account.php">حسابي</a></li>

      </ul>

    </div>



    <div class="footer-col">

      <h4>الدعم</h4>

      <?php if (defined('WHATSAPP_NUMBER') && WHATSAPP_NUMBER): ?>

        <?php $wa = preg_replace('/\D+/', '', WHATSAPP_NUMBER); ?>

        <p>واتساب: <a href="https://wa.me/<?= $wa ?>" target="_blank" rel="noopener"><?= htmlspecialchars(WHATSAPP_NUMBER) ?></a></p>

      <?php else: ?>

        <p class="note">أضف ثابت WHATSAPP_NUMBER في config.php لعرض زر واتساب هنا.</p>

      <?php endif; ?>

    </div>

  </div>



  <div class="container footer-bottom">

    <span>© <?= date('Y') ?> <?= defined('SITE_NAME') ? SITE_NAME : 'GameBox' ?>. جميع الحقوق محفوظة.</span>

  </div>


<!-- UI Footer Enhancements -->
<div class="ui-footer-actions" dir="rtl">
  <a class="ui-cta-btn whatsapp" href="https://wa.me/218910089975" target="_blank" rel="noopener">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M16.72 14.53c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.6.07-.27-.14-1.17-.43-2.23-1.36-.82-.73-1.37-1.64-1.53-1.91-.16-.27-.02-.41.12-.54.12-.12.27-.32.41-.49.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.49-.07-.14-.61-1.46-.84-2-.22-.52-.44-.45-.61-.45h-.52c-.18 0-.49.07-.75.34s-.98.96-.98 2.34 1 2.72 1.14 2.91c.14.18 1.97 3 4.78 4.2.67.29 1.2.46 1.61.59.68.22 1.3.19 1.8.11.55-.08 1.6-.65 1.83-1.27.23-.63.23-1.17.16-1.28-.07-.11-.25-.18-.52-.32zM12.01 2C6.49 2 2 6.49 2 12.01c0 1.76.46 3.41 1.26 4.85L2 22l5.29-1.24a10.01 10.01 0 0 0 4.72 1.2c5.52 0 10.01-4.49 10.01-10.01C22.02 6.49 17.53 2 12.01 2z"/></svg>
    واتساب
  </a>
  <a class="ui-cta-btn support" href="support.php">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a9 9 0 00-9 9v3a3 3 0 003 3h1v-8H6a7 7 0 0114 0h-1v8h1a3 3 0 003-3v-3a9 9 0 00-9-9zm-1 18h2v2h-2v-2z"/></svg>
    دعم فني
  </a>
</div>

</footer>



</nav>







<script defer src="/assets/js/account.mobile.js?v=3"></script>

<script>
(function(){
  if(window.__gb_side_fill_v1) return; window.__gb_side_fill_v1 = true;
  function $(s,sc){ return (sc||document).querySelector(s); }
  function $all(s,sc){ return Array.from((sc||document).querySelectorAll(s)); }
  function drawer(){ return document.querySelector('#sideMenu, .drawer, .side-menu'); }
  function add(nav, href, label){
    if(!nav) return;
    const exists = $all('a', nav).some(a => (a.getAttribute('href')||'')===href);
    if(exists) return;
    const a = document.createElement('a');
    a.href = href; a.textContent = label; a.setAttribute('data-close','');
    nav.appendChild(a);
  }
  document.addEventListener('DOMContentLoaded', function(){
    const d = drawer(); if(!d) return;
    const nav = d.querySelector('nav, .drawer-nav, .menu, .menu-list') || d;
    add(nav, '/index.php', 'الرئيسية');
    add(nav, '/games.php', 'الألعاب');
    add(nav, '/apps.php', 'التطبيقات');
    add(nav, '/subscriptions.php', 'الاشتراكات');
    add(nav, '/account.php', 'حسابي');
    add(nav, '/wallet.php', 'المحفظة');
  });
})();
</script>



<script defer src="/assets/js/site.bundle.min.js"></script>
</body>

</html>



<!-- === Smart Bottom Tabbar (added minimally) === -->
<nav class="smart-tabbar" dir="rtl" aria-label="التنقل السريع">
  <a href="/index.php" class="tab-item" aria-label="الرئيسية">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z"/></svg>
    <span>الرئيسية</span>
  </a>
  <a href="/games.php" class="tab-item" aria-label="الألعاب">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M3 5h8v6H3zM13 5h8v6h-8zM3 13h8v6H3zM13 13h8v6h-8z"/></svg>
    <span>الألعاب</span>
  </a>
  <a href="/apps.php" class="tab-item" aria-label="التطبيقات">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M4 7h16v3H4zM4 12h16v3H4zM4 17h10v3H4z"/></svg>
    <span>التطبيقات</span>
  </a>
  <a href="/subscriptions.php" class="tab-item" aria-label="الاشتراكات">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M6 7h12v2H6zm0 4h12v2H6zm0 4h8v2H6z"/></svg>
    <span>الاشتراكات</span>
  </a>
  <a href="/account.php" class="tab-item" aria-label="حسابي">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-5 0-9 2.5-9 5.5V22h18v-2.5C21 16.5 17 14 12 14Z"/></svg>
    <span>حسابي</span>
  </a>
  <button type="button" class="tab-item menu-toggle" data-menu-toggle aria-controls="sideMenu" aria-label="القائمة">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M3 6h18v2H3zm0 5h18v2H3zm0 5h18v2H3z"/></svg>
    <span>القائمة</span>
  </button>
</nav>


<!-- === Minimal Footer Controller (drawer + theme + tabbar) === -->
<script>
(function(){
  if(window.__gb_footer_min_guard) return; window.__gb_footer_min_guard = true;
  const root = document.documentElement;
  const KEY  = 'gb_theme';
  const $ = (s,sc)=> (sc||document).querySelector(s);
  const $$ = (s,sc)=> Array.from((sc||document).querySelectorAll(s));

  function ensureBackdrop(){
    let b = $('#menuBackdrop');
    if(!b){ b = document.createElement('div'); b.id='menuBackdrop'; b.className='drawer-backdrop'; document.body.appendChild(b); }
    return b;
  }
  function findDrawer(){ return document.querySelector('#sideMenu, .drawer, .side-menu'); }
  function isOpen(d){ return d && d.classList.contains('open'); }
  function openDrawer(d){
    const b = ensureBackdrop(); if(!d) return;
    d.classList.add('open'); d.setAttribute('aria-hidden','false');
    document.body.classList.add('menu-open'); document.body.style.overflow='hidden';
    b.classList.add('show');
    const inp = d.querySelector('#drawerThemeToggle'); if(inp) inp.checked = (root.getAttribute('data-theme')==='dark');
  }
  function closeDrawer(d){
    const b = ensureBackdrop(); if(!d) return;
    d.classList.remove('open'); d.setAttribute('aria-hidden','true');
    document.body.classList.remove('menu-open'); document.body.style.overflow='';
    b.classList.remove('show');
  }

  // Enhance existing drawer non-invasively: add "حسابي" + theme switch if missing
  (function enhanceDrawer(){
    const d = findDrawer(); if(!d) return;
    const nav = d.querySelector('.drawer-nav, nav') || d;
    if(nav && !Array.from(nav.querySelectorAll('a')).some(a => (a.getAttribute('href')||'').indexOf('/account.php') !== -1)){
      const a = document.createElement('a'); a.href='/account.php'; a.textContent='حسابي'; a.setAttribute('data-close','');
      nav.appendChild(a);
    }
    let utils = d.querySelector('.drawer-utils');
    if(!utils){ utils = document.createElement('div'); utils.className='drawer-utils'; d.appendChild(utils); }
    if(!utils.querySelector('[data-theme-toggle]')){
      const wrap = document.createElement('label'); wrap.className = 'switch';
      wrap.innerHTML = '<span>الوضع الداكن</span><input type="checkbox" id="drawerThemeToggle" data-theme-toggle><span class="track"><span class="thumb"></span></span>';
      utils.appendChild(wrap);
    }
  })();

  (function bindBottomMenu(){
    const btn = document.querySelector('.smart-tabbar .menu-toggle');
    if(!btn) return;
    const handler = (e)=>{
      e.preventDefault(); e.stopPropagation();
      const d = findDrawer(); if(!d) return;
      isOpen(d) ? closeDrawer(d) : openDrawer(d);
    };
    document.addEventListener('click', (e)=>{ if(e.target.closest('.smart-tabbar .menu-toggle')) handler(e); }, true);
    document.addEventListener('touchstart', (e)=>{ if(e.target.closest('.smart-tabbar .menu-toggle')) handler(e); }, {passive:false, capture:true});
    ensureBackdrop().addEventListener('click', ()=> closeDrawer(findDrawer()));
    document.addEventListener('mousedown', (e)=>{
      const d = findDrawer(); if(!d || !isOpen(d)) return;
      if(!e.target.closest('.drawer, .side-menu')) closeDrawer(d);
    }, true);
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape'){ closeDrawer(findDrawer()); } });
    document.addEventListener('click', (e)=>{ if(e.target.closest('.drawer [data-close], .drawer a')) closeDrawer(findDrawer()); });
  })();

  function setTheme(mode){
    root.setAttribute('data-theme', mode);
    try{ localStorage.setItem(KEY, mode); }catch(e){}
    ['#drawerThemeToggle','#themeToggle','[data-theme-toggle]'].forEach(sel=>{
      document.querySelectorAll(sel).forEach(el=>{
        if(el.tagName==='INPUT') el.checked = (mode==='dark');
        else if(!el.dataset.lockLabel){ el.textContent = (mode==='dark' ? '☀️' : '🌙'); el.title = (mode==='dark' ? 'الوضع الفاتح' : 'الوضع الداكن'); }
      });
    });
  }
  (function initTheme(){
    let saved=null; try{ saved = localStorage.getItem(KEY);}catch(e){}
    if(saved==='dark' || saved==='light'){ setTheme(saved); }
  })();
  document.addEventListener('click', (e)=>{
    const t = e.target.closest('[data-theme-toggle],#themeToggle');
    if(!t) return;
    e.preventDefault(); e.stopPropagation();
    const next = (root.getAttribute('data-theme')==='dark' ? 'light' : 'dark');
    setTheme(next);
  }, true);

  (function tabbarScroll(){
    const tabbar = document.querySelector('.smart-tabbar'); if(!tabbar) return;
    let lastY = window.scrollY, ticking=false;
    function onScroll(){
      const y = window.scrollY;
      if (y > lastY + 8)      tabbar.classList.add('hide');
      else if (y < lastY - 8) tabbar.classList.remove('hide');
      if (y < 10) tabbar.classList.remove('hide');
      lastY = y; ticking=false;
    }
    window.addEventListener('scroll', function(){
      if(!ticking){ window.requestAnimationFrame(onScroll); ticking=true; }
    }, {passive:true});
  })();

  // Minimal CSS fallback for tabbar + switch (only if missing in your CSS)
  (function cssFallback(){
    if(document.getElementById('gb-footer-fallback-css')) return;
    const style = document.createElement('style'); style.id='gb-footer-fallback-css';
    style.textContent = '.drawer-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.40);opacity:0;pointer-events:none;transition:opacity .24s;z-index:120}.drawer-backdrop.show{opacity:1;pointer-events:auto}.smart-tabbar{position:fixed;inset-inline:0;bottom:10px;z-index:110;margin-inline:auto;max-width:720px;padding:8px 10px;background:rgba(15,23,42,.92);color:#fff;border:1px solid rgba(255,255,255,.15);border-radius:14px;box-shadow:0 14px 36px rgba(0,0,0,.28);display:grid;grid-template-columns:repeat(5,1fr);gap:6px;transform:translateY(0);transition:transform .24s,opacity .24s}html[data-theme="light"] .smart-tabbar{background:#fff;color:#0b1222;border-color:rgba(11,18,34,.15)}.smart-tabbar.hide{transform:translateY(120%);opacity:0;pointer-events:none}.smart-tabbar .tab-item{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:8px 4px;border-radius:12px;text-decoration:none;color:inherit;border:1px solid transparent;background:transparent;cursor:pointer}.smart-tabbar .tab-item.is-active{border-color:rgba(255,255,255,.22);background:rgba(255,255,255,.06);font-weight:800}html[data-theme="light"] .smart-tabbar .tab-item.is-active{border-color:rgba(11,18,34,.18);background:rgba(11,18,34,.06)}.drawer-utils{padding:10px 12px;border-top:1px solid rgba(255,255,255,.12);display:flex;align-items:center;justify-content:space-between;gap:10px}.switch{position:relative;display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-weight:700}.switch input{position:absolute;inset:0;opacity:0}.switch .track{width:44px;height:26px;border-radius:999px;background:#334155;position:relative;transition:.25s}.switch .thumb{width:20px;height:20px;border-radius:999px;background:#fff;position:absolute;top:3px;left:3px;transition:transform .25s}.switch input:checked + .track{background:#22c55e}.switch input:checked + .track .thumb{transform:translateX(18px)}';
    document.head.appendChild(style);
  })();

})();
</script>