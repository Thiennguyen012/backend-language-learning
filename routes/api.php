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
use App\Http\Controllers\Api\QuestionType\QuestionTypeController;
use App\Http\Controllers\Api\WordType\WordTypeController;
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
            Route::match(['put', 'post'], '/profile', [AuthController::class, 'updateProfile']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-attempts', [UserTestAttemptController::class, 'myAttempts'])->middleware('permission:attempt.history');
        Route::get('/word-types', [WordTypeController::class, 'index']);
        Route::get('/question-types', [QuestionTypeController::class, 'index']);

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware('permission:user.view');
            Route::post('/', [UserController::class, 'store'])->middleware('permission:user.create');
            Route::get('/{id}/test-attempts', [UserTestAttemptController::class, 'historyByUser'])->middleware('permission:user_test_attempt.view');
            Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:user.view');
            Route::match(['put', 'post'], '/{id}', [UserController::class, 'update'])->middleware('permission:user.update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('permission:user.delete');
        });

        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->middleware('permission:role.view');
            Route::post('/', [RoleController::class, 'store'])->middleware('permission:role.create');
            Route::get('/{role}', [RoleController::class, 'show'])->middleware('permission:role.view');
            Route::put('/{role}', [RoleController::class, 'update'])->middleware('permission:role.update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('permission:role.delete');
        });

        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->middleware('permission:permission.view');
            Route::post('/', [PermissionController::class, 'store'])->middleware('permission:permission.create');
            Route::get('/{permission}', [PermissionController::class, 'show'])->middleware('permission:permission.view');
            Route::put('/{permission}', [PermissionController::class, 'update'])->middleware('permission:permission.update');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->middleware('permission:permission.delete');
        });

        Route::prefix('flashcards')->group(function () {
            Route::get('/', [FlashcardController::class, 'index'])->middleware('permission:flashcard.view');
            Route::post('/', [FlashcardController::class, 'store'])->middleware('permission:flashcard.create');
            Route::get('/{id}', [FlashcardController::class, 'show'])->middleware('permission:flashcard.view');
            Route::put('/{id}', [FlashcardController::class, 'update'])->middleware('permission:flashcard.update');
            Route::delete('/{id}', [FlashcardController::class, 'destroy'])->middleware('permission:flashcard.delete');
        });

        Route::prefix('flashcard-collections')->group(function () {
            Route::get('/', [FlashcardCollectionController::class, 'index'])->middleware('permission:flashcard_collection.view');
            Route::post('/', [FlashcardCollectionController::class, 'store'])->middleware('permission:flashcard_collection.create');
            Route::get('/{id}', [FlashcardCollectionController::class, 'show'])->middleware('permission:flashcard_collection.view');
            Route::put('/{id}', [FlashcardCollectionController::class, 'update'])->middleware('permission:flashcard_collection.update');
            Route::delete('/{id}', [FlashcardCollectionController::class, 'destroy'])->middleware('permission:flashcard_collection.delete');
            Route::post('/{id}/attach', [FlashcardCollectionController::class, 'attach'])->middleware('permission:flashcard_collection.update');
            Route::post('/{id}/detach', [FlashcardCollectionController::class, 'detach'])->middleware('permission:flashcard_collection.update');
        });

        Route::prefix('collection-tests')->group(function () {
            Route::get('/', [CollectionTestController::class, 'index'])->middleware('permission:collection_test.view');
            Route::post('/', [CollectionTestController::class, 'store'])->middleware('permission:collection_test.create');
            Route::get('/{id}', [CollectionTestController::class, 'show'])->middleware('permission:collection_test.view');
            Route::put('/{id}', [CollectionTestController::class, 'update'])->middleware('permission:collection_test.update');
            Route::delete('/{id}', [CollectionTestController::class, 'destroy'])->middleware('permission:collection_test.delete');
        });

        Route::post('/tests/{id}/start', [UserTestAttemptController::class, 'start'])->middleware('permission:attempt.do');

        Route::prefix('user-test-attempts')->group(function () {
            Route::get('/', [UserTestAttemptController::class, 'index'])->middleware('permission:user_test_attempt.view');
            Route::post('/', [UserTestAttemptController::class, 'store'])->middleware('permission:user_test_attempt.create');
            Route::get('/{id}', [UserTestAttemptController::class, 'show'])->middleware('permission:user_test_attempt.view');
            Route::put('/{id}', [UserTestAttemptController::class, 'update'])->middleware('permission:user_test_attempt.update');
            Route::delete('/{id}', [UserTestAttemptController::class, 'destroy'])->middleware('permission:user_test_attempt.delete');
        });

        Route::get('/attempts/{id}', [UserTestAttemptController::class, 'remaining'])->middleware('permission:attempt.view');
        Route::get('/attempts/{id}/questions', [UserTestAttemptController::class, 'questions'])->middleware('permission:attempt.view');
        Route::post('/attempts/{id}/answers', [UserTestAttemptController::class, 'answer'])->middleware('permission:attempt.do');
        Route::post('/attempts/{id}/submit', [UserTestAttemptController::class, 'submit'])->middleware('permission:attempt.do');

        Route::prefix('user-test-answers')->group(function () {
            Route::get('/', [UserTestAnswerController::class, 'index'])->middleware('permission:user_test_answer.view');
            Route::post('/', [UserTestAnswerController::class, 'store'])->middleware('permission:user_test_answer.create');
            Route::get('/{id}', [UserTestAnswerController::class, 'show'])->middleware('permission:user_test_answer.view');
            Route::put('/{id}', [UserTestAnswerController::class, 'update'])->middleware('permission:user_test_answer.update');
            Route::delete('/{id}', [UserTestAnswerController::class, 'destroy'])->middleware('permission:user_test_answer.delete');
        });

        Route::prefix('test-types')->group(function () {
            Route::get('/', [TestTypeController::class, 'index'])->middleware('permission:test_type.view');
            Route::post('/', [TestTypeController::class, 'store'])->middleware('permission:test_type.create');
            Route::get('/{id}', [TestTypeController::class, 'show'])->middleware('permission:test_type.view');
            Route::put('/{id}', [TestTypeController::class, 'update'])->middleware('permission:test_type.update');
            Route::delete('/{id}', [TestTypeController::class, 'destroy'])->middleware('permission:test_type.delete');
        });

        Route::prefix('questions')->group(function () {
            Route::get('/', [QuestionController::class, 'index'])->middleware('permission:question.view');
            Route::post('/', [QuestionController::class, 'store'])->middleware('permission:question.create');
            Route::get('/{id}', [QuestionController::class, 'show'])->middleware('permission:question.view');
            Route::put('/{id}', [QuestionController::class, 'update'])->middleware('permission:question.update');
            Route::delete('/{id}', [QuestionController::class, 'destroy'])->middleware('permission:question.delete');
        });
    });
});
