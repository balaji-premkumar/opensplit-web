<?php

use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupController;
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

