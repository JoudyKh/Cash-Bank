<?php

namespace App\Http\Controllers\Api\General\Info;

use App\Models\Section;
use App\Http\Controllers\Controller;
use App\Http\Resources\SectionResource;
use App\Services\General\Info\InfoService as GeneralInfoService;


class InfoController extends Controller
{
    public function __construct(protected GeneralInfoService $infoService)
    {
    }

    /**
     * @OA\Get(
     *     path="/infos",
     *     tags={"App" , "App - Info"},
     *     summary="Get all info",
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *      security={{ "bearerAuth": {} }},
     *      @OA\Parameter(
     *         name="locale",
     *         in="header",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"en", "ar"}
     *         ),
     *         description="The locale of the response"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     * @OA\Get(
     *     path="/admin/infos",
     *     tags={"Admin" , "Admin - Info"},
     *     summary="Get all admin info",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function index()
    {
        return success($this->infoService->getAll());
    }


}
