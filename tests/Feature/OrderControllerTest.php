<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_user_can_see_their_orders(): void
    {
        $user = User::factory()->create();
        // создаём 2 заказа для этого пользователя и 1 для другого
        Order::factory()->count(2)->create(['user_id' => $user->id]);
        Order::factory()->create();

        Sanctum::actingAs($user, [], 'sanctum');
        $response = $this->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonCount(2);
    }

    #[Test]
    public function user_can_view_specific_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        // Подменяем авторизацию: разрешаем всем, кто user_id == owner
        \Illuminate\Support\Facades\Gate::define('view', function ($authUser, $orderToView) {
            return $authUser->id === $orderToView->user_id;
        });

        Sanctum::actingAs($user, [], 'sanctum');

        $this->getJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $order->id]);
    }

    #[Test]
    public function user_cannot_view_others_orders(): void
    {
        $user   = User::factory()->create();
        $other  = User::factory()->create();
        $order  = Order::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($user, [], 'sanctum');
        $this->getJson("/api/orders/{$order->id}")
            ->assertForbidden();
    }

    #[Test]
    public function user_can_create_order_from_cart(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();
        // имитируем корзину с одним товаром
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create([
            'cart_id'    => $cart->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        Sanctum::actingAs($user, [], 'sanctum');
        $payload = [
            'address'       => 'Ул. Тестовая, 1',
            'phone'         => '+70000000000',
            'delivery_time' => now()->addHour()->toDateTimeString(),
        ];

        $this->postJson('/api/orders', $payload)
            ->assertCreated()
            ->assertJsonStructure(['message', 'order' => ['id', 'user_id', 'address', 'phone', 'status']]);

        // корзина должна очиститься
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'address' => $payload['address']]);
    }
    #[Test]
    public function only_admin_can_access_all_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        Order::factory()->count(3)->create();

        Sanctum::actingAs($admin, [], 'sanctum');
        $response = $this->getJson('/api/admin/orders');
        $response->assertOk()->assertJsonCount(3);

        Sanctum::actingAs($user, [], 'sanctum');
        $this->getJson('/api/admin/orders')->assertForbidden();
    }
    #[Test]
    public function cannot_create_order_if_cart_is_empty(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user, [], 'sanctum');
        $payload = [
            'address'       => 'Empty cart',
            'phone'         => '+79999999999',
            'delivery_time' => now()->addHour()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/orders', $payload);
        $response->assertStatus(400); // Как в контроллере
    }
    #[Test]
    public function guest_cannot_create_order(): void
    {
        $payload = [
            'address'       => 'Guest attempt',
            'phone'         => '+70000000000',
            'delivery_time' => now()->addHour()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/orders', $payload);
        $response->assertStatus(401); // Unauthorized
    }
}
