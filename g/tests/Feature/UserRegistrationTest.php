<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_success()
     {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'phone' => '12345678901',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                ->assertJson(['message' => 'Utilisateur enregistré avec succès.']);
    }

    public function test_user_registration_with_missing_fields()
    {
        $response = $this->postJson('/api/auth/register', [
            // Champs manquants
        ]);

        $response->assertStatus(400);
    }
  public function test_user_registration_with_invalid_email()
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'phone' => '12345678901',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);
    }
     public function test_user_registration_with_existing_email()
    {
        User::create([
            'first_name' => 'Existing',
            'last_name' => 'User',
            'email' => 'existinguser@example.com',
            'phone' => '12345678901',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existinguser@example.com',
            'phone' => '01987654321',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);
    }
     public function test_user_registration_with_existing_phone()
    {
        User::create([
            'first_name' => 'Existing',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'phone' => '12345678901',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'newuser@example.com',
            'phone' => '12345678901',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);
    }
    public function test_user_registration_with_short_password()
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'phone' => '12345678901',
            'password' => 'pwd',
            'password_confirmation' => 'pwd',
        ]);

        $response->assertStatus(400);
    }

    public function test_user_registration_with_unconfirmed_password()
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'phone' => '12345678901',
            'password' => 'password',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(400);
    }
}




