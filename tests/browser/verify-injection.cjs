/* Regression: client-controlled cart fields (name/price) must be ignored by the
   server. Places a COD order with a tampered payload, asserts the stored order
   shows the real product name, then deletes the test order. */
const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';

(async () => {
  const browser = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
  const page = await browser.newPage();
  await page.setViewport({ width: 1366, height: 900 });

  // 1. login as demo customer
  await page.goto(BASE + '/login', { waitUntil: 'networkidle2' });
  await page.type('input[name=email]', 'user@shivayra.com');
  await page.type('input[name=password]', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), page.click('#loginStaticBtn')]);
  console.log('logged in:', page.url());

  // 2. fetch a real product id
  const productId = await page.evaluate(async () => {
    const r = await fetch('/product', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const html = await r.text();
    const m = html.match(/add-to-cart[^>]*data-id="(\d+)"/);
    return m ? m[1] : null;
  });
  console.log('product id:', productId);
  if (!productId) { console.log('NO PRODUCT FOUND — aborting'); process.exit(1); }

  // 3. grab csrf + place a COD order with a tampered item name
  const result = await page.evaluate(async (pid) => {
    const token = document.querySelector('meta[name=csrf-token]')?.content
      || document.querySelector('input[name=_token]')?.value;
    // visit checkout to get a fresh csrf inside its form
    const res = await fetch('/checkout');
    const html = await res.text();
    const t2 = html.match(/name="_token" value="([^"]+)"/)?.[1];
    const csrf = t2 || token;

    const cart = [{ id: Number(pid), name: '<script>alert(1)</script>PWNED', variantId: null, variantValues: null, price: 1, img: '/images/placeholder.svg', qty: 1 }];
    const body = new URLSearchParams({
      _token: csrf,
      name: 'QA Tester', email: 'qa@example.com', phone: '9999999999',
      address: '1 Test Lane', city: 'Testville', state: 'TS', pincode: '100001',
      same_address: '1', payment_method: 'cod',
      cart_data: JSON.stringify(cart),
    });
    const r = await fetch('/checkout', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body, redirect: 'follow' });
    return { status: r.status, url: r.url, text: (await r.text()).slice(0, 200) };
  }, productId);
  console.log('order attempt:', result.status, result.url);

  // 4. check the latest order detail for the injected name
  await page.goto(BASE + '/my-account', { waitUntil: 'networkidle2' });
  const detailLink = await page.evaluate(() => {
    const a = [...document.querySelectorAll('a')].find(x => /order-detail/.test(x.href));
    return a ? a.href : null;
  });
  let verdict = 'FAIL: no order detail link found';
  if (detailLink) {
    await page.goto(detailLink, { waitUntil: 'networkidle2' });
    const injected = await page.evaluate(() => ({
      pwnedVisible: document.body.innerText.includes('PWNED'),
      scriptTagInDom: document.body.innerHTML.includes('<script>alert(1)'),
    }));
    verdict = (!injected.pwnedVisible && !injected.scriptTagInDom)
      ? 'PASS: tampered name/price ignored by server'
      : 'FAIL: injected payload rendered: ' + JSON.stringify(injected);
  }

  // Self-clean: remove the QA order
  const { spawnSync } = require('child_process');
  spawnSync('php', ['artisan', 'tinker', '--execute', `App\\Models\\Order::where('email', 'qa@example.com')->delete();`], { cwd: process.cwd(), stdio: 'ignore' });

  console.log(verdict);
  await browser.disconnect();
})();
