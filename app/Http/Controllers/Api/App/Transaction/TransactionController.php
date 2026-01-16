<?php

namespace App\Http\Controllers\Api\App\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\Transaction\CreateTransactionRequest;
use App\Models\Transaction;
use App\Services\App\Transaction\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {}
          /**
     * @OA\Get(
     *     path="/transactions",
     *     tags={"App" , "App - Transaction"},
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
     *         name="limited",
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
        try{
            return success($this->transactionService->index($request));
        }catch(\Exception $e){
            return error($e);
        }
    }
     /**
     * @OA\Get(
     *     path="/transactions/{transaction}",
     *     tags={"App" , "App - Transaction"},
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
     *     path="/transactions/get-amount-received",
     *     tags={"App" , "App - Transaction"},
     *     summary="get the amount received",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="amount_sent",
     *                 type="double",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function calcAmountReceived(Request $request)
    {
        try{
            return success($this->transactionService->calcAmountReceived($request));
        }catch(\Exception $e){
            return error($e);
        }
    }
      /**
     * @OA\Post(
     *     path="/transactions/get-amount-sent",
     *     tags={"App" , "App - Transaction"},
     *     summary="get the amount sent",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="amount_received",
     *                 type="double",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function calcAmountSent(Request $request)
    {
        try{
            return success($this->transactionService->calcAmountSent($request));
        }catch(\Exception $e){
            return error($e);
        }
    }
      /**
     * @OA\Post(
     *     path="/transactions",
     *     tags={"App" , "App - Transaction"},
     *     summary="store new transaction",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/CreateTransactionRequest") ,
     *         )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TransactionResource")
     *     )
     * )
     */
    public function store(CreateTransactionRequest $request)
    {
        DB::beginTransaction();

        try {
            DB::commit();
            Cache::flush();
            return success($this->transactionService->store($request), 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
        /**
     * @OA\Post(
     *     path="/transactions/{transaction}/confirm-transform",
     *     tags={"App" , "App - Transaction"},
     *     summary="confirm transformation",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *    @OA\Parameter(
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
    public function confirmTransformation(Transaction $transaction)
    {
        try {
            return success($this->transactionService->confirmTransformation($transaction));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }
    /**
 * @OA\Post(
 *     path="/transactions/{transaction}/cancel-transform",
 *     tags={"App" , "App - Transaction"},
 *     summary="cancel transformation",
 *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
 *    @OA\Parameter(
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
    public function cancelTransaction(Transaction $transaction)
    {
        try {
            return success($this->transactionService->cancelTransaction($transaction));
        } catch (\Throwable $th) {
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }

    }
}
