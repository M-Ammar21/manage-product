<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'jhon_doe',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.username', 'jhon_doe');

        $this->assertDatabaseHas('users', ['username' => 'jhon_doe']);
    }

    public function test_register_validation_returns_bad_request(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => '',
            'password' => 'secret',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_user_can_login_and_receive_tokens(): void
    {
        $this->postJson('/api/auth/register', [
            'username' => 'jhon_doe',
            'password' => 'supersecret',
            'password_confirmation' => 'supersecret',
        ])->assertCreated();

        $response = $this->postJson('/api/auth/login', [
            'username' => 'jhon_doe',
            'password' => 'supersecret',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['authentication_token', 'refresh_token', 'token_type', 'expires_at']);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'missing',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid username or password.');
    }
}
