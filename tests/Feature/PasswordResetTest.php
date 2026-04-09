<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_request_password_reset_link()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'scxipted@gmail.com',
        ]);

        $response = $this->get('/forgot-password');
        $response->assertStatus(200);

        $response = $this->withoutMiddleware()->post('/forgot-password', [
            'email' => 'scxipted@gmail.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status', __('If an account matches that email, a reset link has been sent.'));
    }
}
