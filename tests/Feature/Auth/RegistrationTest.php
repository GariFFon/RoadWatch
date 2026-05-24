<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '9876543210',   // required by RegisteredUserController
            'password' => 'Password1',     // requires mixedCase + number
            'password_confirmation' => 'Password1',
        ]);

        $this->assertAuthenticated();
        // New citizens (public registration) redirect to citizen complaints index
        $response->assertRedirect(route('citizen.complaints.index', absolute: false));
    }
}
