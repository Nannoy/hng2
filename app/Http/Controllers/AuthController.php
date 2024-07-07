<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Organization;

class AuthController extends Controller
{

    public function register(UserRegisterRequest $request)
{
    $validatedData = $request->validated();

    $user = User::create([
        'userId' => Str::uuid()->toString(), // Use UUID
        'firstName' => $validatedData['firstName'],
        'lastName' => $validatedData['lastName'],
        'email' => $validatedData['email'],
        'password' => bcrypt($validatedData['password']),
        'phone' => $validatedData['phone'] ?? null,
    ]);

    $organization = $user->createOrganization();

    $token = auth('api')->login($user);

    return response()->json([
        'status' => 'success',
        'message' => 'Registration successful',
        'data' => [
            'accessToken' => $token,
            'user' => [
                'userId' => $user->userId,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            //'organization' => $organization,
        ]
    ], 201);
}

public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$token = auth('api')->attempt($credentials)) {
        return response()->json([
            "status"=> "Bad request",
            "message"=> "Authentication failed",
        ], 401);
    }

    $user = auth('api')->user();

    return response()->json([
        'status' => 'success',
        'message' => 'Login successful',
        'data' => [
            'accessToken' => $token,
            'user' => [
                'userId' => $user->userId,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]
    ]);
}

public function getUser($id)
{
    try {
        // Get the authenticated user
        $authenticatedUser = auth()->user();

        // Check if the authenticated user is requesting their own record
        if ($authenticatedUser->userId == $id) {
            $user = $authenticatedUser;
        } else {
            // Otherwise, find the user by ID in the organizations they belong to or created
            $user = User::where('userId', $id)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'Bad request',
                    'message' => 'User not found',
                    'statusCode' => 404
                ], 404);
            }

            // Additional logic to check if the authenticated user belongs to or created the organization
            $belongsToOrganization = Organization::where('orgId', $authenticatedUser->organization_id)
                ->whereHas('users', function($query) use ($id) {
                    $query->where('userId', $id);
                })->exists();

            if (!$belongsToOrganization) {
                return response()->json([
                    'status' => 'Forbidden',
                    'message' => 'You do not have permission to access this user',
                    'statusCode' => 403
                ], 403);
            }
        }

        // Return the user's data
        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => [
                'userId' => $user->userId,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Bad request',
            'message' => 'An error occurred',
            'statusCode' => 400
        ], 400);
    }
}


public function getUserOrganisations(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => 'Unauthorized',
            'message' => 'User not authenticated',
            'statusCode' => 401
        ], 401);
    }

    $organizations = $user->organizations()->get();

    return response()->json([
        'status' => 'success',
        'message' => 'Organizations retrieved successfully',
        'data' => [
            'organisations' => $organizations
        ]
    ], 200);
}

public function getOrganisation(Request $request, $orgId)
{
    // Authenticate the user
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'status' => 'Unauthorized',
            'message' => 'User not authenticated',
            'statusCode' => 401
        ], 401);
    }

    // Find the organization
    $organization = Organization::find($orgId);

    if (!$organization) {
        return response()->json([
            'status' => 'Not Found',
            'message' => 'Organization not found',
            'statusCode' => 404
        ], 404);
    }

    // Check if the user belongs to the organization
    if (!$organization->users()->where('userId', $user->userId)->exists()) {
        return response()->json([
            'status' => 'Forbidden',
            'message' => 'You do not have access to this organization',
            'statusCode' => 403
        ], 403);
    }

    // Return the organization details
    return response()->json([
        'status' => 'success',
        'message' => 'Organization retrieved successfully',
        'data' => [
            'orgId' => $organization->orgId,
            'name' => $organization->name,
            'description' => $organization->description,
        ]
    ], 200);
}


    public function me()
    {
        return response()->json(auth()->user());
    }


    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}