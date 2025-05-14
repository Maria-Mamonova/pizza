<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    #[Test]
    public function it_has_order_items(): void
    {
        $order = Order::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertTrue($order->items->contains($item));
    }

    #[Test]
    public function it_has_fillable_fields(): void
    {
        $orderData = [
            'user_id' => 1,
            'address' => 'ул. Проверочная, 5',
            'phone' => '+70000000001',
            'delivery_time' => now()->addDay(),
            'status' => 'new',
        ];

        $order = new Order();
        $order->fill($orderData);

        foreach ($orderData as $key => $value) {
            $this->assertEquals($value, $order->{$key});
        }
    }
}
