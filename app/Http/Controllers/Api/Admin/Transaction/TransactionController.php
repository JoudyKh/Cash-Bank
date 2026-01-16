<?php

namespace App\Http\Controllers\Api\Admin\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Transaction\UpdateStatusRequest;
use App\Models\Transaction;
use App\Services\Admin\Transaction\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {
    }
    /**
     * @OA\Get(
     *     path="/admin/transactions",
     *     tags={"Admin" , "Admin - Transaction"},
     *     summary="get all transactions",
     *     security={{ "bearerAuth": {} }},
     *    @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="any"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="data base column name"
     *         )
     *     ),
     *    @OA\Parameter(
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
     *         @OA\JsonContent(ref="#/components/schemas/TransactionResource")
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            return success($this->transactionService->index($request));
        } catch (\Exception $e) {
            return error($e);
        }
    }
     /**
     * @OA\Get(
     *     path="/admin/transactions/{transaction}",
     *     tags={"Admin" , "Admin - Transaction"},
     *     summary="show one transaction",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="transaction",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TransactionResource")
     *     )
     * )
     */
    public function show(Transaction $transaction)
    {
        try{
            return success($this->transactionService->show($transaction));
        }catch(\Exception $e){
            return error($e);
        }
    }
    /**
     * @OA\Post(
     *     path="/admin/transactions/{transaction}",
     *     tags={"Admin" , "Admin - Transaction"},
     *     summary="store new transaction",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *    @OA\Parameter(
     *         name="transaction",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateStatusRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TransactionResource")
     *     )
     * )
     */
    public function updateStatus(Transaction $transaction, UpdateStatusRequest $request)
    {
        DB::beginTransaction();

        try {
            DB::commit();
            Cache::flush();
            return success($this->transactionService->updateStatus($transaction, $request), 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
}
