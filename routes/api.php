<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CycleController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/verify_email_conformity', [AuthController::class, 'verifyEmailConformity']);
    Route::post('/forgotten-password', [AuthController::class, 'forgottenPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'getUser']);
        Route::get('/email/make_confirmation', [AuthController::class, 'makeEmailConfirmation']);
        Route::post('/email/match_code', [AuthController::class, 'matchConfirmationCode']);
    });

    Route::prefix('budget')->group(function () {
        Route::get('/', [BudgetController::class, 'get']);
        Route::get('/balance', [BudgetController::class, 'getBalance']);
        Route::post('/set', [BudgetController::class, 'set']);
    });

    Route::prefix('cycle')->group(function () {
        Route::get('/current', [CycleController::class, 'get']);
        Route::post('/edit', [CycleController::class, 'edit']);
    });

    Route::prefix('category')->group(function () {
        Route::get('/currents', [CategoryController::class, 'getCurrents']);
        Route::delete('/delete/{id}', [CategoryController::class, 'delete']);
    });
});