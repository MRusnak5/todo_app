<?php

namespace Tests\Feature;
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanLoginWithCorrectCredentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function testUserCannotLoginWithIncorrectCredentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
    }

    public function testUserCanRegisterWithCorrectCredentials()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password',

        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function testUserCannotRegisterWithIncorrectCredentials()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'example',
            'password'              => 'password',

        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('users', [
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
