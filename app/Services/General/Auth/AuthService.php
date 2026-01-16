<?php

namespace App\Services\General\Auth;

use App\Http\Requests\Api\General\Auth\ChangeEmailRequest;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AuthCode;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\General\User\UserService;
use App\Http\Requests\Api\General\Auth\LoginRequest;
use App\Services\General\Notification\NotificationService;
use App\Http\Requests\Api\General\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\General\Auth\UpdateProfileRequest;
use App\Http\Requests\Api\General\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\General\Auth\SendVerificationCodeRequest;
use App\Http\Requests\Api\General\Auth\CheckVerificationCodeRequest;

class AuthService
{
    protected ?User $user;
    protected NotificationService $notificationService;
    protected UserService $userService;

    public function __construct(NotificationService $notificationService, UserService $userService)
    {
        $this->notificationService = $notificationService;
        $this->userService = $userService;
        // @phpstan-ignore-next-line
        $this->user = auth('sanctum')->user();
    }

    public function getProfile(Request $request): JsonResponse
    {
        //if the user has notification
        // $notifications = $this->notificationService->getAllNotifications();
        //if wanted the updated the status of has_read to true then must pass read=1 param .
        // if ($request->read)
        //     $this->notificationService->readAllNotifications();
        return success(
            UserResource::make($this->user),
            200,
            [
                // 'notifications' => $notifications,
                //consider that the notifications comes from many types for example messages , new visit , new ad ..etc .
                // 'notifications_types_stats' => $this->notificationService->getNotificationTypeStatistics(0),
                // 'notifications_count' => $this->notificationService->getAllNotifications(0, true),
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function login(LoginRequest $request, ?bool $isAdmin = false): array
    {
        $fun = $isAdmin ? 'whereHas' : 'whereDoesntHave';
        // login by username or email
        $user = User::where(function ($q) use ($request) {
            $q->where('username', $request->username)
                ->orWhere('email', $request->username);
        })->$fun('roles', function ($q) {
                $q->where('name', Constants::ADMIN_ROLE);
            })
            ->first();
        if (!$user)
            throw new Exception(__('messages.username_is_not_correct'), 422);
        if (!Hash::check($request->password, $user->password))
            throw new Exception(__('messages.wrong_password'), 422);

        $token = $user->createToken('auth')->plainTextToken;
        if ($request->fcm_token) {
            $this->userService->handleFcmToken($user, $request->fcm_token);
        }
        $user['token'] = $token;
        return ['user' => new UserResource($user)];
    }

    function logout(): true
    {
        // @phpstan-ignore-next-line
        $this->user->tokens()->where('id', $this->user->currentAccessToken()->id)->delete();
        return true;
    }

    /**
     * @throws Exception
     */
    public function changePassword(ChangePasswordRequest $request): true
    {

        if (Hash::check($request->old_password, $this->user->password)) {
            $this->user->update(
                ['password' => Hash::make($request->password)]
            );
            return true;
        }
        throw new Exception(__('messages.wrong_old_password'), 422);
    }

    /**
     * @throws Exception
     */
    public function resetPassword(ResetPasswordRequest $request): true
    {
        $passwordResetCode = AuthCode::where('code', $request->verification_code)
            ->where('expired_at', '>', Carbon::now())
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhereHas('user', function ($userQuery) use ($request) {
                        $userQuery->where('email', $request->email);
                    });
            })
            ->first();
        if (!$passwordResetCode) {
            throw new Exception(__('messages.invalid_verification_code'), 422);
        }
        $passwordResetCode->delete();
        $user = User::where('email', $request->email)->first();
        $user->update(
            ['password' => Hash::make($request->password)]
        );
        return true;
    }

    public function checkVerificationCode(CheckVerificationCodeRequest $request): array
    {
        $passwordResetCode = AuthCode::where('code', $request->verification_code)
            ->where('expired_at', '>', Carbon::now())
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhereHas('user', function ($userQuery) use ($request) {
                        $userQuery->where('email', $request->email);
                    });
            })
            ->first();
        // this part for activation after signup if needed .
        if ($this->user) {
            if (!$passwordResetCode) {
                throw new Exception(__('messages.invalid_verification_code'), 422);
            }
            $passwordResetCode->delete();
            $this->user->update([/*'is_active' => 1,*/ 'email_verified_at' => Carbon::now()]);
            $response = [
                'message' => __('messages.your_account_has_been_activated'),
            ];
            return $response;
        }
        $response = [
            'code' => $request->verification_code,
            'is_valid' => $passwordResetCode ? true : false,
        ];
        return $response;
    }


    /**
     * @throws Exception
     * @return true
     */
    public function sendVerificationCode(SendVerificationCodeRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $authToVerfiy = [];
            // this part for activation after signup if needed .
            $user = User::withTrashed()->where('email', $request->email)->first();
            if ($request->type === 'resetPass') {
                if (!$user) {
                    throw new Exception(__('messages.email_not_found'), 422);
                } else {
                    if ($this->user && $this->user->is_active) {
                        throw new Exception(__('messages.you_have_already_activate_your_account'), 422);
                    }
                    $authToVerfiy['user_id'] = $user->id;
                    AuthCode::where('user_id', $user->id)->delete();

                }

            } elseif ($request->type === 'new') {
                if ($user)
                    throw new Exception(__('messages.invalid_email'), 422);
                $authToVerfiy['email'] = $request->email;
                AuthCode::where('email', $request->email)->delete();

            }
            $code = rand(1000, 9999);
            $details = [
                'title' => __('messages.your_verification_code_is'),
                'body' => $code,
            ];
            Mail::to($user ? $user->email : $request->email)->send(new \App\Mail\VerificationCode($details));
            AuthCode::create(array_merge([
                'code' => $code,
                'expired_at' => Carbon::now()->addMinutes(15)->format('Y-m-d H:i:s')
            ], $authToVerfiy));
            return true;

        });
    }
    public function changeEmail(ChangeEmailRequest $request)
    {
        $data = $request->validated();
        $passwordResetCode = AuthCode::where('code', $request->verification_code)
            ->where('expired_at', '>', Carbon::now())
            ->where('email', $request->email)
            ->first();
        if (!$passwordResetCode) {
            throw new Exception(__('messages.invalid_verification_code'), 422);
        }
        $passwordResetCode->delete();
        $this->user->update($data);
        return userResource::make($this->user);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {

        $data = $request->validated();
        if ($request->has('password')) {
            if (!Hash::check($request->old_password, $this->user->password)) {
                throw new Exception(__('messages.wrong_old_password'), 422);
            }
            $data['password'] = Hash::make($request->password);
        }
        $this->user->update($data);

        //if the user has notification
        $notifications = $this->notificationService->getAllNotifications();

        return success(
            UserResource::make(User::where('id', $this->user->id)->first()),
            200,
            [
                'notifications' => $notifications ?? null,
                //consider that the notifications comes from many types for example messages , new visit , new ad ..etc .
                'notifications_types_stats' => $this->notificationService?->getNotificationTypeStatistics(0) ?? [],
                'notifications_count' => $this->notificationService?->getAllNotifications(0, true) ?? [],
            ]
        );
    }

}
