<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_endpoint_creates_user_and_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'new-user@example.com',
            'name' => 'New User',
        ]);
    }

    public function test_login_endpoint_returns_token_and_generates_two_factor_code(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'login-user@example.com',
            'password' => Hash::make('password123'),
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login-user@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $user->refresh();
        $this->assertNotNull($user->two_factor_code);
        $this->assertNotNull($user->two_factor_expires_at);

        Notification::assertSentTo($user, TwoFactorCodeNotification::class);
    }

    public function test_forgot_password_endpoint_returns_success_message(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'If the account exists, a password reset link has been sent.',
            ]);
    }

    public function test_reset_password_endpoint_updates_the_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Password has been reset successfully.',
            ]);

        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }

    public function test_user_endpoint_returns_current_user_payload(): void
    {
        $user = User::factory()->create([
            'role' => 'helpdesk_agent',
            'two_factor_code' => 123456,
            'two_factor_expires_at' => now()->addMinutes(5),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.role', 'helpdesk_agent')
            ->assertJsonPath('two_factor_required', true);
    }

    public function test_verify_endpoint_clears_two_factor_fields_with_valid_code(): void
    {
        $user = User::factory()->create([
            'two_factor_code' => 123456,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/verify', [
            'code' => 123456,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Two factor authentication verified.',
            ]);

        $user->refresh();
        $this->assertNull($user->two_factor_code);
        $this->assertNull($user->two_factor_expires_at);
    }

    public function test_resend_endpoint_regenerates_two_factor_code(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'two_factor_code' => 111111,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/verify/resend');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Two factor code has been resent',
            ]);

        $user->refresh();
        $this->assertNotSame(111111, $user->two_factor_code);
        $this->assertNotNull($user->two_factor_expires_at);

        Notification::assertSentTo($user, TwoFactorCodeNotification::class);
    }

    public function test_logout_endpoint_revokes_all_tokens(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout');

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'You are logged out.',
            ]);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
