<?php

namespace App\Services\General\User;

use App\Constants\Notifications;
use App\Models\AuthCode;
use App\Models\Info;
use App\Models\Transaction;
use App\Models\User;
use App\Constants\Constants;
use App\Models\UserFcmToken;
use App\Models\Wallet;
use App\Services\General\Notification\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class UserService
{
    public function __construct(protected NotificationService $notificationService)
    {
    }
    public function createUser(FormRequest $request): User
    {
        $data = $request->validated();
        if (
            !AuthCode::where('email', $data['email'])
                ->where('code', $data['verification_code'])
                ->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))->exists()
        )
            throw new \Exception(__('messages.have_to_reverify'), 422);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->assignRole(Constants::USER_ROLE);
        $user->wallet()->create();
        $user['token'] = $this->generateUserToken($user);
        if ($request->fcm_token) {
            $this->handleFcmToken($user, $request->fcm_token);
        }
        if ($request->referral_code) {
            $owner = User::where('referral_code', $request->referral_code)->first();
            if ($owner) {
                $amount = Info::where('super_key', 'transformation')->where('key', 'referral_amount')->pluck('value')->first();
                $systemWallet = Wallet::where('key', 'cashBank360')->first();

                $wallet = $owner->wallet;
                $array = [
                    'status' => 'completed',
                    'amount_sent' => $amount,
                    'amount_received' => $amount,
                    'amount_confirmed' => $amount,
                    'from_wallet_id' => $systemWallet->id,
                    'from_wallet_number' => $systemWallet->number,
                    'to_wallet_id' => $systemWallet->id,
                    'to_wallet_number' => $wallet->number,
                    'note' => 'using referral code',
                ];
                $transaction = $user->transactions()->create($array);
                $wallet->logs()->create([
                    'transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'type' => 'deposit',
                ]);
                $userFcmTokens = $owner->fcmTokens()->pluck('fcm_token');
                foreach ($userFcmTokens as $fcmToken) {
                    pushFirebaseNotification($fcmToken, "عملية تحويل جديدة", "", [
                        'transaction_id' => strval($transaction->id),
                        'type' => Notifications::NEW_TRANSFORM['TYPE'],
                        'state' => Notifications::NEW_TRANSFORM['STATE'],
                        'icon' => 'notification_icon',
                        'sound' => 'notification_sound'
                    ]);
                }
                $this->notificationService->pushNotification("عملية تحويل جديدة", "", Notifications::NEW_TRANSFORM['TYPE'], Notifications::NEW_TRANSFORM['STATE'], $owner, class_basename($transaction), $transaction->id);
                $this->notificationService->pushAdminsNotifications(Notifications::NEW_TRANSACTION, $transaction);
            }

        }
        return $user;
    }

    public function handleUserImage(?User $user, FormRequest $request): void
    {
        if ($request->hasFile('image')) {
            $user->images()->updateOrCreate(
                ['user_id' => $user->id], // Search criteria
                ['image' => $request->file('image')->storePublicly('users/images', 'public')] // Values to update or create
            );
        } elseif ($request->has('image') && $request->image === null) {
            $image = $user->images()->first();
            if ($image && Storage::exists('public/' . $image->image)) {
                Storage::delete('public/' . $image->image);
            }
            $user->images()->delete();
        }
    }
    protected function generateUserToken(User $user): string
    {
        return $user->createToken('auth')->plainTextToken;
    }

    public function handleFcmToken($user, $fcmToken)
    {
        $existingFcmToken = UserFcmToken::where('fcm_token', $fcmToken)->first();
        if ($existingFcmToken) {
            return $existingFcmToken->update([
                'token_id' => $user->tokens()->orderBy('id', 'DESC')->first()->id,
                'user_id' => $user->id,
            ]);
        }
        return $user->fcmTokens()->firstOrCreate(
            [
                'fcm_token' => $fcmToken,
                'token_id' => $user->tokens()->orderBy('id', 'DESC')->first()->id
            ]
        );
    }
}
