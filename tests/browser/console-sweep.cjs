/* Console-error + failed-request sweep over key public pages. */
const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';
const PAGES = ['/', '/product', '/product-detail/' + (process.argv[2] || ''), '/about', '/contact', '/login', '/admin/login', '/termsandcondition', '/privacypolicy'];
(async () => {
  const browser = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
  const issues = [];
  for (const p of PAGES) {
    if (!p || p.endsWith('/')) continue;
    const page = await browser.newPage();
    const errors = [];
    page.on('console', m => { if (m.type() === 'error') errors.push(m.text().slice(0, 120)); });
    page.on('pageerror', e => errors.push('PAGEERROR: ' + String(e).slice(0, 120)));
    page.on('response', r => { if (r.status() >= 400) errors.push(`HTTP ${r.status()} ${r.url().replace(BASE, '')}`); });
    try { await page.goto(BASE + p, { waitUntil: 'networkidle2', timeout: 25000 }); } catch (e) { errors.push('NAV: ' + String(e).slice(0, 80)); }
    if (errors.length) issues.push({ page: p, errors: [...new Set(errors)] });
    await page.close();
  }
  if (!issues.length) console.log('CLEAN: no console errors / failed requests on any page');
  else issues.forEach(i => { console.log(i.page); i.errors.forEach(e => console.log('   ' + e)); });
  await browser.disconnect();
})();
