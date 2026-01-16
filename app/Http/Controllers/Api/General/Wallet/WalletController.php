<?php

namespace App\Http\Controllers\Api\General\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Services\General\Wallet\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }
    /**
     * @OA\Get(
     *     path="/wallets",
     *     tags={"App" , "App - Wallet"},
     *     summary="get all wallets",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/WalletResource")
     *     )
     * )
     * @OA\Get(
     *     path="/admin/wallets",
     *     tags={"Admin" , "Admin - Wallet"},
     *     summary="get all wallets",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="trash",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/WalletResource")
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            return success($this->walletService->index($request));
        } catch (\Exception $e) {
            return error($e);
        }
    }
    /**
     * @OA\Get(
     *     path="/wallets/{id}",
     *     tags={"App" , "App - Wallet"},
     *     summary="show one wallet",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/WalletResource")
     *     )
     * )
     * @OA\Get(
     *     path="/admin/wallets/{id}",
     *     tags={"Admin" , "Admin - Wallet"},
     *     summary="show one wallet",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/WalletResource")
     *     )
     * )
     */
    public function show(Wallet $wallet)
    {
        try {
            return success($this->walletService->show($wallet));
        } catch (\Exception $e) {
            return error($e);
        }
    }
}
