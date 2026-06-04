<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Permission\PermissionController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Flashcard\FlashcardController;
use App\Http\Controllers\Api\FlashcardCollection\FlashcardCollectionController;
use App\Http\Controllers\Api\CollectionTest\CollectionTestController;
use App\Http\Controllers\Api\UserTestAttempt\UserTestAttemptController;
use App\Http\Controllers\Api\UserTestAnswer\UserTestAnswerController;
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
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-device', [AuthController::class, 'logoutFromDevice']);
            Route::post('/logout-all', [AuthController::class, 'logoutFromAllDevices']);
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
            Route::get('/', [RoleController::class, 'index'])->middleware('permission:view_roles');
            Route::post('/', [RoleController::class, 'store'])->middleware('permission:manage_roles');
            Route::get('/{role}', [RoleController::class, 'show'])->middleware('permission:view_roles');
            Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:manage_roles');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:manage_roles');
        });

        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->middleware('permission:view_permissions');
            Route::post('/', [PermissionController::class, 'store'])->middleware('permission:manage_permissions');
            Route::get('/{permission}', [PermissionController::class, 'show'])->middleware('permission:view_permissions');
            Route::put('/{permission}', [PermissionController::class, 'update'])->middleware('permission:manage_permissions');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:manage_permissions');
        });

        Route::prefix('flashcards')->middleware('permission:manage_flashcards')->group(function () {
            Route::post('/', [FlashcardController::class, 'store']);
            Route::get('/{id}', [FlashcardController::class, 'show']);
            Route::put('/{id}', [FlashcardController::class, 'update']);
            Route::delete('/{id}', [FlashcardController::class, 'destroy']);
        });

        Route::prefix('flashcard-collections')->middleware('permission:manage_flashcards')->group(function () {
            Route::get('/', [FlashcardCollectionController::class, 'index']);
            Route::post('/', [FlashcardCollectionController::class, 'store']);
            Route::get('/{id}', [FlashcardCollectionController::class, 'show']);
            Route::put('/{id}', [FlashcardCollectionController::class, 'update']);
            Route::delete('/{id}', [FlashcardCollectionController::class, 'destroy']);
            Route::post('/{id}/attach', [FlashcardCollectionController::class, 'attach']);
            Route::post('/{id}/detach', [FlashcardCollectionController::class, 'detach']);
        });

        Route::prefix('collection-tests')->middleware('permission:manage_tests')->group(function () {
            Route::get('/', [CollectionTestController::class, 'index']);
            Route::post('/', [CollectionTestController::class, 'store']);
            Route::get('/{id}', [CollectionTestController::class, 'show']);
            Route::put('/{id}', [CollectionTestController::class, 'update']);
            Route::delete('/{id}', [CollectionTestController::class, 'destroy']);
        });

        Route::post('/tests/{id}/start', [UserTestAttemptController::class, 'start']);

        Route::prefix('user-test-attempts')->group(function () {
            Route::get('/', [UserTestAttemptController::class, 'index']);
            Route::post('/', [UserTestAttemptController::class, 'store']);
            Route::get('/{id}', [UserTestAttemptController::class, 'show']);
            Route::put('/{id}', [UserTestAttemptController::class, 'update']);
            Route::delete('/{id}', [UserTestAttemptController::class, 'destroy']);
        });

        Route::get('/attempts/{id}', [UserTestAttemptController::class, 'remaining']);
        Route::get('/attempts/{id}/questions', [UserTestAttemptController::class, 'questions']);
        Route::post('/attempts/{id}/answers', [UserTestAttemptController::class, 'answer']);
        Route::post('/attempts/{id}/submit', [UserTestAttemptController::class, 'submit']);

        Route::prefix('user-test-answers')->group(function () {
            Route::get('/', [UserTestAnswerController::class, 'index']);
            Route::post('/', [UserTestAnswerController::class, 'store']);
            Route::get('/{id}', [UserTestAnswerController::class, 'show']);
            Route::put('/{id}', [UserTestAnswerController::class, 'update']);
            Route::delete('/{id}', [UserTestAnswerController::class, 'destroy']);
        });

        Route::prefix('test-types')->middleware('permission:manage_tests')->group(function () {
            Route::get('/', [TestTypeController::class, 'index']);
            Route::post('/', [TestTypeController::class, 'store']);
            Route::get('/{id}', [TestTypeController::class, 'show']);
            Route::put('/{id}', [TestTypeController::class, 'update']);
            Route::delete('/{id}', [TestTypeController::class, 'destroy']);
        });

        Route::prefix('questions')->middleware('permission:manage_questions')->group(function () {
            Route::get('/', [QuestionController::class, 'index']);
            Route::post('/', [QuestionController::class, 'store']);
            Route::get('/{id}', [QuestionController::class, 'show']);
            Route::put('/{id}', [QuestionController::class, 'update']);
            Route::delete('/{id}', [QuestionController::class, 'destroy']);
        });
    });
});
