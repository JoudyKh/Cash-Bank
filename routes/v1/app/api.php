<?php

use App\Http\Controllers\Api\App\Home\HomeController;
use App\Http\Controllers\Api\App\Transaction\TransactionController as AppTransactionController;
use App\Http\Controllers\Api\App\UserWallet\UserWalletController;
use App\Http\Controllers\Api\General\Info\InfoController;
use App\Http\Controllers\Api\General\Notification\NotificationController;
use App\Http\Controllers\Api\General\Wallet\WalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\General\Auth\AuthController;
use App\Http\Controllers\Api\App\Auth\AuthController as AppAuthController;

/** @Auth */
Route::post('login', [AuthController::class, 'login'])->name('user.login');//
Route::post('register', [AppAuthController::class, 'register'])->name('user.register');//
Route::post('reset-password', [AuthController::class, 'resetPassword']);//
Route::post('send/verification-code', [AuthController::class, 'sendVerificationCode']);//
Route::post('check/verification-code', [AuthController::class, 'checkVerificationCode']);//


Route::group(['middleware' => ['auth:api', 'last.active']], function () {
    /** @Auth */
    Route::post('logout', [AuthController::class, 'logout']);//
    Route::get('/check/auth', [AuthController::class, 'authCheck']);//
    Route::get('profile', [AuthController::class, 'profile']);//
    Route::put('change-password', [AuthController::class, 'changePassword']);//
    Route::put('profile/update', [AuthController::class, 'updateProfile']);//
    Route::post('change-email', [AuthController::class, 'changeEmail']);//


    Route::prefix('/wallets')->group(function(){
        Route::get('/', [WalletController::class, 'index']);
        Route::get('/{wallet}', [WalletController::class, 'show']);
        Route::get('/logs/show', [UserWalletController::class, 'indexWalletLog']);
    });
    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::prefix('/transactions')->group(function(){
        Route::post('/get-amount-received', [AppTransactionController::class, 'calcAmountReceived']);
        Route::post('/get-amount-sent', [AppTransactionController::class, 'calcAmountSent']);
        Route::get('/', [AppTransactionController::class, 'index']);
        Route::post('/', [AppTransactionController::class, 'store']);
        Route::get('/{transaction}', [AppTransactionController::class, 'show']);
        Route::post('/{transaction}/confirm-transform', [AppTransactionController::class, 'confirmTransformation']);
        Route::post('/{transaction}/cancel-transform', [AppTransactionController::class, 'cancelTransaction']);
    });
    Route::get('/home', [HomeController::class, 'index']);
});

Route::get('/infos', [InfoController::class, 'index']);//