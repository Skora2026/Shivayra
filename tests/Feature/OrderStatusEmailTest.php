<?php

namespace Tests\Feature;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Order lifecycle emails + admin/storefront state sync.
 *
 * Covered states: shipped, delivered, cancelled, returned, refunded
 * ("ordered" is fired at checkout by OrderPlacedMail and proven live
 * separately). Also locks: no email on no-op resubmits, completed_at only
 * moves when the status changes, cancelled stays terminal, rejection after
 * approval rolls "returned" back to delivered, and the admin UI speaks the
 * same four labels as the customer's shipment timeline.
 */
class OrderStatusEmailTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->admin = $this->makeUser('Admin Tester');
        $this->admin->assignRole('admin');
        $this->customer = $this->makeUser('Storefront Customer');
    }

    /** This project ships no factories — build users directly. */
    private function makeUser(string $name): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = strtolower(str_replace(' ', '.', $name)) . '-' . uniqid() . '@example.com';
        $user->password = bcrypt('secret-password');
        $user->save();

        return $user;
    }

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'SHV-' . uniqid(),
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '9999999999',
            'address' => '1 Test Street',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'pincode' => '411001',
            'subtotal' => 1000,
            'total' => 1000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ], $overrides));
    }

    private function makeItem(Order $order): OrderItem
    {
        $item = new OrderItem();
        $item->order_id = $order->id;
        $item->product_name = 'Test Ring';
        $item->price = 1000;
        $item->qty = 1;
        $item->total = 1000;
        $item->save();

        return $item;
    }

    private function makeReturnRequest(Order $order, OrderItem $item): ReturnRequest
    {
        $return = new ReturnRequest();
        $return->request_number = 'RET-' . uniqid();
        $return->order_id = $order->id;
        $return->user_id = $this->customer->id;
        $return->order_item_id = $item->id;
        $return->status = 'pending';
        $return->reason = 'Size does not fit';
        $return->save();

        return $return;
    }

    private function setStatus(Order $order, string $status, string $payment = 'pending'): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.orders.updateStatus', $order->id), [
                'order_status' => $status,
                'payment_status' => $payment,
            ])
            ->assertRedirect();
    }

    public function test_shipped_transition_updates_status_and_emails_customer(): void
    {
        $order = $this->makeOrder();
        Mail::fake();

        $this->setStatus($order, 'shipped');

        $this->assertSame('shipped', $order->refresh()->order_status);
        Mail::assertQueued(
            OrderStatusMail::class,
            fn ($m) => $m->hasTo('customer@example.com') && $m->state === 'shipped'
        );
    }

    public function test_delivered_transition_stamps_completed_at_and_emails_customer(): void
    {
        $order = $this->makeOrder(['order_status' => 'shipped']);
        Mail::fake();

        $this->setStatus($order, 'completed');

        $order->refresh();
        $this->assertSame('completed', $order->order_status);
        $this->assertNotNull($order->completed_at, 'delivery anchor must be stamped');
        Mail::assertQueued(
            OrderStatusMail::class,
            fn ($m) => $m->state === 'delivered'
        );
    }

    public function test_cancelled_transition_is_final_and_emails_customer(): void
    {
        $order = $this->makeOrder();
        $this->makeItem($order);
        Mail::fake();

        $this->setStatus($order, 'cancelled');

        $this->assertSame('cancelled', $order->refresh()->order_status);
        Mail::assertQueued(
            OrderStatusMail::class,
            fn ($m) => $m->state === 'cancelled'
        );

        // Terminal: reopening must be refused and the status must not move.
        Mail::fake();
        $this->actingAs($this->admin)
            ->post(route('admin.orders.updateStatus', $order->id), [
                'order_status' => 'processing',
                'payment_status' => 'pending',
            ])
            ->assertSessionHas('error');
        $this->assertSame('cancelled', $order->refresh()->order_status);
        Mail::assertNotSent(OrderStatusMail::class);
    }

    public function test_resubmitting_same_status_sends_no_email_and_keeps_anchor(): void
    {
        $order = $this->makeOrder();
        $this->setStatus($order, 'completed');
        $anchor = $order->refresh()->completed_at;
        $this->assertNotNull($anchor);

        Mail::fake();
        // Same status again — e.g. admin only fixes the payment field.
        $this->setStatus($order, 'completed', 'paid');

        $order->refresh();
        $this->assertSame('completed', $order->order_status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertTrue(
            $anchor->equalTo($order->completed_at),
            'a no-op resubmit must never move the return-window anchor'
        );
        Mail::assertNotSent(OrderStatusMail::class);
    }

    public function test_return_approval_marks_order_returned_and_emails_customer(): void
    {
        $order = $this->makeOrder();
        $this->setStatus($order, 'completed');
        $anchor = $order->refresh()->completed_at;
        $item = $this->makeItem($order);
        $return = $this->makeReturnRequest($order, $item);
        Mail::fake();

        $this->actingAs($this->admin)
            ->post(route('admin.returns.status', $return->id), [
                'status' => 'approved',
                'admin_note' => 'Approved after inspection',
            ])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('returned', $order->order_status);
        $this->assertTrue(
            $anchor->equalTo($order->completed_at),
            'the return window anchor must survive the returned transition'
        );
        $this->assertSame('approved', $return->refresh()->status);
        Mail::assertQueued(
            OrderStatusMail::class,
            fn ($m) => $m->hasTo('customer@example.com') && $m->state === 'returned'
        );
    }

    public function test_refund_marks_order_refunded_and_emails_customer(): void
    {
        $order = $this->makeOrder(['payment_status' => 'paid']);
        $this->setStatus($order, 'completed', 'paid');
        $item = $this->makeItem($order);
        $return = $this->makeReturnRequest($order, $item);

        $this->actingAs($this->admin)
            ->post(route('admin.returns.status', $return->id), ['status' => 'approved'])
            ->assertRedirect();

        Mail::fake();
        $this->actingAs($this->admin)
            ->post(route('admin.returns.status', $return->id), ['status' => 'refunded'])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('refunded', $order->order_status);
        $this->assertSame('refunded', $order->payment_status);
        Mail::assertQueued(
            OrderStatusMail::class,
            fn ($m) => $m->state === 'refunded'
        );
    }

    public function test_rejection_after_approval_restores_delivered_without_email(): void
    {
        $order = $this->makeOrder();
        $this->setStatus($order, 'completed');
        $item = $this->makeItem($order);
        $return = $this->makeReturnRequest($order, $item);

        $this->actingAs($this->admin)
            ->post(route('admin.returns.status', $return->id), ['status' => 'approved'])
            ->assertRedirect();
        $this->assertSame('returned', $order->refresh()->order_status);

        Mail::fake();
        $this->actingAs($this->admin)
            ->post(route('admin.returns.status', $return->id), ['status' => 'rejected'])
            ->assertRedirect();

        $this->assertSame('completed', $order->refresh()->order_status);
        Mail::assertNotSent(OrderStatusMail::class);
    }

    public function test_admin_ui_speaks_the_client_four_states(): void
    {
        $order = $this->makeOrder();

        // Detail page: dropdown + stepper use the storefront's four labels.
        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order->id))
            ->assertOk()
            ->assertSee('>Placed</option>', false)
            ->assertSee('>Shipped</option>', false)
            ->assertSee('>Delivered</option>', false)
            ->assertDontSee('>Completed</option>', false); // stale order label (payment select legitimately keeps Pending)

        // Orders list: filter pills match the badge labels, incl. Shipped.
        $this->actingAs($this->admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            // Pills show client-facing labels but filter the raw DB values
            // (server-side LIKE) — label + key must stay paired.
            ->assertSee('data-dt-search="pending">Placed</a>', false)
            ->assertSee('data-dt-search="shipped">Shipped</a>', false)
            ->assertSee('data-dt-search="completed">Delivered</a>', false)
            // Returned/Refunded are their own categories, not under Delivered.
            ->assertSee('data-dt-search="returned">Returned</a>', false)
            ->assertSee('data-dt-search="refunded">Refunded</a>', false)
            ->assertDontSee('data-dt-search="pending">Pending</a>', false);
    }

    public function test_return_module_states_lock_the_status_dropdown(): void
    {
        $order = $this->makeOrder();
        $this->setStatus($order, 'completed');
        $item = $this->makeItem($order);
        $return = $this->makeReturnRequest($order, $item);
        $this->actingAs($this->admin)
            ->post(route('admin.returns.status', $return->id), ['status' => 'approved'])
            ->assertRedirect();
        $this->assertSame('returned', $order->refresh()->order_status);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order->id));
        $response->assertOk()
            ->assertSee('disabled', false)
            ->assertSee('name="order_status" value="returned"', false)
            ->assertSee('RETURNED', false);

        // And the hidden twin keeps the form submittable without moving it.
        Mail::fake();
        $this->actingAs($this->admin)
            ->post(route('admin.orders.updateStatus', $order->id), [
                'order_status' => 'returned',
                'payment_status' => 'pending',
            ])
            ->assertRedirect();
        $this->assertSame('returned', $order->refresh()->order_status);
        Mail::assertNotSent(OrderStatusMail::class);
    }
}
