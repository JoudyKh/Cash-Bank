<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Constants\Constants;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\User\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService){}
    /**
     * @OA\Get(
     *     path="/admin/users",
     *     tags={"Admin" , "Admin - Users"},
     *     summary="get all users",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *    @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    
    public function index(Request $request)
    {
        try{
            return success($this->userService->index($request));
        }catch(\Exception $e){
            return error($e->getMessage(), [$e->getMessage()], $e->getCode());
        }
    }
        /**
     * @OA\Delete(
     *     path="/admin/users/{user}",
     *     tags={"Admin" , "Admin - Users"},
     *     summary="SoftDelete an existing user",
     *      security={{ "bearerAuth": {}, "Accept": "json/application" }},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(User $user)
    {
        try{
            return success($this->userService->destroy($user));
        }catch(\Exception $e){
            return error($e->getMessage(), [$e->getMessage()], $e->getCode());
        }
    }
}
