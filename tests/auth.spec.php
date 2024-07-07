<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Organization;
use Tests\TestCase;

// Token Generation Test
uses(RefreshDatabase::class)->beforeEach(function () {
    Artisan::call('migrate');
});

test('token generation and expiration', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $payload = JWTAuth::getPayload($token)->toArray();

    expect($payload['sub'])->toBe($user->id);
    expect($payload['exp'])->toBeLessThanOrEqual(time() + config('jwt.ttl') * 60);
});

// Organization Access Test
test('users cannot see data from organisations they do not have access to', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $org = Organization::factory()->create();
    $org->users()->attach($user1);

    $token = JWTAuth::fromUser($user2);

    $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                     ->getJson('/api/organisations');

    $response->assertStatus(403); // Forbidden access
});

// Successful Registration
test('it should register user successfully with default organisation', function () {
    $response = $this->postJson('/api/auth/register', [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure([
                 'status',
                 'message',
                 'data' => [
                     'accessToken',
                     'user' => [
                         'id',
                         'firstName',
                         'lastName',
                         'email',
                         'phone',
                     ],
                     'organization' => [
                         'id',
                         'name',
                         'description',
                     ]
                 ]
             ]);

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    $this->assertDatabaseHas('organizations', ['name' => "John's Organization"]);
});

// Successful Login
test('it should log the user in successfully', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'status',
                 'message',
                 'data' => [
                     'accessToken',
                     'user' => [
                         'id',
                         'firstName',
                         'lastName',
                         'email',
                         'phone',
                     ]
                 ]
             ]);
});

// Validation Errors
test('it should fail if required fields are missing', function () {
    $response = $this->postJson('/api/auth/register', [
        'lastName' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
             ->assertJson([
                 'status' => 'error',
                 'message' => 'Validation errors',
                 'errors' => [
                     'firstName' => ['The first name field is required.']
                 ]
             ]);
});

// Duplicate Email/UserID
test('it should fail if there is a duplicate email or userId', function () {
    $user = User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
             ->assertJson([
                 'status' => 'error',
                 'message' => 'Validation errors',
                 'errors' => [
                     'email' => ['The email has already been taken.']
                 ]
             ]);
});
