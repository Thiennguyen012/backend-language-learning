<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Permission\PermissionController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Order\OrderController;
use Illuminate\Support\Facades\Route;

// Test CORS endpoint
Route::get('/test-cors', function () {
    return response()->json([
        'message' => 'CORS is working!',
        'origin' => request()->header('Origin'),
        'time' => now()->toDateTimeString(),
    ]);
});

Route::prefix('admin')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
        });
    });

    Route::middleware('auth:sanctum', 'super_admin')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{role}', [RoleController::class, 'show']);
            Route::put('/{role}', [RoleController::class, 'update']);
            Route::delete('/{role}', [RoleController::class, 'destroy']);
        });

        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::post('/', [PermissionController::class, 'store']);
            Route::get('/{permission}', [PermissionController::class, 'show']);
            Route::put('/{permission}', [PermissionController::class, 'update']);
            Route::delete('/{permission}', [PermissionController::class, 'destroy']);
        });
    });

    Route::prefix('orders')->group(function () {
        Route::post('/decrypt', [OrderController::class, 'decryptPayload']);

        // Authenticated routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/my-orders', [OrderController::class, 'myOrders']);
            Route::post('/', [OrderController::class, 'store']);
            Route::put('/{id}', [OrderController::class, 'update']); // User can update their own orders, Super Admin can update any

            Route::middleware('super_admin')->group(function () {
                Route::get('/', [OrderController::class, 'index']);
                Route::patch('/{id}/status', [OrderController::class, 'updateStatus']); // Update status only (Super Admin)
                Route::delete('/{id}', [OrderController::class, 'destroy']);
            });
        });

        // Public routes - no auth needed
        Route::get('/{id}', [OrderController::class, 'show'])->whereNumber('id');
    });
});
