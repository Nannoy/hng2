<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});


Route::middleware(['auth:api'])->group(function(){
    Route::post('me', [AuthController::class, 'me']);
    Route::get('users/{id}', [AuthController::class, 'getUser']);
    Route::get('organisations', [AuthController::class, 'getUserOrganisations']);
    Route::get('organisations/{orgId}', [AuthController::class, 'getOrganisation']);
    Route::post('organisations', [OrganisationController::class, 'createOrganization']);
    Route::post('organisations/{orgId}/users', [OrganisationController::class, 'addUserToOrganization']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});

//'middleware' => 'api',
