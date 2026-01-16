<?php

use App\Constants\Constants;
use App\Http\Controllers\Api\Admin\User\UserController;
use App\Http\Controllers\Api\General\Notification\NotificationController;
use App\Http\Controllers\Api\Admin\Transaction\TransactionController as AdminTransactionController;
use App\Http\Controllers\Api\General\Wallet\WalletController;
use App\Http\Controllers\Api\Admin\Wallet\WalletController as AdminWalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\General\Info\InfoController;
use App\Http\Controllers\Api\General\Auth\AuthController;
use App\Http\Controllers\Api\Admin\Info\InfoController as AdminInfoController;

/** @Auth */
Route::post('login', [AuthController::class, 'login'])->name('admin.login');//
Route::post('reset-password', [AuthController::class, 'resetPassword']);//
Route::post('send/verification-code', [AuthController::class, 'sendVerificationCode']);//
Route::post('check/verification-code', [AuthController::class, 'checkVerificationCode']);//

Route::group(['middleware' => ['auth:api', 'last.active', 'ability:' . Constants::ADMIN_ROLE]], function () {

    /** @Auth */
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/check/auth', [AuthController::class, 'authCheck']);
    Route::get('profile', [AuthController::class, 'profile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);//
    Route::put('profile/update', [AuthController::class, 'updateProfile']);//
    Route::post('change-email', [AuthController::class, 'changeEmail']);//


    Route::prefix('infos')->group(function () {
        Route::get('/', [InfoController::class, 'index']);//
        Route::post('/update', [AdminInfoController::class, 'update']);//
    });

    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::prefix('/wallets')->group(function(){
        Route::get('/', [WalletController::class, 'index']);
        Route::get('/{wallet}', [WalletController::class, 'show']);
        Route::put('/{wallet}', [AdminWalletController::class, 'update']);
        // Route::delete('/{wallet}/{force?}', [AdminWalletController::class, 'destroy']);
        // Route::get('/{wallet}/restore', [AdminWalletController::class, 'restore']);
    });
    Route::prefix('/transactions')->group(function(){
        Route::get('/', [AdminTransactionController::class, 'index']);
        Route::get('/{transaction}', [AdminTransactionController::class, 'show']);
        Route::post('/{transaction}', [AdminTransactionController::class, 'updateStatus']);
    });
    Route::prefix('users')->group(function(){
        Route::get('/', [UserController::class, 'index']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
    });
});


