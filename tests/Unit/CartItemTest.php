<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_item_belongs_to_cart_and_product()
    {
        $cartItem = CartItem::factory()->create();

        $this->assertInstanceOf(Cart::class, $cartItem->cart);
        $this->assertInstanceOf(Product::class, $cartItem->product);
    }

    public function test_cannot_create_duplicate_cart_item()
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);
    }
}
