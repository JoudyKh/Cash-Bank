<?php

namespace App\Services\Admin\Wallet;
use App\Http\Requests\Api\Admin\Wallet\UpdateWalletRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;

class WalletService
{
    public function __construct()
    {
    }
    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        $data = $request->validated();
        if($request->hasFile('icon')){
            if (Storage::exists("public/$wallet->icon")) {
                Storage::delete("public/$wallet->icon");
            }
            $data['icon'] = $data['icon']->storePublicly('wallets/icons', 'public');
        }
        $wallet->update($data);
        return WalletResource::make($wallet);
    }

}
