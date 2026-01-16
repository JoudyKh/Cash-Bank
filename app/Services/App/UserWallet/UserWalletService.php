<?php

namespace App\Services\App\UserWallet;
use App\Http\Resources\UserWalletLogResource;
use App\Models\User;

class UserWalletService
{
    protected ?User $user;
    public function __construct()
    {
        $this->user = auth('sanctum')->user();
    }
    public function indexWalletLog()
    {
        $wallet = $this->user->wallet;
        $logs = $wallet->logs()->with('transaction')->orderByDesc('created_at')->paginate(config('app.pagination_limit'));
        return UserWalletLogResource::collection($logs);
    }
}
