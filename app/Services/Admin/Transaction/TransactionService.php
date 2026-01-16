<?php

namespace App\Services\Admin\Transaction;
use App\Constants\Notifications;
use App\Http\Requests\Api\Admin\Transaction\UpdateStatusRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Services\General\Notification\NotificationService;
use App\Traits\SearchTrait;
use Illuminate\Http\Request;

class TransactionService
{
    public function __construct(protected NotificationService $notificationService)
    {
    }
    use SearchTrait;
    public function index(Request $request)
    {
        $transactions = Transaction::with('user.wallet')->orderByDesc('created_at');
        $this->applySearchAndSort($transactions, $request, Transaction::$searchable);
        $transactions = $request->limited ?
            $transactions->take(10)->get() :
            $transactions->paginate(config('app.pagination_limit'));
        return TransactionResource::collection($transactions);
    }
    public function show(Transaction $transaction)
    {
        return TransactionResource::make($transaction->load('user.wallet'));
    }
    public function updateStatus(Transaction $transaction, UpdateStatusRequest $request)
    {
        $data = $request->validated();
        $toWallet = Wallet::where('id', $transaction->to_wallet_id)->first();
        $fromWallet = Wallet::where('id', $transaction->from_wallet_id)->first();
        $title = "جار معالجة معاملتك";

        if ($fromWallet && $fromWallet->key === 'cashBank360' && $toWallet && $toWallet->key === 'cashBank360') {
            throw new \Exception(__('messages.transaction_not_changable'), 422);
        }

        if ($data['status'] == 'completed') {
            $title = "تمت معالجة عملية التحويل بنجاح";
            if ($transaction->status == 'processing' || $transaction->status == 'cancelled' || $transaction->status == 'pending') {
                if ($toWallet && $toWallet->key === 'cashBank360') {
                    $recipientWallet = UserWallet::where('number', $transaction->to_wallet_number)->first();
                    if ($recipientWallet) {
                        $recipientWallet->logs()->create([
                            'transaction_id' => $transaction->id,
                            'amount' => $data['amount_confirmed'],
                            'type' => 'deposit',
                        ]);
                    }
                    $user = $recipientWallet->user;
                    if ($user)
                        $this->sendNotifications($user, $transaction);
                }
            }
            if ($transaction->status == 'cancelled') {
                if ($fromWallet && $fromWallet->key === 'cashBank360') {
                    $userWallet = UserWallet::where('number', $transaction->from_wallet_number)->first();
                    if ($userWallet && $userWallet->amount >= $transaction->amount_sent) {
                        $userWallet->logs()->create([
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->amount_sent,
                            'type' => 'Withdrawal',
                        ]);
                        $user = $userWallet->user;
                        if ($user)
                            $this->sendNotifications($user, $transaction);
                    } else {
                        throw new \Exception(__('messages.no_amount'), 422);
                    }
                }
            }
        } elseif ($data['status'] === 'cancelled') {
            $title = "لم يتم التحويل";
            if ($transaction->status == 'processing' || $transaction->status == 'completed' || $transaction->status == 'pending') {
                if ($fromWallet && $fromWallet->key === 'cashBank360') {
                    $userWallet = UserWallet::where('number', $transaction->from_wallet_number)->first();
                    if ($userWallet) {
                        $userWallet->logs()->create([
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->amount_sent,
                            'type' => 'deposit',
                        ]);
                    }
                    $user = $userWallet->user;
                    if ($user)
                        $this->sendNotifications($user, $transaction);
                }
            }
            if ($transaction->status == 'completed') {
                if ($toWallet && $toWallet->key === 'cashBank360') {
                    $recipientWallet = UserWallet::where('number', $transaction->to_wallet_number)->first();
                    if ($recipientWallet && $recipientWallet->amount >= $transaction->amount_confirmed) {
                        $recipientWallet->logs()->create([
                            'transaction_id' => $transaction->id,
                            'amount' => $transaction->amount_confirmed,
                            'type' => 'Withdrawal',
                        ]);
                        $user = $recipientWallet->user;
                        if ($user)
                            $this->sendNotifications($user, $transaction);
                    } else {
                        throw new \Exception(__('messages.no_amount'), 422);
                    }
                }
            }
        }


        $transaction->update($data);
        $user = User::where('id', $transaction->user_id)->first();
        if ($user) {
            $userFcmTokens = $user->fcmTokens()->pluck('fcm_token');
            foreach ($userFcmTokens as $fcmToken) {
                pushFirebaseNotification($fcmToken, $title, "", [
                    'transaction_id' => strval($transaction->id),
                    'type' => Notifications::NEW_TRANSFORM['TYPE'],
                    'state' => Notifications::NEW_TRANSFORM['STATE'],
                    'icon' => 'notification_icon',
                    'sound' => 'notification_sound'
                ]);
            }
            $this->notificationService->pushNotification($title, "", Notifications::NEW_TRANSFORM['TYPE'], Notifications::NEW_TRANSFORM['STATE'], $user, class_basename($transaction), $transaction->id);

        }

        return TransactionResource::make($transaction->load('user.wallet'));
    }
    protected function sendNotifications($user, $transaction)
    {
        $userFcmTokens = $user->fcmTokens()->pluck('fcm_token');
        foreach ($userFcmTokens as $fcmToken) {
            pushFirebaseNotification($fcmToken, "عملية تحويل جديدة", "", [
                'transaction_id' => strval($transaction->id),
                'type' => Notifications::NEW_TRANSFORM['TYPE'],
                'state' => Notifications::NEW_TRANSFORM['STATE'],
                'icon' => 'notification_icon',
                'sound' => 'notification_sound'
            ]);
        }
        $this->notificationService->pushNotification("عملية تحويل جديدة", "", Notifications::NEW_TRANSFORM['TYPE'], Notifications::NEW_TRANSFORM['STATE'], $user, class_basename($transaction), $transaction->id);
    }
}
