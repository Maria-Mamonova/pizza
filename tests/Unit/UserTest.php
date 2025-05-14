<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_user()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    #[Test]
    public function it_has_orders_relationship()
    {
        $user = User::factory()->hasOrders(3)->create();

        $this->assertCount(3, $user->orders);
    }

    #[Test]
    public function it_sets_default_role_to_user()
    {
        $user = User::factory()->create();

        $this->assertEquals('user', $user->role);
    }

    #[Test]
    public function it_can_create_unverified_user()
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }
    #[Test]
    public function it_hides_password_and_remember_token_in_array()
    {
        $user = User::factory()->create([
            'password' => 'secret',
            'remember_token' => 'tokentest',
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }

    #[Test]
    public function it_has_cart_relationship()
    {
        $user = User::factory()->create();
        $cart = \App\Models\Cart::factory()->create(['user_id' => $user->id]);

        $this->assertNotNull($user->cart);
        $this->assertEquals($cart->id, $user->cart->id);
    }
}
