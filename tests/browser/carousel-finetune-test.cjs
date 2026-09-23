/* Fine-tune verification for the product-detail gallery:
   crossfade on image swap, keyboard arrows + input guard, rewind/speed
   params, slide-counter regression, mobile swiper behavior. */
const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';

(async () => {
    const browser = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
    const page = await browser.newPage();
    await page.setViewport({ width: 1366, height: 900 });

    const errors = [];
    page.on('pageerror', e => errors.push('pageerror: ' + e.message));
    page.on('console', m => { if (m.type() === 'error') errors.push('console: ' + m.text()); });

    // Find a multi-image product: try known slug, else scrape the shop page.
    let url = `${BASE}/product-detail/bracelets`;
    let ok = await probe(url);
    if (!ok) {
        await page.goto(`${BASE}/product`, { waitUntil: 'networkidle2', timeout: 30000 });
        const links = await page.$$eval('a[href*="/product-detail/"]', as => [...new Set(as.map(a => a.href))]);
        for (const l of links) { if (await probe(l)) { url = l; break; } }
    }

    async function probe(u) {
        try {
            const r = await page.goto(u, { waitUntil: 'networkidle2', timeout: 30000 });
            if (!r || !r.ok()) return false;
            const n = await page.$$eval('.thumbSwiper img', els => els.length);
            if (n < 2) return false;
            const single = await page.$eval('.pd-stage', el => el.classList.contains('pd-single'));
            return !single;
        } catch { return false; }
    }

    if (!ok) { console.log('FAIL no multi-image product found'); process.exit(1); }
    console.log('product: ' + url.replace(BASE, ''));

    const results = [];
    const check = (name, pass, detail = '') => { results.push([pass, name, detail]); };

    // 1. Slide counter fully removed (regression)
    const counterCount = await page.$$eval('.pd-counter', els => els.length);
    check('counter removed', counterCount === 0, `found=${counterCount}`);

    // 2. Arrows sit astride the image's outer edges, vertically centered
    const arrowGeo = await page.evaluate(() => {
        const stage = document.querySelector('.pd-stage')?.getBoundingClientRect();
        const imgWrap = document.querySelector('.pd-img-wrap')?.getBoundingClientRect();
        const prev = document.querySelector('.pd-arrow-prev')?.getBoundingClientRect();
        const next = document.querySelector('.pd-arrow-next')?.getBoundingClientRect();
        if (!stage || !imgWrap || !prev || !next) return null;
        return { prevNearEdge: prev.left - imgWrap.left < 80, nextNearEdge: imgWrap.right - next.right < 80,
                 overlap: prev.right > imgWrap.left && next.left < imgWrap.right,
                 vCenter: Math.abs((prev.top + prev.height / 2) - (stage.top + stage.height / 2)) < 20 };
    });
    check('arrows straddle image edges + centered', !!arrowGeo && arrowGeo.prevNearEdge && arrowGeo.nextNearEdge && arrowGeo.overlap && arrowGeo.vCenter, JSON.stringify(arrowGeo));

    // 3. Crossfade: observe the fade class in the SAME tick as the click
    // (the swap window is only 150ms, too tight for two round-trips).
    const src0 = await page.$eval('#mainImg', el => el.src);
    const fading = await page.evaluate(() => {
        document.querySelector('.pd-arrow-next').click();
        return document.getElementById('mainImg').classList.contains('pd-fade-out');
    });
    check('fade-out starts on swap', fading === true);
    await new Promise(r => setTimeout(r, 450));
    const after = await page.$eval('#mainImg', el => ({ src: el.src, fading: el.classList.contains('pd-fade-out') }));
    check('src changed after fade', after.src !== src0, `${src0.slice(-20)} -> ${after.src.slice(-20)}`);
    check('fade class cleaned up', after.fading === false);

    // 4. Keyboard: ArrowRight (body focused) steps the image
    await page.evaluate(() => document.activeElement?.blur());
    await page.keyboard.press('ArrowRight');
    await new Promise(r => setTimeout(r, 450));
    const srcKb = await page.$eval('#mainImg', el => el.src);
    check('keyboard steps image', srcKb !== after.src);

    // 5. Input guard: arrows while qty input focused do NOT move the image
    const hasQty = await page.$('#qtySelector');
    if (hasQty) {
        await page.focus('#qtySelector');
        await page.keyboard.press('ArrowRight');
        await page.keyboard.press('ArrowRight');
        await new Promise(r => setTimeout(r, 450));
        const srcGuard = await page.$eval('#mainImg', el => el.src);
        check('input guard blocks arrows', srcGuard === srcKb, `expected=${srcKb.slice(-16)} got=${srcGuard.slice(-16)}`);
    } else check('input guard blocks arrows', true, 'no qty input on page');

    // 6. Swiper params: rewind + speed
    const params = await page.evaluate(() => ({
        mainRewind: window.mainSwiper?.params?.rewind === true,
        mainSpeed: window.mainSwiper?.params?.speed,
        thumbRewind: window.thumbSwiper?.params?.rewind === true,
        thumbSpeed: window.thumbSwiper?.params?.speed,
        keyboard: window.mainSwiper?.params?.keyboard?.enabled === true,
    }));
    check('mainSwiper rewind', params.mainRewind);
    check('mainSwiper speed 550', params.mainSpeed === 550, `got=${params.mainSpeed}`);
    check('thumbSwiper rewind', params.thumbRewind);
    check('thumbSwiper speed 450', params.thumbSpeed === 450, `got=${params.thumbSpeed}`);
    check('mainSwiper keyboard enabled', params.keyboard);

    // 7. Wrap-around: prev at first slide wraps to last (modulo desktop path)
    const wrapped = await page.evaluate(async () => {
        const thumbs = [...document.querySelectorAll('.thumbSwiper img')];
        thumbs.forEach(t => t.classList.remove('active'));
        thumbs[0].classList.add('active');
        document.querySelector('.pd-arrow-prev')?.click();
        await new Promise(r => setTimeout(r, 400));
        const idx = thumbs.findIndex(t => t.classList.contains('active'));
        return idx; // expect thumbs.length - 1
    });
    check('desktop arrows wrap around', wrapped === (await page.$$eval('.thumbSwiper img', e => e.length)) - 1, `activeIdx=${wrapped}`);

    await page.screenshot({ path: process.env.TEMP + '/finetune-desktop.png' });

    // 8. Mobile: swiper slides via arrow, params live
    await page.setViewport({ width: 390, height: 844, isMobile: true, hasTouch: true });
    await page.reload({ waitUntil: 'networkidle2' });
    const mob = await page.evaluate(async () => {
        if (!window.mainSwiper) return { err: 'no mainSwiper' };
        const before = window.mainSwiper.activeIndex;
        document.querySelector('.mainSwiper .swiper-button-next')?.click();
        await new Promise(r => setTimeout(r, 700));
        return { before, after: window.mainSwiper.activeIndex,
                 rewind: window.mainSwiper.params.rewind === true,
                 speed: window.mainSwiper.params.speed,
                 counter: document.querySelectorAll('.pd-counter').length };
    });
    check('mobile arrow advances slide', mob.after !== undefined && mob.after !== mob.before, JSON.stringify(mob));
    check('mobile rewind+speed', mob.rewind === true && mob.speed === 550);
    check('mobile counter removed', mob.counter === 0, `found=${mob.counter}`);
    await page.screenshot({ path: process.env.TEMP + '/finetune-mobile.png' });

    // 9. No console/page errors across the whole run
    check('zero console errors', errors.length === 0, errors.slice(0, 3).join(' | '));

    let pass = 0;
    for (const [ok2, name, detail] of results) {
        console.log(`${ok2 ? 'PASS' : 'FAIL'}  ${name}${ok2 || !detail ? '' : '  [' + detail + ']'}`);
        if (ok2) pass++;
    }
    console.log(`\n${pass}/${results.length}`);
    await page.close();
    process.exit(pass === results.length ? 0 : 1);
})();
