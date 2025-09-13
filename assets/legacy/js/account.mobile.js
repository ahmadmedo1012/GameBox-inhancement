// ======================================================================
// GameBox — Account Mobile Detector + Cards v3 (SAFE)
// - No PHP edits: detects account page by URL or structure
// - Adds .is-account-page to <body>, then converts .table.responsive to cards
// ======================================================================
(function(){
  if(window.__gb_account_mobile_v3) return; window.__gb_account_mobile_v3 = true;

  // Heuristic: URL ends with account.php OR presence of 2+ .table.responsive OR heading contains "حسابي"
  const path = (location.pathname || '').toLowerCase();
  const looksLikeAccount = /\/account(\.php)?$/.test(path);
  const tables = document.querySelectorAll('.table.responsive');
  const heading = document.querySelector('h1, h2, .page-title');
  const isArabicAccount = heading && /حسابي|الحساب|ملفي/.test((heading.textContent||'').trim());
  const isAccount = looksLikeAccount || (tables.length >= 1 && isArabicAccount);

  if(!isAccount) return;
  document.body.classList.add('is-account-page');

  // Build labels from THEAD for each table; inject as data-label on each TD
  tables.forEach(tbl => {
    const headRow = tbl.querySelector('thead tr');
    if(!headRow) return;
    const labels = Array.from(headRow.children).map(th => (th.textContent||'').trim());
    tbl.querySelectorAll('tbody tr').forEach(tr => {
      Array.from(tr.children).forEach((td, i) => {
        if(!td.getAttribute('data-label')) td.setAttribute('data-label', labels[i] || '');
      });
    });
  });
})();