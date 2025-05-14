<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cart;
use Carbon\Carbon;

class ClearOldOrders extends Command
{
    /**
     * Название и сигнатура консольной команды.
     *
     * @var string
     */
    protected $signature = 'app:clear-old-carts';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Удалить корзины (с товарами), созданные более 24 часов назад';

    /**
     * Выполнение команды.
     */
    public function handle(): int
    {
        \Log::info('Запуск очистки старых корзин');

        $threshold = Carbon::now()->subDay();

        // Найти и удалить старые корзины с товарами
        $carts = Cart::where('created_at', '<', $threshold)
            ->whereHas('items') // корзина не пустая
            ->get();

        $count = 0;

        foreach ($carts as $cart) {
            $cart->items()->delete();
            $cart->delete();
            $count++;
        }

        $this->info("Удалено корзин: {$count}");

        return Command::SUCCESS;
    }
}
