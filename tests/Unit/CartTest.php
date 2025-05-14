<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_belongs_to_user()
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->for($user)->create();

        $this->assertTrue($cart->user->is($user));
    }

    public function test_guest_cart_has_session_token()
    {
        $cart = Cart::factory()->guest()->create();

        $this->assertNull($cart->user_id);
        $this->assertNotNull($cart->session_token);
    }
}
