<?php

namespace App\Http\Controllers\Api\Admin\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Wallet\UpdateWalletRequest;
use App\Models\Wallet;
use App\Services\Admin\Wallet\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {}
     /**
     * @OA\Post(
     *     path="/admin/wallets/{id}",
     *     tags={"Admin" , "Admin - Wallet"},
     *     summary="update an existing wallet",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", enum={"PUT"}, default="PUT")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateWalletRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/WalletResource")
     *     )
     * )
     */
    public function update(UpdateWalletRequest $request, Wallet $wallet)
    {
        try{
            return success($this->walletService->update($request, $wallet));
        }catch(\Exception $e){
            return error($e);
        }
    }
}
