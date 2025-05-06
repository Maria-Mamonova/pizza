<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $products = Product::all();

        // 1 корзина для пользователя
        if ($user && $products->count()) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'session_token' => null,
            ]);

            $products->random(2)->each(function ($product) use ($cart) {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 2),
                ]);
            });
        }

        // 1 гостевая корзина
        $guestCart = Cart::create([
            'user_id' => null,
            'session_token' => Str::uuid(),
        ]);

        $products->random(2)->each(function ($product) use ($guestCart) {
            CartItem::create([
                'cart_id' => $guestCart->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 2),
            ]);
        });
    }
}
