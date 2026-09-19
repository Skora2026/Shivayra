/* Mobile responsiveness audit — headless Edge @ 390px viewport.
   Measures horizontal overflow, offscreen content, tiny tap targets, console errors.
   Usage: node tests/browser/mobile-audit.js */
const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');

const EDGE = 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe';
const BASE = 'http://127.0.0.1:8000';
const OUT = path.join(__dirname, 'shots');
fs.mkdirSync(OUT, { recursive: true });

const PAGES = [
  { name: 'home', url: '/' },
  { name: 'listing', url: '/product' },
  { name: 'detail', url: '/product-detail/rings-gold-rings-piece-1' },
  { name: 'contact', url: '/contact' },
  { name: 'wishlist', url: '/wishlist' },
  { name: 'login', url: '/login' },
  { name: 'checkout', url: '/checkout', auth: 'user' },
  { name: 'account', url: '/my-account', auth: 'user' },
  { name: 'admin-dashboard', url: '/admin/dashboard', auth: 'admin' },
  { name: 'admin-products', url: '/admin/products', auth: 'admin' },
];

const AUDIT_JS = () => {
  const vw = document.documentElement.clientWidth;
  const overflowers = [];
  document.querySelectorAll('body *').forEach(el => {
    const r = el.getBoundingClientRect();
    if (r.width > 0 && (r.right > vw + 2 || r.left < -2)) {
      const cs = getComputedStyle(el);
      if (cs.position === 'fixed' && (el.classList.contains('cart-panel') || el.classList.contains('offcanvas-mobile'))) return;
      if (cs.display === 'none' || cs.visibility === 'hidden') return;
      // skip children of horizontally scrollable containers
      let p = el.parentElement, scrollable = false;
      while (p && p !== document.body) {
        const pcs = getComputedStyle(p);
        if (/(auto|scroll|hidden|clip)/.test(pcs.overflowX)) { scrollable = true; break; }
        p = p.parentElement;
      }
      if (scrollable) return;
      overflowers.push({
        tag: el.tagName.toLowerCase(),
        cls: String(el.className && el.className.baseVal !== undefined ? el.className.baseVal : el.className).slice(0, 60),
        left: Math.round(r.left), right: Math.round(r.right), w: Math.round(r.width),
      });
    }
  });
  // dedupe by class+tag, keep widest few
  const seen = new Map();
  overflowers.forEach(o => { const k = o.tag + '.' + o.cls; if (!seen.has(k)) seen.set(k, o); });
  const tinyTaps = [];
  const vh = window.innerHeight;
  document.querySelectorAll('a, button, input, select').forEach(el => {
    const r = el.getBoundingClientRect();
    if (r.width > 0 && r.height > 0 && (r.width < 30 || r.height < 30)) {
      const cs = getComputedStyle(el);
      if (cs.display === 'none' || cs.visibility === 'hidden') return;
      // skip elements entirely outside the viewport (parked offcanvas panels etc.)
      if (r.left >= vw || r.right <= 0 || r.top >= vh || r.bottom <= 0) return;
      // skip clipped children of overflow:hidden/auto containers
      let p2 = el.parentElement, clipped = false;
      while (p2 && p2 !== document.body) {
        const pcs = getComputedStyle(p2);
        if (/(hidden|auto|scroll|clip)/.test(pcs.overflowX) || /(hidden|auto|scroll|clip)/.test(pcs.overflowY)) { clipped = true; break; }
        p2 = p2.parentElement;
      }
      if (clipped) return;
      tinyTaps.push({ tag: el.tagName.toLowerCase(), cls: String(el.className).slice(0, 50), w: Math.round(r.width), h: Math.round(r.height) });
    }
  });
  return {
    scrollWidth: document.documentElement.scrollWidth,
    clientWidth: vw,
    bodyScrollWidth: document.body.scrollWidth,
    overflowX: document.documentElement.scrollWidth > vw + 1,
    overflowers: [...seen.values()].slice(0, 12),
    tinyTaps: tinyTaps.slice(0, 12),
  };
};

(async () => {
  // Connect to an already-running Edge: start it with
  //   msedge.exe --headless=new --remote-debugging-port=9333 --user-data-dir=%TEMP%\edge-manual about:blank
  const PORT = process.env.EDGE_DEBUG_PORT || 9333;
  const browser = await puppeteer.connect({ browserURL: 'http://127.0.0.1:' + PORT, defaultViewport: null });
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true, deviceScaleFactor: 2 });
  const consoleErrors = [];
  page.on('console', m => { if (m.type() === 'error') consoleErrors.push(m.text().slice(0, 120)); });
  page.on('pageerror', e => consoleErrors.push('PAGEERROR: ' + String(e).slice(0, 120)));

  const report = [];

  // seed a cart so /checkout renders instead of alerting + redirecting
  await page.goto(BASE + '/', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.evaluate(() => {
    localStorage.setItem('cart', JSON.stringify([{ id: 1, name: 'Audit Product', price: 1999, qty: 1, img: '' }]));
  });

  // login as customer
  await page.goto(BASE + '/login', { waitUntil: 'networkidle2', timeout: 30000 });
  await page.type('input[name="email"]', 'user@shivayra.com');
  await page.type('input[name="password"]', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), page.click('button[type="submit"]')]);
  const userOk = !page.url().includes('/login');
  console.log('customer login:', userOk ? 'OK' : 'FAILED -> ' + page.url());

  // login as admin
  await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle2', timeout: 30000 });
  await page.type('input[name="email"]', 'admin@shivayra.com');
  await page.type('input[name="password"]', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), page.click('button[type="submit"]')]);
  const adminOk = !page.url().includes('/login');
  console.log('admin login:', adminOk ? 'OK' : 'FAILED -> ' + page.url());

  for (const p of PAGES) {
    if (p.auth === 'user' && !userOk) { report.push({ page: p.name, skipped: 'no user session' }); continue; }
    if (p.auth === 'admin' && !adminOk) { report.push({ page: p.name, skipped: 'no admin session' }); continue; }
    consoleErrors.length = 0;
    try {
      await page.goto(BASE + p.url, { waitUntil: 'networkidle2', timeout: 30000 });
    } catch (e) {
      report.push({ page: p.name, error: 'nav failed: ' + String(e).slice(0, 80) });
      continue;
    }
    await new Promise(r => setTimeout(r, 700));
    const audit = await page.evaluate(AUDIT_JS);
    await page.screenshot({ path: path.join(OUT, p.name + '.png'), fullPage: false });
    report.push({
      page: p.name,
      finalUrl: page.url().replace(BASE, ''),
      overflowX: audit.overflowX,
      scrollWidth: audit.scrollWidth,
      overflowers: audit.overflowers,
      tinyTaps: audit.tinyTaps,
      consoleErrors: consoleErrors.slice(0, 4),
    });
  }

  await browser.disconnect();
  fs.writeFileSync(path.join(OUT, 'report.json'), JSON.stringify(report, null, 2));
  for (const r of report) {
    if (r.skipped || r.error) { console.log(`\n== ${r.page}: ${r.skipped || r.error}`); continue; }
    console.log(`\n== ${r.page} (${r.finalUrl}) overflowX=${r.overflowX ? 'YES ' + r.scrollWidth + 'px' : 'no'}`);
    if (r.overflowers.length) console.log('   offscreen:', JSON.stringify(r.overflowers.slice(0, 6)));
    if (r.tinyTaps.length) console.log('   tinyTaps:', JSON.stringify(r.tinyTaps.slice(0, 6)));
    if (r.consoleErrors.length) console.log('   console:', r.consoleErrors.join(' | '));
  }
})();
