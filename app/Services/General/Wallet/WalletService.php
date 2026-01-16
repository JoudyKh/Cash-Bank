<?php

namespace App\Services\General\Wallet;
use App\Constants\Constants;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletService
{
    public function __construct()
    {}
    public function index(Request $request)
    {
        $wallets = Wallet::orderByDesc($request->trash ? 'deleted_at' : 'created_at');
        if ($request->has('trash') && $request->input('trash') == 1 && Auth::user()?->hasRole(Constants::ADMIN_ROLE)) {
            $wallets->onlyTrashed();
        }
        $wallets = $wallets->get();
        return WalletResource::collection($wallets);
    }
    public function show(Wallet $wallet)
    {
        return WalletResource::make($wallet);
    }
}
