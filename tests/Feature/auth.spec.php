<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Artisan;
use PhpParser\Token;
use App\Models\Organization;


class Auth_spec extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_cannot_see_data_from_organizations_they_dont_have_access_to()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an organization
        $organization = Organization::factory()->create();

        // Authenticate the user and get JWT token
        $token = JWTAuth::fromUser($user);

        // Attempt to retrieve organization data that the user is associated with
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->get('/api/organizations/' . $organization->orgId);

        $response->assertStatus(403); // Expecting Forbidden status code

        // Ensure the user cannot see the organization details
        $response->assertJson([
            'status' => 'Forbidden',
            'message' => 'You do not have access to this organization',
        ]);

        // Create another user who is associated with the organization
        $anotherUser = User::factory()->create();
        $organization->users()->attach($anotherUser->userId);

        // Authenticate the another user and get JWT token
        $anotherToken = JWTAuth::fromUser($anotherUser);

        // Attempt to retrieve organization data that the another user is associated with
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $anotherToken])
                         ->get('/api/organizations/' . $organization->orgId);

        $response->assertStatus(200); // Expecting success status code

        // Ensure the another user can see the organization details
        $response->assertJson([
            'status' => 'success',
            'message' => 'Organization retrieved successfully',
            'data' => [
                'orgId' => $organization->orgId,
                // Include other expected data fields here
            ]
        ]);
    }
}
