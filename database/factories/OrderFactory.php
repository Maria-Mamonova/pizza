<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\User;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => 'new',
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'delivery_time' => $this->faker->dateTimeBetween('+1 hour', '+2 days'),
        ];
    }
}
