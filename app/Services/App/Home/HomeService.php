<?php

namespace App\Services\App\Home;

use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserWalletResource;

class HomeService
{
    public function __construct( )
    {

    }
    public function index()
    {
        $user = auth('sanctum')->user();
        $wallet = $user->wallet;
        $transactions = $user->transactions()->orderByDesc('created_at')->take(10)->get();
        return [
            'wallet' => UserWalletResource::make($wallet),
            'transactions' => TransactionResource::collection($transactions),
        ];
    }
}
