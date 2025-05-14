<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function only_admin_can_access_orders_index(): void
    {
        $response = $this->getJson('/api/admin/orders');
        $response->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create(['role' => 'user']), [], 'sanctum');
        $this->getJson('/api/admin/orders')->assertForbidden();

        Sanctum::actingAs($this->admin, [], 'sanctum');
        $this->getJson('/api/admin/orders')->assertOk();
    }

    #[Test]
    public function admin_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'new']);

        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->postJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertOk();
        $this->assertEquals('delivered', $order->fresh()->status);
    }

    #[Test]
    public function admin_can_view_order_details(): void
    {
        $product = Product::factory()->create();
        $order = Order::factory()->hasItems(1, ['product_id' => $product->id])->create();

        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->getJson("/api/admin/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'user',
                'items',
                'status',
            ])
            ->assertJsonPath('items.0.product.id', $product->id);
    }

    #[Test]
    public function admin_can_delete_order(): void
    {
        $order = Order::factory()->create();

        Sanctum::actingAs($this->admin, [], 'sanctum');

        $response = $this->deleteJson("/api/admin/orders/{$order->id}");

        $response->assertOk(); // изменено с assertNoContent
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
