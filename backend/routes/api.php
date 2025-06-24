<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

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

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Rei do Óleo API is running',
        'timestamp' => now(),
        'version' => env('API_VERSION', 'v1')
    ]);
});

// Public Routes (não precisam de autenticação)
Route::prefix('v1')->group(function () {
    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

        // Email Verification
        Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [AuthController::class, 'sendVerification'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });
});

// Protected Routes (precisam de autenticação)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // Authentication Routes (Authenticated)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/me', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    // User Routes
    Route::apiResource('users', UserController::class);

    // Business Logic Routes (a serem implementadas)
    Route::prefix('products')->group(function () {
        // Products routes will be implemented here
        Route::get('/', function () {
            return response()->json(['message' => 'Products endpoint - To be implemented']);
        });
    });

    Route::prefix('orders')->group(function () {
        // Orders routes will be implemented here
        Route::get('/', function () {
            return response()->json(['message' => 'Orders endpoint - To be implemented']);
        });
    });

    Route::prefix('customers')->group(function () {
        // Customers routes will be implemented here
        Route::get('/', function () {
            return response()->json(['message' => 'Customers endpoint - To be implemented']);
        });
    });

    Route::prefix('reports')->group(function () {
        // Reports routes will be implemented here
        Route::get('/', function () {
            return response()->json(['message' => 'Reports endpoint - To be implemented']);
        });
    });
});

// Fallback Route
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Endpoint not found',
        'code' => 404
    ], 404);
});
