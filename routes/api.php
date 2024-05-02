<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CycleController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/signup', 'signup');
    Route::post('/verify_email_conformity', 'verifyEmailConformity');
    Route::post('/forgotten-password', 'forgottenPassword');
    Route::post('/reset-password', 'resetPassword');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::get('/user', 'getUser');
        Route::get('/email/make_confirmation', 'makeEmailConfirmation');
        Route::post('/email/match_code', 'matchConfirmationCode');
    });

    Route::prefix('budget')->controller(BudgetController::class)->group(function () {
        Route::get('/', 'get');
        Route::get('/balance', 'getBalance');
        Route::post('/set', 'set');

        Route::prefix('/category')->group(function () {
            Route::get('/available', 'getAvailableCategoryBudget');
        });
    });

    Route::prefix('cycle')->controller(CycleController::class)->group(function () {
        Route::get('/current', 'get');
        Route::post('/edit', 'edit');
    });

    Route::prefix('category')->controller(CategoryController::class)->group(function () {
        Route::get('/currents', 'getCurrents');
        Route::delete('/delete/{id}', 'delete');
        Route::post('/create', 'add');
        Route::post('/edit', 'edit');
    });
});