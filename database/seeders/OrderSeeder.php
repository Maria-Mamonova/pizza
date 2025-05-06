<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // Используем первого пользователя
        $products = Product::all();

        if ($user && $products->count()) {
            for ($i = 0; $i < 3; $i++) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'address' => 'Город, улица '.$i,
                    'phone' => '7999000112'.$i,
                    'delivery_time' => now()->addDays($i + 1),
                    'status' => 'new',
                ]);

                $orderItems = $products->random(2)->map(function ($product) use ($order) {
                    return OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => rand(1, 3),
                        'price' => $product->price,
                    ]);
                });
            }
        }
    }
}
