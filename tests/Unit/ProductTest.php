<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_product()
    {
        $product = Product::factory()->create([
            'name' => 'Пицца Пепперони',
            'type' => 'pizza',
            'price' => 999.99,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Пицца Пепперони',
            'type' => 'pizza',
            'price' => 999.99,
        ]);
    }

    #[Test]
    public function it_has_cart_items_relationship()
    {
        $product = Product::factory()->has(CartItem::factory()->count(2))->create();

        $this->assertCount(2, $product->cartItems);
    }

    #[Test]
    public function it_has_order_items_relationship()
    {
        $product = Product::factory()->has(OrderItem::factory()->count(3))->create();

        $this->assertCount(3, $product->orderItems);
    }
}
