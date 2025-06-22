<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;

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

// Публічні маршрути (без аутентифікації)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Захищені маршрути (потребують аутентифікації)
Route::middleware('auth:sanctum')->group(function () {
    
    // Аутентифікація
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });

    // Рахунки
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::post('/', [AccountController::class, 'store']);
        Route::get('/types', [AccountController::class, 'accountTypes']);
        Route::get('/{account}', [AccountController::class, 'show']);
        Route::get('/{account}/transactions', [AccountController::class, 'transactions']);
        Route::get('/{account}/balance', [AccountController::class, 'balance']);
        Route::post('/{account}/block', [AccountController::class, 'block']);
        Route::post('/{account}/unblock', [AccountController::class, 'unblock']);
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
}); 