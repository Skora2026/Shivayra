/* Full returns lifecycle through the REAL surfaces, end to end:
   admin delivers the order (form) -> customer files a return from their
   dashboard (form, with the one-request-per-item + window gates proven) ->
   the sidebar queue badge counts it -> admin approves -> admin refunds ->
   the Returned / Refunded order pills surface the order.
   Self-contained: the fixture (SHV-RET-TEST) is reset on entry AND cleaned
   on exit so it never disturbs the pill suite's exact-count expectations. */
const puppeteer = require('puppeteer-core');
const path = require('path');
const { execFileSync } = require('child_process');
const BASE = 'http://127.0.0.1:8000';
const ORDER_NO = 'SHV-RET-TEST';

const php = (code) => execFileSync('php', ['artisan', 'tinker', '--execute=' + code], {
    cwd: path.join(__dirname, '..', '..'),
    stdio: ['ignore', 'pipe', 'pipe'],
}).toString().trim();

/* Resets the fixture to a pending COD order with no return request and
   echoes "id:<orderId>,item:<itemId>". The attached product is an active,
   returnable one so every real-world gate passes on its merits. */
function ensureFixture() {
    const out = php([
        "$u = App\\Models\\User::firstOrCreate(['email' => 'returns.test.ui@example.com'], ['name' => 'Returns Test', 'password' => Illuminate\\Support\\Facades\\Hash::make('Test@1234')]);",
        "$o = App\\Models\\Order::updateOrCreate(['order_number' => '" + ORDER_NO + "'], ['user_id' => $u->id, 'name' => 'Returns Test', 'email' => $u->email, 'phone' => '9999999999', 'address' => '1 Test Lane', 'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411001', 'shipping_name' => 'Returns Test', 'shipping_email' => $u->email, 'shipping_phone' => '9999999999', 'shipping_address' => '1 Test Lane', 'shipping_city' => 'Pune', 'shipping_state' => 'Maharashtra', 'shipping_pincode' => '411001', 'subtotal' => 500, 'shipping_charge' => 0, 'tax' => 0, 'discount' => 0, 'total' => 500, 'payment_method' => 'cod', 'payment_status' => 'pending', 'order_status' => 'pending', 'completed_at' => null]);",
        "App\\Models\\ReturnRequest::where('order_id', $o->id)->delete();",
        "$prod = App\\Models\\Product::where('status', 'active')->where('is_returnable', true)->orderBy('id')->first();",
        "$i = App\\Models\\OrderItem::firstOrCreate(['order_id' => $o->id, 'product_name' => 'Returns Test Item'], ['product_id' => $prod?->id, 'price' => 500, 'qty' => 1, 'total' => 500]);",
        "echo 'id:'.$o->id.',item:'.$i->id.',prod:'.($prod?->id ?? 'none').'@'.($prod?->stock ?? '');",
    ].join('\n'));
    const m = out.match(/id:(\d+),item:(\d+),prod:(\d+|none)@(\d*)/);
    if (!m) throw new Error('fixture failed: ' + out.slice(-400));
    return {
        orderId: +m[1],
        itemId: +m[2],
        prodId: m[3] === 'none' ? null : +m[3],
        prodStock: m[4] === '' ? null : +m[4],
    };
}

(async () => {
    const b = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
    const p = await b.newPage();
    await p.setViewport({ width: 1366, height: 900 });

    const results = [];
    const check = (n, ok, d = '') => results.push([ok, n, d]);

    const clearCookies = async () => {
        const client = await p.createCDPSession();
        await client.send('Network.clearBrowserCookies');
    };
    // Deterministic login: target the form that owns the email field (the
    // front layout renders other forms too) and wait on the landing marker.
    const login = async (url, marker, email, pass) => {
        await p.goto(url, { waitUntil: 'networkidle2', timeout: 30000 });
        if (await p.$('input[name="email"]')) {
            await p.type('input[name="email"]', email);
            await p.type('input[name="password"]', pass);
            await p.evaluate(() => document.querySelector('input[name="email"]').form.requestSubmit());
            await p.waitForFunction(m => location.pathname.includes(m), { timeout: 30000 }, marker);
        }
        check(`login landed on ${marker}`, p.url().includes(marker), p.url());
    };
    const submitVia = (sel) => Promise.all([
        p.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
        p.evaluate(s => document.querySelector(s).form.requestSubmit(), sel),
    ]);
    const visibleRows = () => p.evaluate(() => {
        const empty = t => (t || '').includes('No data') || (t || '').includes('No matching records');
        return [...document.querySelectorAll('table tbody tr')]
            .filter(r => r.offsetParent !== null && !empty(r.textContent))
            .map(r => (r.textContent || '').replace(/\s+/g, ' ').trim());
    });
    const clickPill = async (label) => {
        await p.evaluate(l => {
            const pill = [...document.querySelectorAll('.order-status-filter [data-dt-search]')]
                .find(a => a.textContent.trim() === l);
            pill.click();
        }, label);
        await new Promise(r => setTimeout(r, 700));
    };

    const { orderId, itemId, prodId, prodStock } = ensureFixture();
    console.log(`[phase] fixture ok (order ${orderId}, item ${itemId}, product #${prodId} stock@${prodStock})`);

    /* ---- Leg 1: admin delivers the order (real form) ---- */
    console.log('[phase] leg1: admin login');
    await clearCookies();
    await login(BASE + '/admin/login', 'dashboard', 'admin@shivayra.com', 'admin123');
    console.log('[phase] leg1: deliver via admin form');
    await p.goto(`${BASE}/admin/orders/${orderId}`, { waitUntil: 'networkidle2', timeout: 30000 });
    const hasStatusSel = !!(await p.$('select[name="order_status"]'));
    check('order form present on show page', hasStatusSel);
    if (hasStatusSel) {
        await p.select('select[name="order_status"]', 'completed'); // value behind "Delivered"
        await submitVia('select[name="order_status"]');
        const after = await p.evaluate(() => ({
            sel: document.querySelector('select[name="order_status"]')?.value,
            current: (document.querySelector('.status-stepper .step.current')?.textContent || '').trim().replace(/\s+/g, ' '),
            done: document.querySelectorAll('.status-stepper .step.done').length,
        }));
        check('admin marked order Delivered', after.sel === 'completed', JSON.stringify(after));
        check('stepper: current Delivered, >=3 done', after.current.includes('Delivered') && after.done >= 3, JSON.stringify(after));
    }


    /* ---- Leg 2: customer files the return (real form + gates) ---- */
    console.log('[phase] leg2: client login + file return');
    await clearCookies();
    await login(BASE + '/login', 'my-account', 'returns.test.ui@example.com', 'Test@1234');
    await p.goto(BASE + '/my-account', { waitUntil: 'networkidle2', timeout: 30000 });
    const returnHref = await p.evaluate(id =>
        document.querySelector(`a[href*="/my-account/return/${id}"]`)?.href || null, itemId);
    check('dashboard offers Return item link', !!returnHref, returnHref || 'link missing');
    await p.goto(returnHref || `${BASE}/my-account/return/${itemId}`, { waitUntil: 'networkidle2', timeout: 30000 });
    const hasReason = !!(await p.$('textarea[name="reason"]'));
    check('return form renders with reason field', hasReason, p.url());
    if (hasReason) {
        await p.type('textarea[name="reason"]', 'Lifecycle test — the size does not fit, please arrange pickup.');
        await submitVia('textarea[name="reason"]');
        const body = await p.evaluate(() => document.body.innerText);
        check('filing succeeded (success flash)', body.includes('Return request filed'), body.slice(0, 120));
    }
    await p.goto(`${BASE}/my-account/return/${itemId}`, { waitUntil: 'networkidle2', timeout: 30000 });
    check('second filing blocked (redirected away)', !p.url().includes(`/return/${itemId}`), p.url());

    /* ---- Leg 3: sidebar queue badge + admin decides ---- */
    console.log('[phase] leg3: badge + approve + refund');
    await clearCookies();
    await login(BASE + '/admin/login', 'dashboard', 'admin@shivayra.com', 'admin123');
    await p.goto(BASE + '/admin/returns', { waitUntil: 'networkidle2', timeout: 30000 });
    const badge = await p.evaluate(base => {
        const a = [...document.querySelectorAll('a')].find(x => x.href === base + '/admin/returns');
        return a?.querySelector('span.badge')?.textContent?.trim() || null;
    }, BASE);
    check('sidebar queue badge shows "N to do" >=1', /^\d+ to do$/.test(badge || '') && parseInt(badge, 10) >= 1, String(badge));

    const retId = php(`echo App\\Models\\ReturnRequest::where('order_id', ${orderId})->value('id');`);
    check('return request persisted for the item', /^\d+$/.test(retId), retId);
    await p.goto(`${BASE}/admin/returns/${retId}`, { waitUntil: 'networkidle2', timeout: 30000 });
    const decideSel = !!(await p.$('select[name="status"]'));
    check('decision form offers approve/reject', decideSel, p.url());

    if (decideSel) {
        await p.select('select[name="status"]', 'approved');
        await submitVia('select[name="status"]');
        const approved = await p.evaluate(() => ({
            alert: document.querySelector('.alert-success')?.textContent?.trim() || '',
            opts: [...document.querySelectorAll('select[name="status"] option')].map(o => o.value),
        }));
        check('approve saved', approved.alert.includes('approved'), approved.alert);
        check('refunded option now offered', approved.opts.includes('refunded'), JSON.stringify(approved.opts));

        if (approved.opts.includes('refunded')) {
            // Mid-flight proof: the order sits in `returned` right now —
            // the Returned pill must surface it BEFORE refund closes the loop.
            console.log('[phase] leg3b: Returned pill while order is returned');
            await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
            await new Promise(r => setTimeout(r, 1000));
            await clickPill('Returned');
            const midRows = await visibleRows();
            check('Returned pill surfaces the order (approved)', midRows.some(r => r.includes(ORDER_NO)), JSON.stringify(midRows));
            await p.goto(`${BASE}/admin/returns/${retId}`, { waitUntil: 'networkidle2', timeout: 30000 });

            await p.select('select[name="status"]', 'refunded');
            await submitVia('select[name="status"]');
            const refunded = await p.evaluate(() => ({
                alert: document.querySelector('.alert-success')?.textContent?.trim() || '',
                final: document.body.innerText.includes('refunded and final'),
                hasForm: !!document.querySelector('select[name="status"]'),
            }));
            check('refund saved + request locked final',
                refunded.alert.includes('refunded') && refunded.final && !refunded.hasForm, JSON.stringify(refunded));
        } else {
            check('Returned pill surfaces the order (approved)', false, 'refunded option never appeared');
            check('refund saved + request locked final', false, 'refunded option never appeared');
        }
    } else {
        check('approve saved', false, 'no decision form');
        check('refunded option now offered', false, 'no decision form');
        check('Returned pill surfaces the order (approved)', false, 'no decision form');
        check('refund saved + request locked final', false, 'no decision form');
    }

    /* ---- Leg 4: Returned/Refunded pills surface the order ---- */
    console.log('[phase] leg4: pills');
    await p.goto(BASE + '/admin/orders', { waitUntil: 'networkidle2', timeout: 30000 });
    await new Promise(r => setTimeout(r, 1000));
    await clickPill('Returned');
    let rows = await visibleRows();
    check('Returned pill no longer holds it (now refunded)', !rows.some(r => r.includes(ORDER_NO)), JSON.stringify(rows));
    await clickPill('Refunded');
    rows = await visibleRows();
    check('Refunded pill surfaces the order', rows.some(r => r.includes(ORDER_NO)), JSON.stringify(rows));

    // Cleanup: restore the stock this run's approval put back on the shelf
    // (the suite must be stock-NEUTRAL), then reset the fixture so sibling
    // suites' exact-count checks stay deterministic.
    try {
        if (prodId != null && prodStock != null) {
            php(`App\\Models\\Product::whereKey(${prodId})->update(['stock' => ${prodStock}]);`);
        }
        ensureFixture();
    } catch (e) { console.error('cleanup warning:', e.message); }

    let pass = 0;
    for (const [ok, n, d] of results) { console.log(`${ok ? 'PASS' : 'FAIL'}  ${n}${ok || !d ? '' : '  [' + d + ']'}`); if (ok) pass++; }
    console.log(`\n${pass}/${results.length}`);
    await p.close();
    process.exit(pass === results.length ? 0 : 1);
})().catch(e => { console.error('ERR', e.message); process.exit(1); });
