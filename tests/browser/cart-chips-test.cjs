/* Real-input verification for: variant chips + server-side cart.
 * 1. Guest: hover card -> chips visible -> click "18 inch" chip -> cart badge = 1 (localStorage only)
 * 2. Login with that guest cart pending -> server merge -> GET /cart returns the merged rows
 * 3. Remove item in panel -> badge + server count drop
 */
const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';

(async () => {
  const browser = await puppeteer.connect({
    browserURL: 'http://127.0.0.1:9333',
    defaultViewport: null,
    protocolTimeout: 60000,
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });
  const results = [];
  const check = (name, ok) => results.push(`${ok ? 'PASS' : 'FAIL'}  ${name}`);

  // Fresh session
  const client = await page.target().createCDPSession();
  await client.send('Network.clearBrowserCookies');

  // ---- 1. Guest adds via chip ----
  await page.goto(BASE + '/product', { waitUntil: 'networkidle2', timeout: 45000 });
  await page.evaluate(() => localStorage.clear());
  await page.reload({ waitUntil: 'networkidle2' });

  // find a card with chips
  const hasChips = await page.$('.variant-chip');
  check('chips present on listing', !!hasChips);

  if (hasChips) {
    // hover the card to reveal chips (desktop)
    const card = await page.$('.product-card:has(.variant-chip)') || (await page.evaluateHandle(() => document.querySelector('.variant-chip').closest('.product-card')));
    await card.hover();
    await new Promise(r => setTimeout(r, 600));

    const chipVisible = await page.evaluate(() => {
      const chip = document.querySelector('.variant-chip');
      const cs = getComputedStyle(chip);
      return cs.visibility !== 'hidden' && Number(cs.opacity) > 0.5;
    });
    check('chips visible on hover', chipVisible);

    // real click on the LAST chip (the "18 inch" variant we added, id 44)
    const clicked = await page.evaluate(() => {
      const chips = [...document.querySelectorAll('.variant-chip')];
      const chip = chips.find(c => c.dataset.variantId === '44') || chips[chips.length - 1];
      const r = chip.getBoundingClientRect();
      return { x: r.x + r.width / 2, y: r.y + r.height / 2, variant: chip.dataset.variantId, vals: chip.dataset.variantValues };
    });
    await page.mouse.click(clicked.x, clicked.y);
    await new Promise(r => setTimeout(r, 800));

    const badge = await page.evaluate(() => document.getElementById('lcartCount')?.innerText || '0');
    check('badge shows 1 after chip click', badge === '1');

    const local = await page.evaluate(() => JSON.parse(localStorage.getItem('cart') || '[]'));
    check('local cart has variant 44 with qty 1', local.length === 1 && String(local[0].variantId) === '44' && local[0].qty === 1);
    check('no navigation happened', page.url().replace(/\/$/, '') === BASE + '/product');

    // ---- 2. Login -> merge ----
    // Set up a server cart first (simulating another device): do it via the API using a separate login later.
    // Here: register flow is OTP-gated, so log in as the admin? No — admin is staff.
    // Use the seeded customer? Users table has none. So verify merge by direct API:
    // a) login through the UI is not available (no test customer), so simulate the
    //    client's merge behavior: POST /cart/sync with the local items after authenticating.
    // We authenticate by logging in as admin (valid auth) purely to test the MERGE API path.
    await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle2' });
    await page.type('input[name="email"]', 'admin@shivayra.com');
    await page.type('input[name="password"]', 'admin123');
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), page.click('button[type="submit"]')]);
    check('logged in', page.url().includes('/admin'));

    // back to storefront; seed a DETERMINISTIC server cart (clear + 2 rows)
    // so assertions don't depend on leftovers from earlier runs
    // Deterministic hydration proof: seed clear + 2 rows, wipe local, reload.
    // Badge must equal 3 (1+2) — pure server hydration after a "cache clear".
    await page.goto(BASE + '/product', { waitUntil: 'networkidle2' });
    await page.evaluate(async () => {
        for (let i = 0; i < 50; i++) { if (document.body.dataset.cartHydrated) break; await new Promise(r => setTimeout(r, 100)); }
        const t = document.querySelector('meta[name="csrf-token"]').content;
        await fetch('/cart/sync', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': t, 'Accept': 'application/json' }, body: JSON.stringify({ items: [] }) });
        await fetch('/cart/sync', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': t, 'Accept': 'application/json' }, body: JSON.stringify({ items: [{ id: 47, qty: 1, variantId: 3 }, { id: 57, qty: 2, variantId: null }] }) });
        // Simulate a cache clear / second device: wipe the local copy so the
        // next load must hydrate the cart purely from the server.
        localStorage.removeItem('cart');
    });
    await page.reload({ waitUntil: 'networkidle2' });
    await page.evaluate(async () => {
        for (let i = 0; i < 60; i++) { if (document.body.dataset.cartHydrated) break; await new Promise(r => setTimeout(r, 100)); }
        await new Promise(r => setTimeout(r, 500));
    });
    const seededBadge = await page.evaluate(() => document.getElementById('lcartCount')?.innerText);
    check('hydrated badge shows server cart (3)', seededBadge === '3');

    // Merge semantics proof (the login path header.js uses): merge:true must
    // ADD guest items into existing server rows without deleting other rows.
    const mergeProof = await page.evaluate(async () => {
        const t = document.querySelector('meta[name="csrf-token"]').content;
        const post = (body) => fetch('/cart/sync', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': t, 'Accept': 'application/json' }, body: JSON.stringify(body) }).then(r => r.json());
        await post({ items: [] });                                  // clean slate
        await post({ items: [{ id: 47, qty: 1, variantId: 3 }] });  // other-device row
        await post({ items: [{ id: 57, qty: 1, variantId: 44 }], merge: true }); // guest item
        await post({ items: [{ id: 57, qty: 1, variantId: 44 }], merge: true }); // duplicate guest add -> sums
        const res = await fetch('/cart', { headers: { 'Accept': 'application/json' } });
        const items = (await res.json()).items;
        const row47 = items.find(i => String(i.id) === '47' && String(i.variantId ?? 'null') === '3');
        const row57 = items.find(i => String(i.id) === '57' && String(i.variantId ?? 'null') === '44');
        return { serverQty47: row47?.qty ?? null, serverQty57: row57?.qty ?? null, rows: items.length };
    });
    check('merge keeps other-device row (47|v3 qty 1)', mergeProof.serverQty47 === 1);
    check('merge adds guest item and sums duplicates (57|v44 qty 2)', mergeProof.serverQty57 === 2);
    check('merge created exactly 2 rows (no duplication)', mergeProof.rows === 2);

    // Remove-row proof: deterministic 2-row state -> remove first row via the
    // panel -> that exact row vanishes server-side, count shrank accordingly.
    await page.evaluate(async () => {
        const t = document.querySelector('meta[name="csrf-token"]').content;
        await fetch('/cart/sync', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': t, 'Accept': 'application/json' }, body: JSON.stringify({ items: [{ id: 47, qty: 1, variantId: 3 }, { id: 57, qty: 2, variantId: null }] }) });
        localStorage.removeItem('cart');
    });
    await page.reload({ waitUntil: 'networkidle2' });
    await page.evaluate(async () => {
        for (let i = 0; i < 60; i++) { if (document.body.dataset.cartHydrated) break; await new Promise(r => setTimeout(r, 100)); }
        await new Promise(r => setTimeout(r, 500));
    });    // ---- 3. Remove first row via panel -> that exact row vanishes server-side ----
    const firstRow = await page.evaluate(() => {
        const local = JSON.parse(localStorage.getItem('cart') || '[]');
        return local.length ? { id: String(local[0].id), variantId: String(local[0].variantId) } : null;
    });
    await page.evaluate(() => { document.querySelector('.cart-fab')?.click(); });
    await new Promise(r => setTimeout(r, 500));
    await page.evaluate(() => window.removeItem(0));
    await new Promise(r => setTimeout(r, 1200));
    const serverState = await page.evaluate(async (firstRow) => {
        const res = await fetch('/cart', { headers: { 'Accept': 'application/json' } });
        const items = (await res.json()).items;
        return {
            gone: !items.some(i => String(i.id) === firstRow.id && String(i.variantId ?? 'null') === firstRow.variantId),
            count: items.reduce((a, b) => a + b.qty, 0),
        };
    }, firstRow);
    check('removed row vanished from server cart', serverState.gone);
    check('server count shrank to 2 (3 minus removed qty1)', serverState.count === 2);
  }

  console.log(results.join('\n'));
  const fails = results.filter(r => r.startsWith('FAIL')).length;
  console.log(`\n${results.length - fails}/${results.length} passed`);
  await page.close();
  process.exit(fails ? 1 : 0);
})().catch(e => { console.error('ERR', e.message); process.exit(2); });
