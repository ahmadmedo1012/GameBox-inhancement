/* == Admin Unified JS (rebuilt) == */
/* Anti-FOUC theme bootstrap */
(function(){
  try{
    var st = document.createElement('style');
    st.setAttribute('data-temp-style','anti-fouc');
    st.textContent = 'html{visibility:hidden}';
    document.head.prepend(st);
  }catch(e){}
  var pref = null;
  try{ pref = localStorage.getItem('admin-theme'); }catch(e){}
  var theme = (pref==='dark'||pref==='light') ? pref : 'light';
  document.documentElement.dataset.theme = theme;
  requestAnimationFrame(function(){
    var t = document.querySelector('style[data-temp-style="anti-fouc"]');
    if(t) t.remove();
    document.documentElement.style.visibility = '';
  });
})();

/* Theme toggle */
window.toggleTheme = function(next){
  var el = document.documentElement;
  var cur = el.dataset.theme || 'light';
  var target = next || (cur === 'light' ? 'dark' : 'light');
  el.dataset.theme = target;
  try{ localStorage.setItem('admin-theme', target); }catch(e){}
  window.dispatchEvent(new CustomEvent('themechange', {detail:{theme:target}}));
};

/* Toasts */
(function(){
  function ensureHost(){
    var host = document.querySelector('.toast-host');
    if(!host){
      host = document.createElement('div');
      host.className = 'toast-host';
      document.body.appendChild(host);
    }
    return host;
  }
  window.notify = function(opts){
    opts = opts || {};
    var type = (opts.type||'info').toLowerCase();
    var msg = String(opts.message||'');
    var timeout = Number(opts.timeout||3000);
    var host = ensureHost();
    var el = document.createElement('div');
    el.className = 'toast ' + type;
    el.setAttribute('role','status');
    el.setAttribute('aria-live','polite');
    el.textContent = msg;
    host.appendChild(el);
    if(timeout>0){
      setTimeout(function(){ el.style.opacity = '0'; setTimeout(function(){el.remove();}, 300); }, timeout);
    }
    return el;
  };
})();

/* Header scrolled shadow */
(function(){
  function onScroll(){
    var y = window.scrollY || document.documentElement.scrollTop || 0;
    document.querySelectorAll('.header, .dashboard-header, .topbar, .appbar').forEach(function(el){
      el.classList.toggle('is-scrolled', y>4);
    });
  }
  window.addEventListener('scroll', onScroll, {passive:true});
  onScroll();
})();

/* Sidebar toggling (generic) */
(function(){
  var root = document.documentElement;
  document.addEventListener('click', function(e){
    var t = e.target.closest('[data-toggle="sidebar"], .js-toggle-sidebar');
    if(t){
      var sb = document.querySelector('.sidebar, .dashboard-sidebar, .side-nav');
      if(sb){ sb.classList.toggle('open'); }
    }
  }, false);
})();

/* Tabs (data attributes or class-based) */
(function(){
  function activate(tab){
    if(!tab) return;
    var cont = tab.closest('.tabs, .nav, .nav-tabs') || tab.parentElement;
    if(!cont) return;
    cont.querySelectorAll('.tab, [role="tab"]').forEach(function(x){ x.classList.remove('active'); });
    tab.classList.add('active');
    var targetSel = tab.getAttribute('data-tab-target') || tab.getAttribute('href');
    if(targetSel && targetSel.startsWith('#')){
      var pane = document.querySelector(targetSel);
      if(pane){
        var group = pane.parentElement;
        if(group){
          Array.from(group.children).forEach(function(ch){ ch.hidden = true; });
        }
        pane.hidden = false;
      }
    }
  }
  document.addEventListener('click', function(e){
    var t = e.target.closest('.tab, [role="tab"]');
    if(t){ e.preventDefault(); activate(t); }
  }, false);
})();

/* Forms: prevent double submit */
(function(){
  document.addEventListener('submit', function(e){
    var form = e.target;
    var btn = form.querySelector('button[type="submit"], .btn[type="submit"]');
    if(btn){
      btn.disabled = true;
      setTimeout(function(){ btn.disabled = false; }, 3000);
    }
  }, true);
})();
