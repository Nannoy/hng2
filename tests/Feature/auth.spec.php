<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Artisan;
use PhpParser\Token;
use App\Models\Organization;
use Illuminate\Support\Str;


class Auth_spec extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_register_successfully()
    {
        // Generate fake user data using Faker (optional but useful for testing)
        $userData = [
            'userId'=> str::uuid(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123', // Ensure it meets validation rules
            'phone' => '+1234567890', // Example phone number
        ];

        // Make a POST request to the registration endpoint
        $response = $this->json('POST', '/api/auth/register', $userData);

        // Assert that the registration was successful (HTTP status code 201 Created)
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'user' => [
                             'userId',
                             'firstName',
                             'lastName',
                             'email',
                             // Add more fields as per your response structure
                         ],
                         // Add more data structures as per your response
                     ],
                 ]);

        // Assert that a user record was created in the database
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            // Add more assertions for other fields if needed
        ]);
    }
}
