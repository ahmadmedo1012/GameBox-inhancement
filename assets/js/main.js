/* =======================================================
   GameBox - main.js (core UI for all pages) — v4.4
   Fixes & features:
   - Theme no‑flicker (respects pre-set data-theme in <head>)
   - Drawer (mobile) stable open/close + backdrop
   - Tabbar active-state
   - Toast (global), Ripple on .btn
   - Lazy images, topbar shadow
   - Copy helper for [data-copy]
   - Wallet amount chips: .amount-chips .chip[data-amount] -> #amount
   - Prevent double submit on any <form>
   ======================================================= */

(function(){
  const root = document.documentElement;
  const THEME_KEY = 'gb_theme';

  /* ========== THEME ========== */
  const prefersDark = () => window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  function setTheme(mode){
    root.setAttribute('data-theme', mode);
    root.classList.toggle('light', mode === 'light');
    try{ localStorage.setItem(THEME_KEY, mode); }catch(e){}
    updateThemeToggle(mode);
  }
  function getSavedTheme(){ try{ return localStorage.getItem(THEME_KEY); }catch(e){ return null; } }
  function initTheme(){
    // Respect theme applied early in <head> to avoid flicker
    const pre = root.getAttribute('data-theme');
    if (pre === 'light' || pre === 'dark'){ updateThemeToggle(pre); return; }
    const saved = getSavedTheme();
    const mode = saved ? saved : (prefersDark() ? 'dark' : 'light');
    setTheme(mode);
  }
  function updateThemeToggle(mode){
    const btn = document.getElementById('themeToggle');
    if(!btn) return;
    btn.dataset.mode = mode;
    btn.innerHTML = (mode === 'dark') ? '☀️' : '🌙';
    btn.setAttribute('aria-label', mode==='dark' ? 'التبديل إلى الفاتح' : 'التبديل إلى الداكن');
  }

  /* ========== DRAWER (mobile side menu) ========== */
  function initDrawer(){
    const btn = document.getElementById('menuToggle');
    const drawer = document.getElementById('sideMenu');
    if(!btn || !drawer) return;

    // Ensure backdrop exists and is attached to body
    let backdrop = document.getElementById('menuBackdrop');
    if(!backdrop){
      backdrop = document.createElement('div');
      backdrop.id = 'menuBackdrop';
      backdrop.className = 'drawer-backdrop';
      document.body.appendChild(backdrop);
    }

    function open(){
      if(drawer.parentElement !== document.body) document.body.appendChild(drawer);
      drawer.classList.add('open');
      backdrop.classList.add('show');
      drawer.setAttribute('aria-hidden','false');
      document.body.style.overflow='hidden';
    }
    function close(){
      drawer.classList.remove('open');
      backdrop.classList.remove('show');
      drawer.setAttribute('aria-hidden','true');
      document.body.style.overflow='';
    }

    btn.addEventListener('click', open);
    backdrop.addEventListener('click', close);
    drawer.addEventListener('click', (e)=>{ if(e.target.closest('[data-close],a')) close(); });
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') close(); });

    // Optional: clone top nav into drawer once
    const src = document.querySelector('.nav');
    const dst = drawer.querySelector('.drawer-nav');
    if (src && dst && !dst.childElementCount) {
      const clone = src.cloneNode(true);
      clone.querySelectorAll('#themeToggle,#menuToggle').forEach(el=>el.remove());
      dst.innerHTML = clone.innerHTML;
    }
  }

  /* ========== TABBAR active state ========== */
  function initTabbar(){
    const current = (location.pathname || '/').replace(/\/+$/,'') || '/';
    document.querySelectorAll('.tabbar a').forEach(a => {
      const href = (a.getAttribute('href') || '').replace(/\/+$/,'');
      if (!href) return;
      const isRoot = href === '' || href === '/' || href.endsWith('/index.php');
      if ((isRoot && (current==='/' || current.endsWith('/index.php'))) || (!isRoot && current.endsWith(href))) {
        a.setAttribute('aria-current','page');
      }
    });
  }

  /* ========== TOAST (global) ========== */
  window.showToast = function(msg){
    let el = document.querySelector('.toast');
    if(!el){
      el = document.createElement('div');
      el.className = 'toast';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.classList.add('show');
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(()=> el.classList.remove('show'), 2200);
  };

  /* ========== RIPPLE on .btn ========== */
  function initRipple(){
    document.body.addEventListener('click', function(e){
      const el = e.target.closest('.btn');
      if(!el) return;
      const rect = el.getBoundingClientRect();
      const circle = document.createElement('span');
      const size = Math.max(rect.width, rect.height);
      circle.className = 'ripple';
      circle.style.width = circle.style.height = size + 'px';
      circle.style.left = (e.clientX - rect.left - size/2) + 'px';
      circle.style.top  = (e.clientY - rect.top  - size/2) + 'px';
      el.appendChild(circle);
      setTimeout(()=>circle.remove(), 600);
    }, {passive:true});
  }

  /* ========== Lazy images + Topbar shadow ========== */
  function initLazyAndTopbar(){
    document.querySelectorAll('img:not([loading])').forEach(img => img.loading='lazy');
    const topbar = document.querySelector('.topbar, .header');
    if(topbar){
      const onScroll = () => topbar.classList.toggle('scrolled', window.scrollY > 6);
      onScroll();
      window.addEventListener('scroll', onScroll, {passive:true});
    }
  }

  /* ========== Copy helper ========== */
  function initCopy(){
    document.body.addEventListener('click', async (e)=>{
      const btn = e.target.closest('[data-copy]');
      if(!btn) return;
      try{
        await navigator.clipboard.writeText(btn.dataset.copy);
        const old = btn.textContent;
        btn.textContent = '✓ تم النسخ';
        setTimeout(()=> btn.textContent = old, 1200);
      }catch(err){}
    });
  }

  /* ========== Wallet amount chips helper ========== */
  function initAmountChips(){
    const container = document.querySelector('.amount-chips');
    const amount = document.getElementById('amount');
    if(!container || !amount) return;
    const chips = Array.from(container.querySelectorAll('.chip'));
    const activate = (val)=> chips.forEach(c => c.classList.toggle('active', parseFloat(c.dataset.amount) === parseFloat(val)));
    chips.forEach(ch => ch.addEventListener('click', ()=>{ amount.value = ch.dataset.amount; activate(amount.value); amount.focus(); }));
    if (amount.value) activate(amount.value);
    amount.addEventListener('input', ()=>{
      const v = amount.value;
      const any = chips.some(c => parseFloat(c.dataset.amount) === parseFloat(v));
      if (!any) chips.forEach(c => c.classList.remove('active')); else activate(v);
    });
  }

  /* ========== Prevent double submit on ANY form ========== */
  function initNoDoubleSubmit(){
    document.addEventListener('submit', (e)=>{
      const form = e.target.closest('form');
      if(!form) return;
      const btn = form.querySelector('button[type="submit"], .btn[type="submit"]');
      if(btn){
        btn.disabled = true;
        const old = btn.textContent;
        btn.textContent = 'جارٍ الإرسال…';
        setTimeout(()=>{ try{btn.disabled=false; btn.textContent = old;}catch(e){} }, 3000); // safety
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    initTheme();
    initDrawer();
    initTabbar();
    initRipple();
    initLazyAndTopbar();
    initCopy();
    initAmountChips();
    initNoDoubleSubmit();

    const themeBtn = document.getElementById('themeToggle');
    if (themeBtn){
      themeBtn.addEventListener('click', function(){
        const current = root.getAttribute('data-theme') || (root.classList.contains('light') ? 'light' : 'dark');
        const next = current === 'dark' ? 'light' : 'dark';
        setTheme(next);
        if (typeof showToast === 'function') showToast(next==='dark' ? 'تم تفعيل النمط الداكن 🌙' : 'تم تفعيل النمط الفاتح ☀️');
      });
    }
  });
})();


;/* === GameBox v9 Enhancer (guarded, safe) === */
if(!window.__gb_init_v9){ window.__gb_init_v9 = true; (function(){
  const root=document.documentElement, KEY='gb_theme';
  const $=(s,sc)=> (sc||document).querySelector(s);
  const $$=(s,sc)=> Array.from((sc||document).querySelectorAll(s));
  function prefersDark(){ return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches; }
  function setTheme(mode){
    root.setAttribute('data-theme', mode);
    try{ localStorage.setItem(KEY, mode); }catch(e){}
    const t=$('#themeToggle'); if(t){ t.textContent=(mode==='dark'?'☀️':'🌙'); t.title=(mode==='dark'?'الوضع الفاتح':'الوضع الداكن'); }
  }
  (function initTheme(){
    const pre=root.getAttribute('data-theme');
    if(pre==='dark'||pre==='light'){ setTheme(pre); }
    else{ let s=null; try{s=localStorage.getItem(KEY);}catch(e){} setTheme(s? s : (prefersDark()?'dark':'light')); }
  })();
  (function initDrawer(){
    const btn=$('#menuToggle'), drawer=$('#sideMenu'); if(!btn||!drawer) return;
    let backdrop=$('#menuBackdrop'); if(!backdrop){ backdrop=document.createElement('div'); backdrop.id='menuBackdrop'; backdrop.className='drawer-backdrop'; document.body.appendChild(backdrop); }
    const open=()=>{ drawer.classList.add('open'); backdrop.classList.add('show'); document.body.style.overflow='hidden'; };
    const close=()=>{ drawer.classList.remove('open'); backdrop.classList.remove('show'); document.body.style.overflow=''; };
    btn.addEventListener('click', open, {passive:true}); backdrop.addEventListener('click', close, {passive:true});
    drawer.addEventListener('click', e=>{ if(e.target.closest('[data-close],a')) close(); });
    document.addEventListener('keydown', e=>{ if(e.key==='Escape') close(); });
    const src=$('.nav'), dst=drawer.querySelector('.drawer-nav'); if(src && dst && !dst.childElementCount) dst.innerHTML=src.innerHTML;
  })();
  (function initRipple(){
    document.addEventListener('click', function(e){
      const el=e.target.closest('.btn'); if(!el) return;
      const r=el.getBoundingClientRect(); const s=Math.max(r.width,r.height);
      const dot=document.createElement('span'); dot.className='ripple'; dot.style.width=dot.style.height=s+'px';
      dot.style.left=(e.clientX-r.left - s/2)+'px'; dot.style.top=(e.clientY-r.top - s/2)+'px'; el.appendChild(dot);
      setTimeout(()=>dot.remove(), 600);
    }, {passive:true});
  })();
  (function initTopbar(){
    $$('img:not([loading])').forEach(img=> img.loading='lazy');
    const top=$('.topbar, .header');
    if(top){ const on=()=> top.classList.toggle('scrolled', window.scrollY>6); on(); window.addEventListener('scroll', on, {passive:true}); }
  })();
  (function initCopy(){
    document.addEventListener('click', async e=>{
      const x=e.target.closest('[data-copy]'); if(!x) return;
      try{ await navigator.clipboard.writeText(x.dataset.copy||''); const o=x.textContent; x.textContent='✓ تم النسخ'; setTimeout(()=>x.textContent=o, 1100); }catch(e){}
    }, {passive:true});
  })();
  (function initAmountChips(){
    const wrap=document.querySelector('.amount-chips'), amount=document.getElementById('amount'); if(!wrap||!amount) return;
    const chips=$$('.chip', wrap);
    const activate=v=> chips.forEach(c=> c.classList.toggle('active', parseFloat(c.dataset.amount)===parseFloat(v)));
    chips.forEach(c=> c.addEventListener('click', ()=>{ amount.value=c.dataset.amount; activate(amount.value); amount.focus(); }));
    if(amount.value) activate(amount.value);
    amount.addEventListener('input', ()=>{ const v=amount.value; const any=chips.some(c=>parseFloat(c.dataset.amount)===parseFloat(v)); any?activate(v):chips.forEach(c=>c.classList.remove('active')); });
  })();
  (function guardForms(){
    document.addEventListener('submit', e=>{
      const f=e.target.closest('form'); if(!f) return;
      const b=f.querySelector('button[type="submit"], .btn[type="submit"]'); if(!b) return;
      b.disabled=true; const t=b.textContent; b.textContent='جارٍ الإرسال…'; setTimeout(()=>{ try{b.disabled=false; b.textContent=t;}catch(e){} }, 3000);
    });
  })();
  window.showToast=function(msg){
    let el=document.querySelector('.toast'); if(!el){ el=document.createElement('div'); el.className='toast'; document.body.appendChild(el); }
    el.textContent=msg; el.classList.add('show');
    clearTimeout(window.__toastTimer); window.__toastTimer=setTimeout(()=>el.classList.remove('show'),2200);
  };
})(); }


;/* === GameBox v10 Effects (guarded) === */
if(!window.__gb_effects_v10){ window.__gb_effects_v10 = true; (function(){
  const root=document.documentElement, CONTRAST_KEY='gb_contrast';
  const $=(s,sc)=> (sc||document).querySelector(s);
  const $$=(s,sc)=> Array.from((sc||document).querySelectorAll(s));

  // High-contrast toggle (works only if a button with id="contrastToggle" exists)
  function setContrast(mode){
    if(!mode){ root.removeAttribute('data-contrast'); }
    else{ root.setAttribute('data-contrast', 'high'); }
    try{ localStorage.setItem(CONTRAST_KEY, mode?'high':'off'); }catch(e){}
    const b=$('#contrastToggle'); if(b){ b.textContent = (mode?'🌓 تباين عالٍ':'◐ تباين افتراضي'); }
  }
  function initContrast(){
    let saved=null; try{ saved=localStorage.getItem(CONTRAST_KEY);}catch(e){}
    setContrast(saved==='high');
    const btn=$('#contrastToggle'); if(btn){ btn.addEventListener('click', ()=> setContrast(root.getAttribute('data-contrast')!=='high'), {passive:true}); }
  }

  // Reveal on scroll (elements with [data-animate])
  function initReveal(){
    const els=$$('[data-animate]'); if(!els.length) return;
    const io=new IntersectionObserver((entries)=>{
      entries.forEach(ent=>{ if(ent.isIntersecting){ ent.target.classList.add('is-visible'); io.unobserve(ent.target); } });
    }, {rootMargin:'-10% 0px -5% 0px', threshold:.1});
    els.forEach(el=> io.observe(el));
  }

  // Button press micro-interaction (opt-in via [data-press])
  function initPress(){
    document.addEventListener('mousedown', (e)=>{
      const t=e.target.closest('[data-press]'); if(!t) return;
      t.style.transform='scale(.98)';
    });
    document.addEventListener('mouseup', ()=>{
      $$('[data-press]').forEach(el=> el.style.transform='');
    });
    document.addEventListener('mouseleave', ()=>{
      $$('[data-press]').forEach(el=> el.style.transform='');
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    initContrast();
    initReveal();
    initPress();
  });
})(); }

/* === append: Effects v10.2 === */

;/* ===== GameBox Effects v10.2 (guarded) ===== */
if(!window.__gb_fx_v10_2){ window.__gb_fx_v10_2 = true; (function(){
  const $=(s,sc)=> (sc||document).querySelector(s);
  const $$=(s,sc)=> Array.from((sc||document).querySelectorAll(s));

  /* Auto-tag common blocks for reveal if they don't already have data-animate */
  function autoAnimate(){
    const candidates = $$('.card, .service-card, .product, .section, .grid > *');
    candidates.forEach(el=>{
      if(!el.hasAttribute('data-animate')){
        el.setAttribute('data-animate','');
        el.classList.add('fx-pop');
      }
    });
  }

  /* Intersection reveal */
  function initReveal(){
    const els=$$('[data-animate]'); if(!els.length) return;
    const io=new IntersectionObserver((entries)=>{
      entries.forEach(ent=>{ if(ent.isIntersecting){ ent.target.classList.add('is-visible'); io.unobserve(ent.target); } });
    }, {rootMargin:'-10% 0px -5% 0px', threshold:.1});
    els.forEach(el=> io.observe(el));
  }

  /* Ripple (universal for .btn and .chip) */
  function initRippleUniversal(){
    document.addEventListener('click', function(e){
      const el = e.target.closest('.btn, .chip'); if(!el) return;
      const r = el.getBoundingClientRect(); const size=Math.max(r.width,r.height);
      const dot=document.createElement('span'); dot.className='ripple';
      dot.style.width=dot.style.height=size+'px';
      dot.style.left=(e.clientX-r.left - size/2)+'px';
      dot.style.top =(e.clientY-r.top  - size/2)+'px';
      el.appendChild(dot);
      setTimeout(()=>dot.remove(), 600);
    }, {passive:true});
  }

  /* Press feedback for any .btn (no attribute needed) */
  function initPressUniversal(){
    document.addEventListener('pointerdown', e=>{
      const t=e.target.closest('.btn'); if(!t) return; t.style.transform='scale(.98)';
    });
    document.addEventListener('pointerup', ()=>{
      $$('.btn').forEach(b=> b.style.transform='');
    });
    document.addEventListener('pointercancel', ()=>{
      $$('.btn').forEach(b=> b.style.transform='');
    });
    document.addEventListener('mouseleave', ()=>{
      $$('.btn').forEach(b=> b.style.transform='');
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    autoAnimate();
    initReveal();
    initRippleUniversal();
    initPressUniversal();
  });
})(); }

/* === append: v11 FINAL === */

;/* ================= GameBox v11 — FINAL JS ================= */
if(!window.__gb_final_v11){ window.__gb_final_v11 = true; (function(){
  const root = document.documentElement;
  const THEME_KEY = 'gb_theme';
  const $  = (s,sc)=> (sc||document).querySelector(s);
  const $$ = (s,sc)=> Array.from((sc||document).querySelectorAll(s));

  function setTheme(mode){
    root.setAttribute('data-theme', mode);
    try{ localStorage.setItem(THEME_KEY, mode); }catch(e){}
    root.classList.add('theme-animating');
    clearTimeout(window.__gbThemeTimer);
    window.__gbThemeTimer = setTimeout(()=> root.classList.remove('theme-animating'), 340);
    $$('#themeToggle,[data-theme-toggle]').forEach(btn=>{
      if(btn && !btn.dataset.lockLabel){
        btn.textContent = (mode==='dark' ? '☀️' : '🌙');
        btn.title = (mode==='dark' ? 'الوضع الفاتح' : 'الوضع الداكن');
      }
    });
  }
  function initTheme(){
    const pre = root.getAttribute('data-theme');
    if(pre==='dark' || pre==='light'){ setTheme(pre); return; }
    let saved=null; try{ saved = localStorage.getItem(THEME_KEY); }catch(e){}
    if(saved){ setTheme(saved); }
    else{
      const dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      setTheme(dark ? 'dark' : 'light');
    }
  }
  function bindThemeToggle(){
    function onToggle(e){
      const t = e.target.closest('#themeToggle,[data-theme-toggle]'); if(!t) return;
      e.preventDefault(); e.stopPropagation();
      const next = (root.getAttribute('data-theme')==='dark' ? 'light' : 'dark');
      setTheme(next);
    }
    document.addEventListener('click', onToggle);
    document.addEventListener('pointerup', onToggle);
    document.addEventListener('keydown', (e)=>{
      const t = e.target.closest('#themeToggle,[data-theme-toggle]'); if(!t) return;
      if(e.key==='Enter' || e.key===' '){ e.preventDefault(); const next=(root.getAttribute('data-theme')==='dark'?'light':'dark'); setTheme(next); }
    });
  }

  function ensureBackdrop(){
    let b = $('#menuBackdrop');
    if(!b){ b = document.createElement('div'); b.id='menuBackdrop'; b.className='drawer-backdrop'; document.body.appendChild(b); }
    return b;
  }
  function findDrawer(fromControl){
    if(fromControl && fromControl.getAttribute('aria-controls')){
      const id = fromControl.getAttribute('aria-controls');
      const el = document.getElementById(id);
      if(el) return el;
    }
    return $('#sideMenu') || $('[data-drawer]') || $('.drawer') || $('.side-menu');
  }
  function openDrawer(drawer, backdrop){
    if(!drawer) return;
    drawer.classList.add('open');
    if(backdrop) backdrop.classList.add('show');
    document.body.classList.add('menu-open');
    document.body.style.overflow='hidden';
    drawer.setAttribute('aria-hidden','false');
  }
  function closeDrawer(drawer, backdrop){
    if(!drawer) return;
    drawer.classList.remove('open');
    if(backdrop) backdrop.classList.remove('show');
    document.body.classList.remove('menu-open');
    document.body.style.overflow='';
    drawer.setAttribute('aria-hidden','true');
  }
  function initMenu(){
    const backdrop = ensureBackdrop();
    function delegatedHandler(e){
      const openBtn = e.target.closest('#menuToggle,[data-menu-toggle],.menu-toggle');
      if(openBtn){
        e.preventDefault(); e.stopPropagation();
        const drawer = findDrawer(openBtn);
        if(drawer){ openDrawer(drawer, backdrop); }
        return;
      }
      if(e.target === backdrop){ closeDrawer(findDrawer(), backdrop); return; }
      const closeBtn = e.target.closest('[data-close], .drawer a, .side-menu a');
      if(closeBtn){ closeDrawer(findDrawer(closeBtn), backdrop); return; }
    }
    document.addEventListener('click', delegatedHandler);
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape'){ closeDrawer(findDrawer(), backdrop); } });
  }

  function initTopbarShadow(){
    const top = $('.topbar, .header');
    if(!top) return;
    const on=()=> top.classList.toggle('scrolled', window.scrollY>6);
    on(); window.addEventListener('scroll', on, {passive:true});
  }

  function initRipple(){
    if(window.__gb_fx_ripple_v11) return; window.__gb_fx_ripple_v11 = true;
    document.addEventListener('click', function(e){
      const el = e.target.closest('.btn, .chip'); if(!el) return;
      const r = el.getBoundingClientRect(); const size=Math.max(r.width,r.height);
      const dot=document.createElement('span'); dot.className='ripple';
      Object.assign(dot.style, {
        position:'absolute', borderRadius:'50%', transform:'scale(0)',
        animation:'ripple .6s linear', background:'rgba(255,255,255,.35)',
        pointerEvents:'none', opacity:'.9', width:size+'px', height:size+'px',
        left:(e.clientX-r.left - size/2)+'px', top:(e.clientY-r.top  - size/2)+'px'
      });
      if(getComputedStyle(el).position === 'static'){ el.style.position='relative'; }
      el.appendChild(dot);
      setTimeout(()=>dot.remove(), 650);
    }, {passive:true});
  }

  document.addEventListener('DOMContentLoaded', function(){
    initTheme();
    bindThemeToggle();
    initMenu();
    initTopbarShadow();
    initRipple();
  });
})(); }

/* === append: v11.1 FINAL-FINAL === */

;/* ================= GameBox v11.1 — FINAL-FINAL JS ================= */
if(!window.__gb_final_v11_1){ window.__gb_final_v11_1 = true; (function(){
  const $  = (s,sc)=> (sc||document).querySelector(s);
  const $$ = (s,sc)=> Array.from((sc||document).querySelectorAll(s));

  function ensureBackdrop(){
    let b = $('#menuBackdrop');
    if(!b){ b = document.createElement('div'); b.id='menuBackdrop'; b.className='drawer-backdrop'; document.body.appendChild(b); }
    return b;
  }
  function findDrawer(fromControl){
    if(fromControl && fromControl.getAttribute && fromControl.getAttribute('aria-controls')){
      const id = fromControl.getAttribute('aria-controls');
      const el = document.getElementById(id);
      if(el) return el;
    }
    return $('#sideMenu') || $('[data-drawer]') || $('.drawer') || $('.side-menu');
  }
  function isOpen(d){ return d && d.classList.contains('open'); }
  function openDrawer(d, backdrop, btn){
    if(!d) return;
    d.classList.add('open'); d.setAttribute('aria-hidden','false');
    document.body.classList.add('menu-open'); document.body.style.overflow='hidden';
    if(backdrop) backdrop.classList.add('show');
    if(btn) btn.setAttribute('aria-expanded','true');
  }
  function closeDrawer(d, backdrop, btn){
    if(!d) return;
    d.classList.remove('open'); d.setAttribute('aria-hidden','true');
    document.body.classList.remove('menu-open'); document.body.style.overflow='';
    if(backdrop) backdrop.classList.remove('show');
    if(btn) btn.setAttribute('aria-expanded','false');
  }
  function initMenu(){
    const backdrop = ensureBackdrop();
    const toggles = [ ...$$('#menuToggle'), ...$$('[data-menu-toggle]'), ...$$('.menu-toggle') ];
    const drawer = findDrawer(toggles[0]);
    toggles.forEach(btn=>{
      btn.setAttribute('aria-controls', drawer ? (drawer.id || 'sideMenu') : 'sideMenu');
      btn.setAttribute('aria-expanded','false');
      btn.addEventListener('click', (e)=>{
        e.preventDefault(); e.stopPropagation();
        const d = findDrawer(btn); if(!d) return;
        isOpen(d) ? closeDrawer(d, backdrop, btn) : openDrawer(d, backdrop, btn);
      });
    });
    backdrop.addEventListener('click', ()=> closeDrawer(findDrawer(), backdrop, toggles[0]));
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape'){ closeDrawer(findDrawer(), backdrop, toggles[0]); } });
    document.addEventListener('mousedown', (e)=>{
      const d = findDrawer(); if(!d || !isOpen(d)) return;
      if(!e.target.closest('.drawer, .side-menu') && !e.target.closest('#menuToggle,[data-menu-toggle],.menu-toggle')){
        closeDrawer(d, backdrop, toggles[0]);
      }
    }, true);
    const d2 = findDrawer();
    if(d2){
      d2.addEventListener('click', (e)=>{
        if(e.target.closest('[data-close], a, button[data-close]')){
          closeDrawer(d2, backdrop, toggles[0]);
        }
      });
    }
    let lastW = window.innerWidth;
    window.addEventListener('resize', ()=>{
      const w = window.innerWidth;
      if(w >= 1024 && w !== lastW){ closeDrawer(findDrawer(), backdrop, toggles[0]); }
      lastW = w;
    });
  }

  function initBackToTop(){
    let btn = document.getElementById('backToTop');
    if(!btn){
      btn = document.createElement('button');
      btn.id = 'backToTop'; btn.type = 'button';
      btn.setAttribute('aria-label','العودة للأعلى'); btn.innerHTML = '↑';
      document.body.appendChild(btn);
    }
    const onScroll = ()=>{ if(window.scrollY > 260){ btn.classList.add('show'); } else { btn.classList.remove('show'); } };
    onScroll(); window.addEventListener('scroll', onScroll, {passive:true});
    btn.addEventListener('click', ()=> window.scrollTo({top:0, behavior:'smooth'}));
  }

  document.addEventListener('DOMContentLoaded', function(){
    initMenu();
    initBackToTop();
  });
})(); }

/* === append: v11.2 === */

;/* ================= GameBox v11.2 — Robust Drawer (RTL aware) ================= */
if(!window.__gb_final_v11_2){ window.__gb_final_v11_2 = true; (function(){
  const $  = (s,sc)=> (sc||document).querySelector(s);
  const $$ = (s,sc)=> Array.from((sc||document).querySelectorAll(s));

  function ensureBackdrop(){
    let b = $('#menuBackdrop');
    if(!b){ b = document.createElement('div'); b.id='menuBackdrop'; b.className='drawer-backdrop'; document.body.appendChild(b); }
    return b;
  }
  function findDrawer(fromControl){
    if(fromControl && fromControl.getAttribute && fromControl.getAttribute('aria-controls')){
      const id = fromControl.getAttribute('aria-controls');
      const el = document.getElementById(id);
      if(el) return el;
    }
    return $('#sideMenu') || $('[data-drawer]') || $('.drawer') || $('.side-menu');
  }
  function isOpen(d){ return d && d.classList.contains('open'); }
  function openDrawer(d, backdrop, btn){
    if(!d) return;
    d.classList.add('open'); d.setAttribute('aria-hidden','false');
    document.body.classList.add('menu-open'); document.body.style.overflow='hidden';
    if(backdrop) backdrop.classList.add('show');
    if(btn) btn.setAttribute('aria-expanded','true');
  }
  function closeDrawer(d, backdrop, btn){
    if(!d) return;
    d.classList.remove('open'); d.setAttribute('aria-hidden','true');
    document.body.classList.remove('menu-open'); document.body.style.overflow='';
    if(backdrop) backdrop.classList.remove('show');
    if(btn) btn.setAttribute('aria-expanded','false');
  }
  function initMenu(){
    const backdrop = ensureBackdrop();
    const toggles = [ ...$$('#menuToggle'), ...$$('[data-menu-toggle]'), ...$$('.menu-toggle') ];
    const drawer = findDrawer(toggles[0]);

    // Auto-apply side by document dir if not explicitly set
    if(drawer && !drawer.classList.contains('drawer--left') && !drawer.classList.contains('drawer--right')){
      const isRTL = document.documentElement.getAttribute('dir') === 'rtl';
      drawer.classList.add(isRTL ? 'drawer--right' : 'drawer--left');
    }

    toggles.forEach(btn=>{
      if(!drawer) return;
      if(!drawer.id) drawer.id = 'sideMenu';
      btn.setAttribute('aria-controls', drawer.id);
      btn.setAttribute('aria-expanded','false');
      btn.addEventListener('click', (e)=>{
        e.preventDefault(); e.stopPropagation();
        const d = findDrawer(btn);
        if(!d) return;
        isOpen(d) ? closeDrawer(d, backdrop, btn) : openDrawer(d, backdrop, btn);
      });
    });

    backdrop.addEventListener('click', ()=> closeDrawer(findDrawer(), backdrop, toggles[0]));
    document.addEventListener('keydown', (e)=>{ if(e.key==='Escape'){ closeDrawer(findDrawer(), backdrop, toggles[0]); } });
    document.addEventListener('mousedown', (e)=>{
      const d = findDrawer(); if(!d || !isOpen(d)) return;
      if(!e.target.closest('.drawer, .side-menu') && !e.target.closest('#menuToggle,[data-menu-toggle],.menu-toggle')){
        closeDrawer(d, backdrop, toggles[0]);
      }
    }, true);

    const d2 = findDrawer();
    if(d2){
      d2.addEventListener('click', (e)=>{
        if(e.target.closest('[data-close], a, button[data-close]')){
          closeDrawer(d2, backdrop, toggles[0]);
        }
      });
    }

    // Ensure closed at load to avoid "mid-screen"
    if(drawer){ closeDrawer(drawer, ensureBackdrop(), toggles[0]); }
  }

  // Keep back-to-top from v11.1
  function initBackToTop(){
    let btn = document.getElementById('backToTop');
    if(!btn){
      btn = document.createElement('button');
      btn.id = 'backToTop'; btn.type = 'button';
      btn.setAttribute('aria-label','العودة للأعلى'); btn.innerHTML = '↑';
      document.body.appendChild(btn);
    }
    const onScroll = ()=>{ if(window.scrollY > 260){ btn.classList.add('show'); } else { btn.classList.remove('show'); } };
    onScroll(); window.addEventListener('scroll', onScroll, {passive:true});
    btn.addEventListener('click', ()=> window.scrollTo({top:0, behavior:'smooth'}));
  }

  document.addEventListener('DOMContentLoaded', function(){
    initMenu();
    initBackToTop();
  });
})(); }

/* === append: v11.3 === */

;/* ================= GameBox v11.3 — Mobile Theme Toggle Guarantee ================= */
if(!window.__gb_theme_fix_v11_3){ window.__gb_theme_fix_v11_3 = true; (function(){
  const root = document.documentElement;
  const THEME_KEY = 'gb_theme';
  const qAll = (sel)=> Array.from(document.querySelectorAll(sel));
  const get = ()=> (root.getAttribute('data-theme')||'').trim();
  const set = (mode)=>{
    root.setAttribute('data-theme', mode);
    try{ localStorage.setItem(THEME_KEY, mode); }catch(e){}
    root.classList.add('theme-animating');
    clearTimeout(window.__gbThemeTimer);
    window.__gbThemeTimer = setTimeout(()=> root.classList.remove('theme-animating'), 340);
    // Update visible controls
    qAll('#themeToggle,[data-theme-toggle]').forEach(el=>{
      if(el.tagName==='INPUT' && (el.type==='checkbox' || el.type==='radio')){
        el.checked = (mode==='dark');
      }else if(!el.dataset.lockLabel){
        el.textContent = (mode==='dark' ? '☀️' : '🌙');
        el.title = (mode==='dark' ? 'الوضع الفاتح' : 'الوضع الداكن');
      }
    });
  };
  const toggle = ()=> set(get()==='dark' ? 'light' : 'dark');

  // Initialize from storage or media
  (function initOnce(){
    const pre = get();
    if(pre==='dark' || pre==='light'){ return; }
    let saved=null; try{ saved = localStorage.getItem(THEME_KEY); }catch(e){}
    if(saved==='dark' || saved==='light'){ set(saved); }
    else{
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      set(prefersDark ? 'dark' : 'light');
    }
  })();

  // Bind robust events (mobile-safe)
  function bindThemeDelegation(){
    // Tap/click delegation covers buttons, links, icons…
    const handler = (e)=>{
      const t = e.target.closest('#themeToggle,[data-theme-toggle]');
      if(!t) return;
      e.preventDefault(); e.stopPropagation();
      toggle();
    };
    // Touch first (iOS), then click as fallback
    document.addEventListener('touchstart', handler, {passive:false});
    document.addEventListener('click', handler);

    // Checkbox/radio support
    document.addEventListener('change', (e)=>{
      const t = e.target.closest('#themeToggle,[data-theme-toggle]');
      if(!t) return;
      // If it's a checkbox, its checked state defines theme
      if(t.tagName==='INPUT' && (t.type==='checkbox' || t.type==='radio')){
        set(t.checked ? 'dark' : 'light');
      }
    });
  }

  // Re-apply after drawer opens (some UIs re-render the header)
  document.addEventListener('DOMContentLoaded', bindThemeDelegation);
  window.addEventListener('pageshow', bindThemeDelegation);
})(); }
