<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_token' => null,
        ];
    }

    public function guest(): self
    {
        return $this->state(fn () => [
            'user_id' => null,
            'session_token' => $this->faker->uuid,
        ]);
    }
}
