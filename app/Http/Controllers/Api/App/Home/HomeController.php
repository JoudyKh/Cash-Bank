<?php

namespace App\Http\Controllers\Api\App\Home;

use App\Http\Controllers\Controller;
use App\Services\App\Home\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected HomeService $homeService)
    {
    }
    /**
     * @OA\Get(
     *     path="/home",
     *     tags={"App" , "App - Home"},
     *     summary="",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function index()
    {
        try {
            return success($this->homeService->index());
        } catch (\Exception $e) {
            return error($e->getMessage(), [$e->getMessage()], $e->getCode());
        }
    }
}
