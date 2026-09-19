/* Mobile overflow + visual check for the split login pages. */
const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';

(async () => {
  const browser = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
  for (const path of ['/login', '/admin/login']) {
    const page = await browser.newPage();
    await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
    await page.goto(BASE + path, { waitUntil: 'networkidle2' });
    const overflow = await page.evaluate(() => {
      const doc = document.documentElement;
      return { scrollW: doc.scrollWidth, clientW: doc.clientWidth };
    });
    const ok = overflow.scrollW <= overflow.clientW + 1;
    console.log(`${ok ? 'PASS' : 'FAIL'} | ${path} | scrollW=${overflow.scrollW} clientW=${overflow.clientW}`);
    await page.close();
  }
  await browser.disconnect();
})();
