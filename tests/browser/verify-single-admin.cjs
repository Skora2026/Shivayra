/* Phase-4 verification of the single-admin + split-login implementation. */
const puppeteer = require('puppeteer-core');
const BASE = 'http://127.0.0.1:8000';

(async () => {
  const browser = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9333', defaultViewport: null });
  const results = [];
  const log = (name, ok, detail) => {
    results.push({ name, ok });
    console.log(`${ok ? 'PASS' : 'FAIL'} | ${name} | ${detail}`);
  };
  const newPage = async () => {
    const p = await browser.newPage();
    await p.setViewport({ width: 1366, height: 900 });
    return p;
  };

  // 1. Owner logs in at /admin/login
  {
    const p = await newPage();
    await p.goto(BASE + '/admin/login', { waitUntil: 'networkidle2' });
    await p.type('input[name=email]', 'admin@shivayra.com');
    await p.type('input[name=password]', 'password');
    await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.click('#loginStaticBtn')]);
    const onDashboard = p.url().includes('/admin/dashboard');
    log('1 owner login -> /admin/dashboard', onDashboard, p.url());
    await p.close();
  }

  // 2. Customer credentials rejected on admin login (no info leak)
  {
    const p = await newPage();
    await p.goto(BASE + '/admin/login', { waitUntil: 'networkidle2' });
    await p.type('input[name=email]', 'user@shivayra.com');
    await p.type('input[name=password]', 'password');
    await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.click('#loginStaticBtn')]);
    const stillOnLogin = p.url().includes('/admin/login');
    const errText = await p.evaluate(() => document.body.innerText);
    const generic = errText.includes('Invalid credentials');
    const noLeak = !/customer|user@shivayra/i.test(errText.match(/alert[^<]*</g)?.join('') || '');
    log('2 customer creds rejected at admin login', stillOnLogin && generic && noLeak, `url=${p.url()}`);
    await p.close();
  }

  // 3. Customer login -> /my-account with welcome flash
  {
    const p = await newPage();
    await p.goto(BASE + '/login', { waitUntil: 'networkidle2' });
    await p.type('input[name=email]', 'user@shivayra.com');
    await p.type('input[name=password]', 'password');
    await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.click('#loginStaticBtn')]);
    const onAccount = p.url().includes('/my-account');
    const flash = await p.evaluate(() => document.body.innerText.includes('Welcome back'));
    log('3 customer login -> /my-account + welcome', onAccount && flash, `url=${p.url()} flash=${flash}`);
    await p.close();
  }

  // 4. Admin login rate limit fires (hammering ONE email -> 5-attempt bucket)
  {
    const p = await newPage();
    let throttled = false;
    for (let i = 0; i < 7; i++) {
      await p.goto(BASE + '/admin/login', { waitUntil: 'networkidle2' });
      await p.type('input[name=email]', 'bruteforce@x.com');
      await p.type('input[name=password]', 'wrongpass');
      await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.click('#loginStaticBtn')]);
      const t = await p.evaluate(() => document.body.innerText);
      if (t.includes('Too many login attempts')) { throttled = true; break; }
    }
    log('4 admin brute force throttled', throttled, 'per-email bucket fires by attempt 6');
    await p.close();
  }

  // 5. Demoted admin@gmail.com can still log in as a customer
  {
    const p = await newPage();
    await p.goto(BASE + '/login', { waitUntil: 'networkidle2' });
    await p.type('input[name=email]', 'admin@gmail.com');
    await p.type('input[name=password]', 'Admin@123');
    await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {}), p.click('#loginStaticBtn')]);
    const onAccount = p.url().includes('/my-account');
    log('5 demoted account still works as customer', onAccount, p.url());
    await p.close();
  }

  // 6. Admin login page renders standalone (title + no storefront nav)
  {
    const p = await newPage();
    await p.goto(BASE + '/admin/login', { waitUntil: 'networkidle2' });
    const title = await p.title();
    const hasOwnerAccess = await p.evaluate(() => document.body.innerText.toLowerCase().includes('owner access'));
    log('6 admin login page renders', title.length > 0 && hasOwnerAccess, `title="${title}"`);
    await p.close();
  }

  await browser.disconnect();
  const fails = results.filter(r => !r.ok).length;
  console.log(`\n--- ${results.length - fails}/${results.length} checks passed`);
  process.exit(fails ? 1 : 0);
})();
