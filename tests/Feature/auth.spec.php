<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;


class Auth_spec extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_successfully_with_default_organization()
    {
        // Generate fake user data using Faker (optional but useful for testing)
        $userData = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('password123'), // Hash the password
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
        $this->assertDatabaseHas('organizations', [
            'name' => 'John\'s Organization',
            // Add more assertions for other fields if needed
        ]);
    }

    /** @test */
    public function it_should_fail_if_theres_duplicate_email()
    {
        // Generate fake user data using Faker (optional but useful for testing)
        $userData = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('password123'), // Hash the password
            'phone' => '+1234567890', // Example phone number
        ];
    
        // First user registration attempt
        $response1 = $this->json('POST', '/api/auth/register', $userData);
    
        // Assert that the first registration was successful (HTTP status code 201 Created)
        $response1->assertStatus(201)
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
    
        // Second user registration attempt with the same email
        $response2 = $this->json('POST', '/api/auth/register', $userData);
    
        // Assert that the second registration fails with HTTP status code 422 Unprocessable Entity
        $response2->assertStatus(422)
                  ->assertJson([
                             'status'=> 'Bad request',
                             'message'=> 'Registration unsuccessful!',
                  ]);
    }

    /** @test */
    public function user_can_register_and_login_with_correct_credentials()
    {
        // Generate fake user data using Faker (optional but useful for testing)
        $userData = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123', // Hash the password
            'phone' => '+1234567890', // Example phone number
        ];

        // Make a POST request to register the user
        $this->json('POST', '/api/auth/register', $userData)
             ->assertStatus(201); // Assert successful registration
        
        // Make a POST request to login with correct credentials
        $loginData = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $this->json('POST', '/api/auth/login', $loginData)
             ->assertStatus(200); // Assert successful login
    }

    /** @test */
    public function user_cannot_login_with_incorrect_credentials()
    {
        // Generate fake user data using Faker (optional but useful for testing)
        $userData = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123', // Hash the password
            'phone' => '+1234567890', // Example phone number
        ];

        // Make a POST request to register the user
        $this->json('POST', '/api/auth/register', $userData)
             ->assertStatus(201); // Assert successful registration
        
        // Make a POST request to login with incorrect credentials
        $loginData = [
            'email' => 'john.doe@example.com',
            'password' => 'wrongpassword',
        ];

        $this->json('POST', '/api/auth/login', $loginData)
             ->assertStatus(401); // Assert unauthorized access due to incorrect credentials
    }

    /** @test */
    public function successful_login_return_authToken_and_user_details()
    {
        // Create a user
        $user = User::create([
            'userId' => (string) Str::uuid(),
            'firstName' => 'Test2',
            'lastName' => 'Test2',
            'email' => 'email1@email.com',
            'password' => bcrypt('password123'), // Hash the password
            'phone' => '090876712882',
        ]);

        // Make a POST request to login with correct credentials
        $loginData = [
            'email' => 'email1@email.com',
            'password' => 'password123',
        ];

        $response = $this->json('POST', '/api/auth/login', $loginData);

        // Assert that the login was successful (HTTP status code 200)
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Login successful',
                     'data' => [
                         'accessToken' => true, // Ensure accessToken is present
                         'user' => [
                             'userId' => $user->userId,
                             'firstName' => $user->firstName,
                             'lastName' => $user->lastName,
                             'email' => $user->email,
                             'phone' => $user->phone,
                         ],
                     ],
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'accessToken',
                         'user' => [
                             'userId',
                             'firstName',
                             'lastName',
                             'email',
                             'phone',
                         ],
                     ],
                 ]);
    }

    /** @test */
    public function user_cannot_register_without_email()
    {
        $userData = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'password' => 'Password101',
            'phone' => '090876667',
        ];

        $response = $this->json('POST', '/api/auth/register', $userData);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_register_without_lastName()
    {
        $userData = [
            'firstName' => 'John',
            'email' => 'johndoe@email.com',
            'password' => 'Password101',
            'phone' => '090876667',
        ];

        $response = $this->json('POST', '/api/auth/register', $userData);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_register_without_firstName()
    {
        $userData = [
            'lastName' => 'John',
            'email' => 'johndoe@email.com',
            'password' => 'Password101',
            'phone' => '090876667',
        ];

        $response = $this->json('POST', '/api/auth/register', $userData);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_cannot_register_without_password()
    {
        $userData = [
            'firstName' => 'John',
            'email' => 'johndoe@email.com',
            'lastName' => 'Doe',
            'phone' => '090876667',
        ];

        $response = $this->json('POST', '/api/auth/register', $userData);

        $response->assertStatus(422);
    }


    /** @test */
    public function user_cannot_acces_organization_they_dosent_belong_to()
    {
        // Register User 1
        $user1Data = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $response1 = $this->json('POST', '/api/auth/register', $user1Data);

        // Assert User 1 registration success
        $response1->assertStatus(201);

        // Extract User 1 access token and orgId
        $this->user1AccessToken = $response1['data']['accessToken'];
        $this->user1OrgId = $this->retrieveOrgId($this->user1AccessToken);

        // Register User 2
        $user2Data = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'password456',
        ];

        $response2 = $this->json('POST', '/api/auth/register', $user2Data);

        // Assert User 2 registration success
        $response2->assertStatus(201);

        // Extract User 2 access token
        $this->user2AccessToken = $response2['data']['accessToken'];

        // Attempt to access User 1's organization with User 2's token
        $response3 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->user2AccessToken,
        ])->json('GET', "/api/organisations/{$this->user1OrgId}");

        // Assert that User 2 receives a 403 Forbidden error
        $response3->assertStatus(403);
    }

    /**
     * Helper function to retrieve orgId using User 1's access token.
     *
     * @param string $accessToken
     * @return string
     */
    protected function retrieveOrgId($accessToken)
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->json('GET', '/api/organisations');

        return $response['data']['organisations'][0]['orgId'];
    }


    /** @test */
    public function token_genration_and_expiry_and_user_details()
{
    // Register a new user
    $userData = [
        'userId' => (string) Str::uuid(),
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@example.com',
        'password' => 'password123',
    ];

    $response = $this->json('POST', '/api/auth/register', $userData);

    // Assert registration is successful
    $response->assertStatus(201);

    // Extract access token and user ID from the response
    $accessToken = $response['data']['accessToken'];
    $userId = $response['data']['user']['userId'];

    // Use the access token to authenticate and fetch user details
    $userDetailsResponse = $this->withHeaders([
        'Authorization' => 'Bearer ' . $accessToken,
    ])->json('GET', "/api/users/{$userId}");

    // Assert that the endpoint returns user details successfully
    $userDetailsResponse->assertStatus(200);
}
}
