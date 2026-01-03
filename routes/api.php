<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ==========================================================================
// PUBLIC ROUTES (No authentication required)
// ==========================================================================

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Social authentication
    Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->where('provider', 'google|facebook|twitter');
    Route::get('/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->where('provider', 'google|facebook|twitter');
});

// ==========================================================================
// PROTECTED ROUTES (Authentication required)
// ==========================================================================

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes that require authentication
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });

    // Group routes (RESTful CRUD)
    Route::prefix('groups')->group(function () {
        Route::get('/', [GroupController::class, 'index']);
        Route::post('/', [GroupController::class, 'store']);
        Route::get('/{id}', [GroupController::class, 'show']);
        Route::put('/{id}', [GroupController::class, 'update']);
        Route::delete('/{id}', [GroupController::class, 'destroy']);
        
        // Group members
        Route::post('/{id}/members', [GroupController::class, 'addMembers']);
        Route::delete('/{id}/members/{userId}', [GroupController::class, 'removeMember']);
        
        // Group expenses
        Route::get('/{groupId}/expenses', [ExpenseController::class, 'byGroup']);
    });

    // Expense routes (RESTful CRUD)
    Route::prefix('expenses')->group(function () {
        Route::post('/', [ExpenseController::class, 'store']);
        Route::get('/{id}', [ExpenseController::class, 'show']);
        Route::delete('/{id}', [ExpenseController::class, 'destroy']);
    });
});
