<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_update_is_hashed_and_reported(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPassword123',
        ]);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v2/me', [
            'data' => [
                'attributes' => [
                    'password' => 'NewPassword123',
                    'password_confirmation' => 'NewPassword123',
                    'current_password' => 'OldPassword123',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('meta.password_changed', true)
            ->assertJsonPath('message', 'Profile updated and password changed successfully.');

        $user->refresh();

        $this->assertTrue(Hash::check('NewPassword123', $user->password));
        $this->assertFalse(Hash::check('OldPassword123', $user->password));
    }

    public function test_password_update_requires_minimum_length_and_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPassword123',
        ]);

        $shortPasswordResponse = $this->actingAs($user, 'api')->patchJson('/api/v2/me', [
            'data' => [
                'attributes' => [
                    'password' => 'short',
                    'password_confirmation' => 'short',
                    'current_password' => 'OldPassword123',
                ],
            ],
        ]);

        $shortPasswordResponse->assertStatus(422);

        $mismatchedPasswordResponse = $this->actingAs($user, 'api')->patchJson('/api/v2/me', [
            'data' => [
                'attributes' => [
                    'password' => 'NewPassword123',
                    'password_confirmation' => 'DifferentPassword123',
                    'current_password' => 'OldPassword123',
                ],
            ],
        ]);

        $mismatchedPasswordResponse->assertStatus(422);
    }

    public function test_password_update_rejects_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPassword123',
        ]);

        $response = $this->actingAs($user, 'api')->patchJson('/api/v2/me', [
            'data' => [
                'attributes' => [
                    'password' => 'NewPassword123',
                    'password_confirmation' => 'NewPassword123',
                    'current_password' => 'WrongPassword123',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'The current password is incorrect.')
            ->assertJsonPath('errors.current_password.0', 'The current password is incorrect.');

        $user->refresh();

        $this->assertTrue(Hash::check('OldPassword123', $user->password));
        $this->assertFalse(Hash::check('NewPassword123', $user->password));
    }
}
