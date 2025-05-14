<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function register_returns_token_and_creates_user(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+70000000000',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    #[Test]
    public function login_with_valid_credentials_returns_token(): void
    {
        User::factory()->create([
            'email' => 'joe@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joe@example.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    #[Test]
    public function login_with_invalid_credentials_gives_422(): void
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('goodpass'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'badpass',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function me_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    #[Test]
    public function me_returns_user_when_authenticated(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, [], 'sanctum');

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    #[Test]
    public function logout_clears_tokens(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, [], 'sanctum');

        $this->postJson('/api/logout')->assertOk();

        $this->assertCount(0, $user->tokens()->get());
    }
    #[Test]
    public function cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'phone' => '+79999999999',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->postJson('/api/register', $payload)
            ->assertStatus(422);
    }

    #[Test]
    public function cannot_register_with_invalid_password(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'invalid@example.com',
            'phone' => '+79990000000',
            'password' => '123', // слишком короткий
            'password_confirmation' => '321', // не совпадает
        ];

        $this->postJson('/api/register', $payload)
            ->assertStatus(422);
    }
    #[Test]
    public function guest_cart_merges_into_user_cart_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'merge@example.com',
            'password' => bcrypt('mergepass'),
        ]);

        // Создаем продукты
        $product1 = \App\Models\Product::factory()->create(['id' => 1]);
        $product2 = \App\Models\Product::factory()->create(['id' => 2]);

        // Гостевая корзина с 2 товарами
        $guestCart = \App\Models\Cart::factory()->guest()->create([
            'session_token' => 'merge-session',
        ]);

        $guestCart->items()->createMany([
            ['product_id' => $product1->id, 'quantity' => 1],
            ['product_id' => $product2->id, 'quantity' => 1],
        ]);

        // У пользователя уже есть корзина с одним таким же продуктом
        $userCart = \App\Models\Cart::factory()->for($user)->create();

        $userCart->items()->create([
            'product_id' => $product1->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'merge@example.com',
            'password' => 'mergepass',
            'session-token' => 'merge-session',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $product1->id,
            'quantity' => 2, // было 1 + 1
        ]);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseMissing('carts', [
            'id' => $guestCart->id,
        ]);
    }
}
