<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_an_order_item()
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
        ]);
    }

    #[Test]
    public function it_belongs_to_an_order()
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertNotNull($orderItem->order);
        $this->assertEquals($orderItem->order->id, $orderItem->order_id);
    }

    #[Test]
    public function it_belongs_to_a_product()
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertNotNull($orderItem->product);
        $this->assertEquals($orderItem->product->id, $orderItem->product_id);
    }
}
