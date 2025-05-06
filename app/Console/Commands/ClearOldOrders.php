<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class ClearOldOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-old-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удалить заказы в статусе new, созданные более 24 часов назад';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        \Log::info('Запуск планировщика через cron!');

        $count = Order::where('status', 'new')
            ->where('created_at', '<', Carbon::now()->subDay())
            ->delete();

        $this->info("Удалено заказов: {$count}");

        return Command::SUCCESS;
    }
}
