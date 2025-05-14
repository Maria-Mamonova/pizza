<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Str;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_can_add_and_view_cart_by_session_token(): void
    {
        $product = Product::factory()->create();
        $token = (string) Str::uuid();

        $resp1 = $this->withHeaders(['X-Session-Token' => $token])
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $resp1->assertOk()->assertJsonPath('item.product_id', $product->id);
        $this->assertDatabaseHas('carts', ['session_token' => $token]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $resp2 = $this->withHeaders(['X-Session-Token' => $token])->getJson('/api/cart');
        $resp2->assertOk()->assertJsonCount(1)->assertJsonFragment(['product_id' => $product->id]);
    }

    #[Test]
    public function authenticated_user_gets_same_cart_and_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $sessionToken = (string) Str::uuid();

        Sanctum::actingAs($user, [], 'sanctum');

        $this->withHeaders(['X-Session-Token' => $sessionToken])
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $this->withHeaders(['X-Session-Token' => $sessionToken])
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 3,
            ])->assertOk();

        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($cart);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    #[Test]
    public function cannot_delete_item_from_other_users_cart(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        Sanctum::actingAs($user1, [], 'sanctum');
        $item = CartItem::factory()->create([
            'cart_id' => Cart::factory()->create(['user_id' => $user1->id])->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Sanctum::actingAs($user2, [], 'sanctum');
        $this->deleteJson("/api/cart/{$item->id}")->assertForbidden();
    }
    #[Test]
    public function cannot_add_product_with_invalid_data(): void
    {
        $token = (string) Str::uuid();

        $this->withHeaders(['X-Session-Token' => $token])
            ->postJson('/api/cart', [
                'product_id' => 99999, // несуществующий ID
                'quantity' => 0,       // невалидное количество
            ])
            ->assertStatus(422);
    }

    #[Test]
    public function empty_cart_returns_empty_array(): void
    {
        $token = (string) Str::uuid();

        $response = $this->withHeaders(['X-Session-Token' => $token])
            ->getJson('/api/cart');

        $response->assertOk()->assertExactJson([]);
    }
    #[Test]
    public function guest_with_new_token_gets_new_cart(): void
    {
        $token = (string) Str::uuid();

        $this->withHeaders(['X-Session-Token' => $token])
            ->getJson('/api/cart')
            ->assertOk()
            ->assertExactJson([]);

        $this->assertDatabaseHas('carts', ['session_token' => $token]);
    }
    #[Test]
    public function guest_cart_merges_into_user_cart_on_login(): void
    {
        $product = Product::factory()->create();
        $sessionToken = (string) Str::uuid();
        $guestCart = Cart::factory()->create(['session_token' => $sessionToken]);
        $guestCart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $user = User::factory()->create();
        $userCart = Cart::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user, [], 'sanctum');

        $this->withHeaders(['X-Session-Token' => $sessionToken])
            ->getJson('/api/cart')
            ->assertOk();

        // Проверь, что товар из гостевой корзины появился в пользовательской
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Гостевая корзина удалена
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);
    }
    #[Test]
    public function authenticated_user_without_cart_gets_new_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, [], 'sanctum');

        $this->getJson('/api/cart')->assertOk()->assertExactJson([]);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    }
    #[Test]
    public function guest_without_token_creates_new_cart(): void
    {
        $this->getJson('/api/cart')->assertOk()->assertExactJson([]);

        $this->assertDatabaseCount('carts', 1); // или больше, если другие тесты запущены
    }
    #[Test]
    public function guest_cart_attaches_to_user_when_user_has_no_cart(): void
    {
        $product = Product::factory()->create();
        $sessionToken = (string) Str::uuid();

        $guestCart = Cart::factory()->create(['session_token' => $sessionToken]);
        $guestCart->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $user = User::factory()->create(); // без корзины

        Sanctum::actingAs($user, [], 'sanctum');
        $this->withHeaders(['X-Session-Token' => $sessionToken])->getJson('/api/cart');

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'session_token' => null,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => Cart::where('user_id', $user->id)->first()->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }
    #[Test]
    public function cart_items_return_with_product_details(): void
    {
        $token = (string) Str::uuid();
        $product = Product::factory()->create(['name' => 'Test Product']);

        $cart = Cart::factory()->create(['session_token' => $token]);
        $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->withHeaders(['X-Session-Token' => $token])
            ->getJson('/api/cart')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Test Product']);
    }
}
