/* Prove admin/client order-state sync through the real UI:
   admin detail labels + stepper == client's four timeline states,
   status change through the form, list badge/pills, client timeline. */
const puppeteer = require('puppeteer-core');
const path = require('path');
const { execFileSync } = require('child_process');
const BASE = 'http://127.0.0.1:8000';

/* Fixtures are cleaned up after runs — recreate deterministically so the
   suite is self-contained (order resets to pending on every run). */
function ensureFixture() {
    const php = [
        "$u = App\\Models\\User::firstOrCreate(['email' => 'timeline.test.ui@example.com'], ['name' => 'Timeline UI Test', 'password' => Illuminate\\Support\\Facades\\Hash::make('Test@1234')]);",
        "$o = App\\Models\\Order::updateOrCreate(['order_number' => 'SHV-UI-TEST'], ['user_id' => $u->id, 'name' => 'Timeline UI Test', 'email' => $u->email, 'phone' => '9999999999', 'address' => '1 Test Lane', 'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411001', 'shipping_name' => 'Timeline UI Test', 'shipping_email' => $u->email, 'shipping_phone' => '9999999999', 'shipping_address' => '1 Test Lane', 'shipping_city' => 'Pune', 'shipping_state' => 'Maharashtra', 'shipping_pincode' => '411001', 'subtotal' => 500, 'shipping_charge' => 0, 'tax' => 0, 'discount' => 0, 'total' => 500, 'payment_method' => 'cod', 'payment_status' => 'pending', 'order_status' => 'pending']);",
        "if ($o->items()->count() === 0) { $i = new App\\Models\\OrderItem(); $i->order_id = $o->id; $i->product_name = 'UI Sync Item'; $i->price = 500; $i->qty = 1; $i->total = 500; $i->save(); }",
        "echo 'fixture ok';",
    ].join('\n');
    execFileSync('php', ['artisan', 'tinker', '--execute=' + php], {
        cwd: path.join(__dirname, '..', '..'),
        stdio: ['ignore', 'pipe', 'pipe'],
    });
}

(async () => {
    const b = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
    const results = [];
    const check = (n, ok, d = '') => results.push([ok, n, d]);
    ensureFixture();

    // Start from a clean session (shared debug profile may hold stale auth).
    async function clearCookies(page) {
        const client = await page.createCDPSession();
        await client.send('Network.clearBrowserCookies');
    }
    async function login(page, url, email, pass) {
        await page.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
        if (await page.$('input[name="email"]')) {
            await page.type('input[name="email"]', email);
            await page.type('input[name="password"]', pass);
            // Deterministic: button .click() + waitForNavigation races (proven flaky).
            await page.evaluate(() => document.querySelector('form').requestSubmit());
            const marker = url.includes('/admin/') ? 'dashboard' : 'my-account';
            await page.waitForFunction(m => location.pathname.includes(m), { timeout: 30000 }, marker);
        }
    }

    // ---------- Admin ----------
    const p = await b.newPage();
    await p.setViewport({ width: 1366, height: 900 });
    await clearCookies(p);
    await login(p, BASE + '/admin/login', 'admin@shivayra.com', 'admin123');

    // Detail page: dropdown + stepper speak the client's four labels
    await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
    // Narrow to the fixture via DataTable's own (server-side) search — the row
    // may not be on page 1 as the table grows.
    await p.type('#order-table_filter input', 'SHV-UI-TEST');
    await new Promise(r => setTimeout(r, 900));
    // find the test order's id via datatable row link
    const testOrderUrl = await p.evaluate(() => {
        const rows = [...document.querySelectorAll('table tbody tr')];
        for (const tr of rows) {
            const t = tr.textContent || '';
            if (t.includes('SHV-UI-TEST')) {
                const a = tr.querySelector('a[href*="/admin/orders/"]');
                if (a) return a.getAttribute('href');
            }
        }
        return null;
    });
    check('test order visible in admin list', !!testOrderUrl, 'SHV-UI-TEST row not found');
    if (testOrderUrl) {
        const abs = testOrderUrl.startsWith('http') ? testOrderUrl : BASE + testOrderUrl;
        await p.goto(abs, { waitUntil: 'networkidle2', timeout: 30000 });
    }

    const detail = await p.evaluate(() => {
        const opts = [...document.querySelectorAll('select[name="order_status"] option')].map(o => o.textContent.trim());
        const stepper = [...document.querySelectorAll('.status-stepper .step')].map(s => s.textContent.trim().replace(/\s+/g, ' '));
        return { opts, stepper };
    });
    check('dropdown = Placed,Processing,Shipped,Delivered,Cancelled',
        JSON.stringify(detail.opts) === JSON.stringify(['Placed', 'Processing', 'Shipped', 'Delivered', 'Cancelled']),
        JSON.stringify(detail.opts));
    check('stepper has 4 steps', detail.stepper.length === 4, JSON.stringify(detail.stepper));
    check('stepper labels = client timeline labels',
        detail.stepper.join('|') === 'Placed|Processing|Shipped|Delivered', JSON.stringify(detail.stepper));

    // Change status through the real form (listener attached before submit;
    // target the status form — header renders #logout-form first in the DOM).
    await p.select('select[name="order_status"]', 'shipped');
    await Promise.all([
        p.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        p.evaluate(() => document.querySelector('select[name="order_status"]').form.requestSubmit()),
    ]);
    const after = await p.evaluate(() => ({
        sel: document.querySelector('select[name="order_status"]')?.value,
        current: (document.querySelector('.status-stepper .step.current')?.textContent || '').trim().replace(/\s+/g, ' '),
        doneCount: document.querySelectorAll('.status-stepper .step.done').length,
    }));
    check('form save set status=shipped', after.sel === 'shipped', JSON.stringify(after));
    check('stepper current step = Shipped (2 done)', after.current.includes('Shipped') && after.doneCount === 2, JSON.stringify(after));

    // Orders list: pills + badge
    await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 800));
    await p.type('#order-table_filter input', 'SHV-UI-TEST');
    await new Promise(r => setTimeout(r, 900));
    const list = await p.evaluate(() => {
        const pills = [...document.querySelectorAll('.order-status-filter [data-dt-search]')].map(a => a.textContent.trim());
        const rows = [...document.querySelectorAll('table tbody tr')];
        const row = rows.find(tr => (tr.textContent || '').includes('SHV-UI-TEST'));
        return { pills, badge: row ? (row.textContent || '') : '' };
    });
    check('pills = All,Placed,Processing,Shipped,Delivered,Cancelled,Returned,Refunded',
        JSON.stringify(list.pills) === JSON.stringify(['All', 'Placed', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned', 'Refunded']),
        JSON.stringify(list.pills));
    check('list badge shows Shipped', list.badge.includes('Shipped'), list.badge.slice(0, 120));
    await p.screenshot({ path: process.env.TEMP + '/sync-admin.png' });

    // ---------- Client ----------
    const c = await b.newPage();
    await c.setViewport({ width: 1366, height: 900 });
    await clearCookies(c);
    await login(c, BASE + '/login', 'timeline.test.ui@example.com', 'Test@1234');
    await c.goto(BASE + '/order-detail/SHV-UI-TEST', { waitUntil: 'networkidle2', timeout: 30000 });

    const client = await c.evaluate(() => [...document.querySelectorAll('.timeline-item')].map(el => ({
        t: el.querySelector('.timeline-title')?.textContent.trim(),
        done: el.classList.contains('is-done'),
    })));
    check('client timeline labels unchanged', JSON.stringify(client.map(i => i.t)) === JSON.stringify(['Placed', 'Processing', 'Shipped', 'Delivered']), JSON.stringify(client));
    check('client: first three lit after admin shipped', client[0]?.done && client[1]?.done && client[2]?.done && !client[3]?.done, JSON.stringify(client));
    await c.screenshot({ path: process.env.TEMP + '/sync-client.png' });

    // Restore the fixture (pending) so sibling suites — especially the pill
    // suite's exact-count checks — never inherit our shipped residue.
    try { ensureFixture(); } catch (e) { console.error('cleanup warning:', e.message); }

    let pass = 0;
    for (const [ok, n, d] of results) { console.log(`${ok ? 'PASS' : 'FAIL'}  ${n}${ok || !d ? '' : '  [' + d + ']'}`); if (ok) pass++; }
    console.log(`\n${pass}/${results.length}`);
    await p.close(); await c.close();
    process.exit(pass === results.length ? 0 : 1);
})().catch(e => { console.error('ERR', e.message); process.exit(1); });
