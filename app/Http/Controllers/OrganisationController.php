<?php

namespace App\Http\Controllers;
use App\Models\Organization;
use App\Http\Requests\CreateOrganizationRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AddUserToOrganizationRequest;
use App\Models\User;

class OrganisationController extends Controller
{
    public function createOrganization(CreateOrganizationRequest $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'User not authenticated',
                'statusCode' => 401
            ], 401);
        }

        $validatedData = $request->validated();

        $organization = Organization::create([
            'orgId' => Str::uuid()->toString(),
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? '',
        ]);

        $organization->users()->attach($user->userId);

        return response()->json([
            'status' => 'success',
            'message' => 'Organization created successfully',
            'data' => [
                'orgId' => $organization->orgId,
                'name' => $organization->name,
                'description' => $organization->description,
            ]
        ], 201);
    }

    public function addUserToOrganization(AddUserToOrganizationRequest $request, $orgId)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'User not authenticated',
                'statusCode' => 401
            ], 401);
        }

        $organization = Organization::find($orgId);

        if (!$organization) {
            return response()->json([
                'status' => 'Not Found',
                'message' => 'Organization not found',
                'statusCode' => 404
            ], 404);
        }

        $userId = $request->input('userId');
        $newUser = User::where('userId', $userId)->first();

        if (!$newUser) {
            return response()->json([
                'status' => 'Not Found',
                'message' => 'User not found',
                'statusCode' => 404
            ], 404);
        }

        $organization->users()->attach($newUser->userId);

        return response()->json([
            'status' => 'success',
            'message' => 'User added to organisation successfully',
        ], 200);
    }
}
