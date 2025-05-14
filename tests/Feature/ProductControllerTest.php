<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_all_products(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(3);
    }

    #[Test]
    public function it_returns_single_product(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $product->id]);
    }

    #[Test]
    public function admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, [], 'sanctum');

        $payload = [
            'name'  => 'NewPizza',
            'type'  => 'pizza',
            'price' => 500,
        ];

        $this->postJson('/api/admin/products', $payload)
            ->assertCreated()
            ->assertJsonFragment(['name' => 'NewPizza']);
    }

    #[Test]
    public function admin_can_update_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, [], 'sanctum');

        $product = Product::factory()->create([
            'name'  => 'OldName',
            'type'  => 'drink',
            'price' => 100,
        ]);

        $update = [
            'name'  => 'NewName',
            'type'  => 'pizza',   // <— теперь передаём type
            'price' => 999,       // <— и price
        ];

        $this->putJson("/api/admin/products/{$product->id}", $update)
            ->assertOk()
            ->assertJsonFragment(['name' => 'NewName']);

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'name'  => 'NewName',
            'type'  => 'pizza',
            'price' => 999,
        ]);
    }

    #[Test]
    public function admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, [], 'sanctum');

        $product = Product::factory()->create();

        $this->deleteJson("/api/admin/products/{$product->id}")
            ->assertOk(); // ваш контроллер возвращает 200 с {message}

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
