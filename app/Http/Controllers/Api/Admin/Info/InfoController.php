<?php

namespace App\Http\Controllers\Api\Admin\Info;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\UpdateInfoRequest;
use App\Services\Admin\Info\InfoService as AdminInfoService;


class InfoController extends Controller
{
    public function __construct(protected AdminInfoService $infoService)
    {
    }

    /**
     * @OA\Post(
     *     path="/admin/infos/update",
     *     operationId="post-update-info",
     *     tags={"Admin", "Admin - Info"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Update Site Information",
     *     description="Update various site information including text fields and file uploads.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Site information data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateInfoRequest"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated site information.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Site information updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function update(UpdateInfoRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $this->infoService->update($request);
            DB::commit();
            Cache::flush();
            return success($data);

        } catch (\Throwable $th) {
            DB::rollBack();
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }


}
