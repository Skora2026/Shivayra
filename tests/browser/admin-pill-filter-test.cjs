/* Admin order-list filter pills must actually filter on the relabeled
   Order Status column. Sets one order to Shipped through the real form,
   checks each pill's visible rows, then restores the original state. */
const puppeteer = require('puppeteer-core');
const path = require('path');
const { execFileSync } = require('child_process');
const BASE = 'http://127.0.0.1:8000';

/* Returns-module states have no admin form path (locked dropdown) — flip at
   the source exactly like the returns module does, restoring to pending. */
const setRawStatus = (id, status) => execFileSync(
    'php',
    ['artisan', 'tinker', `--execute=App\\Models\\Order::whereKey(${id})->update(['order_status' => '${status}']); echo 'ok';`],
    { cwd: path.join(__dirname, '..', '..'), stdio: ['ignore', 'pipe', 'pipe'] }
);

(async () => {
    const b = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
    const p = await b.newPage();
    await p.setViewport({ width: 1366, height: 900 });

    const results = [];
    const check = (n, ok, d = '') => results.push([ok, n, d]);

    const client = await p.createCDPSession();
    await client.send('Network.clearBrowserCookies');

    await p.goto(BASE + '/admin/login', { waitUntil: 'networkidle2', timeout: 30000 });
    if (await p.$('input[name="email"]')) {
        await p.type('input[name="email"]', 'admin@shivayra.com');
        await p.type('input[name="password"]', 'admin123');
        // requestSubmit is deterministic; button .click() can race the nav listener.
        await p.evaluate(() => document.querySelector('form').requestSubmit());
        await p.waitForFunction(() => location.pathname.includes('dashboard'), { timeout: 30000 });
    }

    const visibleRows = () => p.evaluate(() => {
        const rows = [...document.querySelectorAll('table tbody tr')];
        const emptyRow = t => (t || '').includes('No data') || (t || '').includes('No matching records');
        return rows.filter(r => r.offsetParent !== null && !emptyRow(r.textContent))
            .map(r => (r.textContent || '').replace(/\s+/g, ' ').trim());
    });
    const clickPill = async (label) => {
        await p.evaluate(l => {
            const pill = [...document.querySelectorAll('.order-status-filter [data-dt-search]')]
                .find(a => a.textContent.trim() === l);
            pill.click();
        }, label);
        await new Promise(r => setTimeout(r, 600));
    };

    await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 1000));

    const total = (await visibleRows()).length;
    check('orders list loaded', total > 0, `rows=${total}`);

    const pillLabels = await p.evaluate(() =>
        [...document.querySelectorAll('.order-status-filter [data-dt-search]')].map(a => a.textContent.trim()));
    check('8 pills incl Returned + Refunded',
        JSON.stringify(pillLabels) === JSON.stringify(['All', 'Placed', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Returned', 'Refunded']),
        JSON.stringify(pillLabels));

    // Put the FIRST order into Shipped through the real form.
    const firstUrl = await p.evaluate(() => {
        const rows = [...document.querySelectorAll('table tbody tr')];
        const row = rows.find(r => r.offsetParent !== null && !/(No data|No matching records)/.test(r.textContent || ''));
        return row?.querySelector('a[href*="/admin/orders/"]')?.getAttribute('href') || null;
    });
    if (firstUrl) {
        await p.goto(firstUrl.startsWith('http') ? firstUrl : BASE + firstUrl, { waitUntil: 'networkidle2', timeout: 30000 });
        await p.select('select[name="order_status"]', 'shipped');
        // NOTE: header renders #logout-form first — must target the status form itself.
        await p.evaluate(() => document.querySelector('select[name="order_status"]').form.requestSubmit());
        await p.waitForFunction(() => location.pathname.includes('/admin/orders'), { timeout: 30000 });
    }
    check('first order set to Shipped', !!firstUrl);

    await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 1000));

    await clickPill('Shipped');
    let rows = await visibleRows();
    check('Shipped pill -> exactly 1 row, badge Shipped', rows.length === 1 && rows[0].includes('Shipped'), JSON.stringify(rows));
    await p.screenshot({ path: process.env.TEMP + '/pill-shipped.png' });

    await clickPill('Placed');
    rows = await visibleRows();
    check(`Placed pill -> ${total - 1} rows, none Shipped`, rows.length === total - 1 && rows.every(r => r.includes('Placed') && !r.includes('Shipped')), JSON.stringify(rows));

    await clickPill('Delivered');
    rows = await visibleRows();
    check('Delivered pill -> 0 rows (none delivered)', rows.length === 0, JSON.stringify(rows));

    await clickPill('All');
    rows = await visibleRows();
    check('All pill restores every row', rows.length === total, `${rows.length}/${total}`);

    // Restore: put the order back to Placed through the form.
    if (firstUrl) {
        await p.goto(firstUrl.startsWith('http') ? firstUrl : BASE + firstUrl, { waitUntil: 'networkidle2', timeout: 30000 });
        await p.select('select[name="order_status"]', 'pending');
        await p.evaluate(() => document.querySelector('select[name="order_status"]').form.requestSubmit());
        await p.waitForFunction(() => location.pathname.includes('/admin/orders'), { timeout: 30000 });
        const restored = await p.goto(firstUrl.startsWith('http') ? firstUrl : BASE + firstUrl, { waitUntil: 'networkidle2', timeout: 30000 })
            .then(() => p.evaluate(() => document.querySelector('select[name="order_status"]')?.value));
        check('state restored to pending', restored === 'pending', `got=${restored}`);
    }

    // Returned / Refunded categories: positive (own badge under own pill),
    // negative (Delivered must not absorb them), cleanup back to Placed.
    const orderId = firstUrl ? (firstUrl.match(/\/admin\/orders\/(\d+)/) || [])[1] : null;
    if (orderId) {
        setRawStatus(orderId, 'returned');
        await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 1000));
        await clickPill('Returned');
        rows = await visibleRows();
        check('Returned pill -> 1 row, badge Returned', rows.length === 1 && rows[0].includes('Returned'), JSON.stringify(rows));
        await clickPill('Refunded');
        rows = await visibleRows();
        check('Refunded pill -> 0 rows while none refunded', rows.length === 0, JSON.stringify(rows));

        setRawStatus(orderId, 'refunded');
        await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 1000));
        await clickPill('Refunded');
        rows = await visibleRows();
        check('Refunded pill -> 1 row, badge Refunded', rows.length === 1 && rows[0].includes('Refunded'), JSON.stringify(rows));
        await clickPill('Delivered');
        rows = await visibleRows();
        check('Delivered excludes refunded (0 rows)', rows.length === 0, JSON.stringify(rows));

        setRawStatus(orderId, 'pending'); // final cleanup — back to Placed
        await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
        await new Promise(r => setTimeout(r, 1000));
        await clickPill('Placed');
        rows = await visibleRows();
        check('cleanup: order back under Placed', rows.length === total, `${rows.length}/${total}`);
    }

    let pass = 0;
    for (const [ok, n, d] of results) { console.log(`${ok ? 'PASS' : 'FAIL'}  ${n}${ok || !d ? '' : '  [' + d + ']'}`); if (ok) pass++; }
    console.log(`\n${pass}/${results.length}`);
    await p.close();
    process.exit(pass === results.length ? 0 : 1);
})().catch(e => { console.error('ERR', e.message); process.exit(1); });
