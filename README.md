# Shivayra

Luxury jewellery e-commerce storefront + single-owner admin panel, built on
**Laravel 12** with a Blade/Vite front end, Yajra DataTables in the admin,
Swiper galleries, Razorpay + COD checkout, OTP email auth and SMTP
transactional email across the full order lifecycle
(placed → shipped → delivered → cancelled → returned → refunded).

## Repository layout

- `public_html/` — the Laravel application (storefront, admin panel, all tests)
- `docs/` — project plans and test documentation
- `shivayra_db.sql` — database import for a fresh setup
- `tests/admin-suite.sh` — curl-based admin feature suite (repo-adjacent)

## Run locally

```bash
cd public_html
composer install && npm install
npm run build
# either import ../shivayra_db.sql  — or —  php artisan migrate --seed
php artisan serve            # http://127.0.0.1:8000
```

Admin panel lives at `/admin/login`; the owner account is seeded by
`database/seeders/AdminSeeder.php`. Customers sign up/in at `/login` with
email + OTP verification.

## Testing

Two permanent layers — full suite inventory, prerequisites and regression
guards are documented in [`docs/testing.md`](../docs/testing.md):

- **`php artisan test`** — feature tests on sqlite: order-state lifecycle
  emails, the seven-status admin/client rendering matrix, access control, and
  filter-pill label ↔ raw-value pairings.
- **`node tests/browser/<suite>.cjs`** (from `public_html/`, server on `:8000`,
  Edge on debug port `9333`) — five maintained UI suites: `admin-pill-filter`,
  `ui-state-sync`, `carousel-finetune`, `cart-chips`, `returns-lifecycle`.
  Suites create **and clean up** their own fixtures; run them sequentially.
- **`bash tests/admin-suite.sh`** (from the repository root) — HTTP-level
  admin CRUD sweep with self-cleaning `ADMIN-SUITE-*` records.

**Regression guard — COD-cancellation sweep:** the abandoned-checkout sweep in
`CheckoutController::sweepAbandonedRazorpayOrders()` used to cancel *any*
pending + unpaid order older than 24 h — including legitimate COD orders, so
placing one real order could silently cancel a customer's day-old
cash-on-delivery order. The sweep is Razorpay-only, locked by
`OrderStateMatrixTest::test_sweep_cancels_stale_razorpay_but_never_cod`.

## Documentation

`docs/testing.md` · `docs/single-admin-plan.md` · `docs/admin-reenvision.md` ·
`docs/mobile-app-redesign-plan.md`

## License

Laravel framework: MIT. Application code maintained by SkoraInfotech.
