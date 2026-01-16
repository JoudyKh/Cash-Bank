<?php

namespace App\Http\Controllers\Api\App\UserWallet;

use App\Http\Controllers\Controller;
use App\Services\App\UserWallet\UserWalletService;
use Illuminate\Http\Request;

class UserWalletController extends Controller
{
    public function __construct(protected UserWalletService $userWalletService){}
      /**
     * @OA\Get(
     *     path="/wallets/logs/show",
     *     tags={"App" , "App - WalletLog"},
     *     summary="get wallet logs",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function indexWalletLog()
    {
        try{
            return success($this->userWalletService->indexWalletLog());
        }catch(\Exception $e){
            return error($e);
        }
    }
}
