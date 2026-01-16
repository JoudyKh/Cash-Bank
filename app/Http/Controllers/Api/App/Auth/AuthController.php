<?php

namespace App\Http\Controllers\Api\App\Auth;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\App\Auth\AuthService;
use App\Http\Requests\Api\App\Auth\SignUpRequest;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *      path="/register",
     *      operationId="app/register",
     *      tags={"App", "App - Auth"},
     *      summary="register a new user",
     *      description="register a new user with the provided information",
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *              required={"username","phone_number", "email", "password", "verification_code"},
     *              @OA\Property(property="username", type="string", example="admin"),
     *              @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *              @OA\Property(property="password", type="string", example="password"),
     *              @OA\Property(property="phone_number", type="string", example="123456789"),
     *              @OA\Property(property="referral_code", type="string"),
     *              @OA\Property(property="verification_code", type="string", example="0123"),
     *          ),
     *     ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User created successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *          )
     *      ),
     * )
     */
    public function register(SignUpRequest $request): JsonResponse
    {
        try {
            $data = $this->authService->register($request);
            return success($data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return error($e->getMessage(), null, $e->getCode());
        }
    }
}
