<?php

namespace Tests\Feature;

use App\Http\Controllers\CheckoutController;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Debug matrix for the order-state work: every one of the seven statuses
 * must render correctly in the admin detail view (stepper / locked dropdown /
 * cancelled lock) and in the untouched client order page, access control on
 * the status endpoint, validation of unknown values, and the abandoned-
 * checkout sweep never touching COD orders.
 */
class OrderStateMatrixTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = $this->makeUser('Matrix Admin');
        $this->admin->assignRole('admin');
        $this->customer = $this->makeUser('Matrix Customer');
    }

    private function makeUser(string $name): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = strtolower(str_replace(' ', '.', $name)) . '-' . uniqid() . '@example.com';
        $user->password = bcrypt('secret-password');
        $user->save();

        return $user;
    }

    private function makeOrder(string $suffix, array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'SHV-M-' . $suffix,
            'user_id' => $this->customer->id,
            'name' => 'Matrix Customer',
            'email' => 'matrix@example.com',
            'phone' => '9999999999',
            'address' => '1 Test Street',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'subtotal' => 500,
            'total' => 500,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ], $overrides));

        $item = new OrderItem();
        $item->order_id = $order->id;
        $item->product_name = 'Matrix Item';
        $item->price = 500;
        $item->qty = 1;
        $item->total = 500;
        $item->save();

        return $order;
    }

    public function test_admin_show_renders_every_status(): void
    {
        $expectations = [
            'pending'    => fn ($r) => $r->assertSee('class="status-stepper"', false)->assertSee('>Placed</option>', false),
            'processing' => fn ($r) => $r->assertSee('class="status-stepper"', false)->assertSee('>Processing</option>', false),
            'shipped'    => fn ($r) => $r->assertSee('class="status-stepper"', false)->assertSee('>Shipped</option>', false),
            'completed'  => fn ($r) => $r->assertSee('class="status-stepper"', false)->assertSee('>Delivered</option>', false),
            'cancelled'  => fn ($r) => $r->assertSee('This order is cancelled and final', false)->assertDontSee('select name="order_status"'),
            'returned'   => fn ($r) => $r->assertSee('name="order_status" value="returned"', false)->assertSee('class="form-select" disabled', false)->assertSee('RETURNED', false),
            'refunded'   => fn ($r) => $r->assertSee('name="order_status" value="refunded"', false)->assertSee('class="form-select" disabled', false)->assertSee('REFUNDED', false),
        ];

        foreach ($expectations as $status => $assert) {
            $order = $this->makeOrder(strtoupper($status), ['order_status' => $status]);
            $assert(
                $this->actingAs($this->admin)->get(route('admin.orders.show', $order->id))->assertOk()
            );
        }
    }

    public function test_client_order_detail_renders_every_status(): void
    {
        foreach (['pending', 'processing', 'shipped', 'completed', 'cancelled', 'returned', 'refunded'] as $status) {
            $order = $this->makeOrder('C' . strtoupper($status), ['order_status' => $status]);

            $response = $this->actingAs($this->customer)
                ->get('/order-detail/' . $order->order_number)
                ->assertOk()
                ->assertSee('Shipment Status');

            if ($status === 'cancelled') {
                $response->assertSee('This order has been cancelled');
            } elseif (in_array($status, ['returned', 'refunded'], true)) {
                // Raw badge reflects the new value; the four timeline states
                // themselves are untouched.
                $response->assertSee(ucfirst($status))->assertSee('Delivered');
            } else {
                $response->assertSee('Processing')->assertSee('Delivered');
            }
        }
    }

    public function test_guest_is_redirected_from_status_endpoint(): void
    {
        $order = $this->makeOrder('GUEST');

        $this->post(route('admin.orders.updateStatus', $order->id), [
            'order_status' => 'shipped',
            'payment_status' => 'pending',
        ])->assertRedirect(route('admin.login')); // admin group owns its own login

        $this->assertSame('pending', $order->refresh()->order_status);
    }

    public function test_customer_is_forbidden_from_status_endpoint(): void
    {
        $order = $this->makeOrder('FORBID');

        $this->actingAs($this->customer)->post(route('admin.orders.updateStatus', $order->id), [
            'order_status' => 'cancelled',
            'payment_status' => 'pending',
        ])->assertForbidden();

        $this->assertSame('pending', $order->refresh()->order_status);
    }

    public function test_unknown_status_value_is_rejected(): void
    {
        $order = $this->makeOrder('BADVAL');

        $this->actingAs($this->admin)->post(route('admin.orders.updateStatus', $order->id), [
            'order_status' => 'delivered', // display label — not a stored value
            'payment_status' => 'pending',
        ])->assertSessionHasErrors('order_status');

        $this->assertSame('pending', $order->refresh()->order_status);
    }

    public function test_sweep_cancels_stale_razorpay_but_never_cod(): void
    {
        Mail::fake();

        $cod = $this->makeOrder('SWEEPCOD');
        $cod->created_at = now()->subDays(2);
        $cod->save();

        $fresh = $this->makeOrder('SWEEPFRESH', ['payment_method' => 'razorpay']);
        $fresh->created_at = now()->subHours(2);
        $fresh->save();

        $stale = $this->makeOrder('SWEEPRZP', ['payment_method' => 'razorpay']);
        $stale->created_at = now()->subDays(2);
        $stale->save();

        CheckoutController::sweepAbandonedRazorpayOrders();

        // COD older than a day is a legitimate unpaid order — must survive.
        $this->assertSame('pending', $cod->refresh()->order_status, 'COD orders must never be swept');
        $this->assertSame('pending', $cod->payment_status);

        // Recent Razorpay session — not stale yet.
        $this->assertSame('pending', $fresh->refresh()->order_status);

        // Abandoned Razorpay checkout past a day — cancelled + payment failed.
        $stale->refresh();
        $this->assertSame('cancelled', $stale->order_status);
        $this->assertSame('failed', $stale->payment_status);

        // The sweep is silent: those customers never received a confirmation,
        // so there is nothing to cancel from their point of view.
        Mail::assertNotSent(OrderStatusMail::class);
        Mail::assertNotQueued(OrderStatusMail::class);
    }
}
