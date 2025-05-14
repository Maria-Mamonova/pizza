<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Cart;
use App\Models\CartItem;
use Carbon\Carbon;

class ClearOldOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_old_carts_with_items()
    {
        // Старая корзина с товаром
        $oldCart = Cart::factory()->create([
            'created_at' => Carbon::now()->subDays(2),
        ]);
        CartItem::factory()->create(['cart_id' => $oldCart->id]);

        // Новая корзина с товаром
        $newCart = Cart::factory()->create([
            'created_at' => Carbon::now()->subHours(2),
        ]);
        CartItem::factory()->create(['cart_id' => $newCart->id]);

        // Вызов команды
        $this->artisan('app:clear-old-carts')
            ->expectsOutput('Удалено корзин: 1')
            ->assertExitCode(0);

        // Проверка, что старая удалена, новая осталась
        $this->assertDatabaseMissing('carts', ['id' => $oldCart->id]);
        $this->assertDatabaseHas('carts', ['id' => $newCart->id]);
    }
}
