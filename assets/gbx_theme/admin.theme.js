
// GBX Admin Theme enhance
(function(){try{
  var t = localStorage.getItem('gbx-admin-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark':'light');
  document.documentElement.setAttribute('data-theme', t);
}catch(e){}})();
window.GBXADMIN = window.GBXADMIN || {};
GBXADMIN.toggleTheme = function(){
  var cur = document.documentElement.getAttribute('data-theme') || 'light';
  var next = cur==='light'?'dark':'light';
  document.documentElement.setAttribute('data-theme', next);
  try{localStorage.setItem('gbx-admin-theme', next);}catch(e){}
};
document.addEventListener('DOMContentLoaded', function(){
  var host = document.querySelector('header, .topbar, .navbar');
  if(host){
    var btn = document.createElement('button');
    btn.className = 'btn';
    btn.textContent = 'الوضع';
    btn.style.marginInlineStart = '8px';
    btn.addEventListener('click', GBXADMIN.toggleTheme);
    host.appendChild(btn);
  }
  document.querySelectorAll('canvas[data-series]').forEach(function(cv){
    var s; try{s = JSON.parse(cv.getAttribute('data-series'));}catch(e){s=null}
    if(!s || !s.data || !s.data.length) return;
    draw(cv, s);
  });
  function draw(canvas, series){
    var ctx = canvas.getContext('2d'), dpr = window.devicePixelRatio||1;
    var w = canvas.clientWidth||canvas.width||320, h=canvas.clientHeight||canvas.height||220;
    canvas.width = w*dpr; canvas.height = h*dpr; ctx.scale(dpr,dpr);
    var left=28, top=28, right=28, bottom=28, plotW=w-left-right, plotH=h-top-bottom;
    var data=series.data, min=Math.min.apply(null,data), max=Math.max.apply(null,data); if(min===max){min=0}
    var color = getComputedStyle(document.documentElement).getPropertyValue('--gbx-primary') || '#1A3C8C';
    ctx.strokeStyle='rgba(0,0,0,.15)'; ctx.beginPath(); ctx.moveTo(left, top+plotH); ctx.lineTo(left+plotW, top+plotH); ctx.stroke();
    if(series.type==='bar'){
      var barW = plotW/data.length*0.7;
      for(var i=0;i<data.length;i++){
        var x = left + (i+0.15)*(plotW/data.length);
        var y = top + plotH - ((data[i]-min)/(max-min))*plotH;
        ctx.fillStyle=color.trim(); ctx.fillRect(x,y,barW, top+plotH-y);
      }
    }else{
      ctx.strokeStyle=color.trim(); ctx.lineWidth=2; ctx.beginPath();
      data.forEach(function(v,i){
        var x = left + (i/(data.length-1))*plotW;
        var y = top + plotH - ((v-min)/(max-min))*plotH;
        if(i===0) ctx.moveTo(x,y); else ctx.lineTo(x,y);
      });
      ctx.stroke();
    }
  }
});
