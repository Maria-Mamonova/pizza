<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthenticateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Пример маршрута, защищённого middleware CustomAuthenticate
        Route::middleware('web') // нужно чтобы session стартовала
        ->middleware(\App\Http\Middleware\CustomAuthenticate::class)
            ->get('/test-auth', function () {
                return response()->json(['message' => 'OK']);
            });
    }

    public function test_unauthenticated_json_request_gets_401(): void
    {
        $response = $this->getJson('/test-auth');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonMissing(['message' => 'OK']);
    }

    public function test_authenticated_user_can_access_protected_route(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, [], 'sanctum');

        $response = $this->getJson('/test-auth');

        $response->assertOk()
            ->assertJson(['message' => 'OK']);
    }

    public function test_non_json_request_aborts_with_401_and_no_redirect(): void
    {
        $response = $this->get('/test-auth', [
            'Accept' => 'text/html',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertStringNotContainsString('Redirecting', $response->getContent());
    }
}
