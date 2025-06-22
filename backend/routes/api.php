<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\TransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('password', [AuthController::class, 'changePassword']);

    // Account routes
    Route::get('accounts', [AccountController::class, 'index']);
    Route::post('accounts', [AccountController::class, 'store']);
    Route::get('accounts/{account}', [AccountController::class, 'show']);
    Route::get('accounts/{account}/transactions', [AccountController::class, 'transactions']);
    
    // Account Types
    Route::get('account-types', [AccountController::class, 'accountTypes']);

    // Transactions
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::post('deposit', [TransactionController::class, 'deposit']);
});

// Тестовий маршрут для перевірки аутентифікації
Route::get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Authenticated successfully',
        'data' => [
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->full_name,
                'email' => $request->user()->email,
            ]
        ]
    ]);
}); 