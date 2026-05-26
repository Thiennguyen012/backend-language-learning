<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Permission\PermissionController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Flashcard\FlashcardController;
use App\Http\Controllers\Api\FlashcardCollection\FlashcardCollectionController;
use App\Http\Controllers\Api\CollectionTest\CollectionTestController;
use App\Http\Controllers\Api\UserTestAttempt\UserTestAttemptController;
use App\Http\Controllers\Api\TestType\TestTypeController;
use App\Http\Controllers\Api\Question\QuestionController;
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

        Route::prefix('flashcards')->group(function () {
            Route::post('/', [FlashcardController::class, 'store']);
            Route::get('/{id}', [FlashcardController::class, 'show']);
            Route::put('/{id}', [FlashcardController::class, 'update']);
            Route::delete('/{id}', [FlashcardController::class, 'destroy']);
        });

        Route::prefix('flashcard-collections')->group(function () {
            Route::get('/', [FlashcardCollectionController::class, 'index']);
            Route::post('/', [FlashcardCollectionController::class, 'store']);
            Route::get('/{id}', [FlashcardCollectionController::class, 'show']);
            Route::put('/{id}', [FlashcardCollectionController::class, 'update']);
            Route::delete('/{id}', [FlashcardCollectionController::class, 'destroy']);
            Route::post('/{id}/attach', [FlashcardCollectionController::class, 'attach']);
            Route::post('/{id}/detach', [FlashcardCollectionController::class, 'detach']);
        });

        Route::prefix('collection-tests')->group(function () {
            Route::get('/', [CollectionTestController::class, 'index']);
            Route::post('/', [CollectionTestController::class, 'store']);
            Route::get('/{id}', [CollectionTestController::class, 'show']);
            Route::put('/{id}', [CollectionTestController::class, 'update']);
            Route::delete('/{id}', [CollectionTestController::class, 'destroy']);
        });

        Route::prefix('user-test-attempts')->group(function () {
            Route::get('/', [UserTestAttemptController::class, 'index']);
            Route::post('/', [UserTestAttemptController::class, 'store']);
            Route::get('/{id}', [UserTestAttemptController::class, 'show']);
            Route::put('/{id}', [UserTestAttemptController::class, 'update']);
            Route::delete('/{id}', [UserTestAttemptController::class, 'destroy']);
        });

        Route::prefix('test-types')->group(function () {
            Route::get('/', [TestTypeController::class, 'index']);
            Route::post('/', [TestTypeController::class, 'store']);
            Route::get('/{id}', [TestTypeController::class, 'show']);
            Route::put('/{id}', [TestTypeController::class, 'update']);
            Route::delete('/{id}', [TestTypeController::class, 'destroy']);
        });

        Route::prefix('questions')->group(function () {
            Route::get('/', [QuestionController::class, 'index']);
            Route::post('/', [QuestionController::class, 'store']);
            Route::get('/{id}', [QuestionController::class, 'show']);
            Route::put('/{id}', [QuestionController::class, 'update']);
            Route::delete('/{id}', [QuestionController::class, 'destroy']);
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
