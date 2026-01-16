<?php

namespace App\Services\App\Transaction;
use App\Constants\Constants;
use App\Constants\Notifications;
use App\Http\Requests\Api\App\Transaction\CreateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Info;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Services\General\Notification\NotificationService;
use App\Traits\SearchTrait;
use Illuminate\Http\Request;

class TransactionService
{
    protected ?User $user;
    public function __construct(protected NotificationService $notificationService)
    {
        $this->user = auth('sanctum')->user();
    }
    use SearchTrait;
    public function index(Request $request)
    {
        $transactions = $this->user->transactions();
        $this->applySearchAndSort($transactions, $request, Transaction::$searchable);
        $transactions = $transactions->with('user')->orderByDesc('created_at');
        $transactions = $request->limited ?
            $transactions->take(10)->get() :
            $transactions->paginate(config('app.pagination_limit'));
        return TransactionResource::collection($transactions);
    }
    public function show(Transaction $transaction)
    {
        $transaction = $this->user->transactions()->where('transactions.id', $transaction->id)->first();
        if (!$transaction)
            throw new \Exception(__('messages.invalid_transactionId'));
        return TransactionResource::make($transaction->load('user'));
    }
    public function calcAmountReceived(Request $request)
    {
        $amountSent = $request->input('amount_sent');
        $commissionRate = Info::where('super_key', 'transaction')->where('key', 'commission_rate')->pluck('value')->first();
        if ($commissionRate && $commissionRate > 0) {
            $commission = ($commissionRate / 100) * $amountSent;
            $amountReceived = $amountSent - $commission;

            return $amountReceived;
        }

        return $amountSent;
    }
    public function calcAmountSent(Request $request)
    {
        $amountReceived = $request->input('amount_received');  
        $commissionRate = Info::where('super_key', 'transaction')->where('key', 'commission_rate')->pluck('value')->first();  
    
        if ($commissionRate && $commissionRate > 0) {  
            $amountSent = round($amountReceived / (1 - ($commissionRate / 100)), 2);  
    
            return $amountSent;  
        }  
    
        return $amountReceived;  
    }
    public function store(CreateTransactionRequest $request)
    {
        $data = $request->validated();
        $data['status'] = 'pending';
        $fromWallet = Wallet::where('id', $data['from_wallet_id'])->first();
        $toWallet = Wallet::where('id', $data['to_wallet_id'])->first();

        if ($fromWallet && $fromWallet->key === 'cashBank360' && $toWallet && $toWallet->key === 'cashBank360') {
            $data['status'] = 'completed';
            $data['amount_confirmed'] = $data['amount_received'];
        }

        $transaction = $this->user->transactions()->create($data);
        if ($fromWallet && $fromWallet->key === 'cashBank360') {
            $userWallet = $this->user->wallet;
            if ($userWallet) {
                $userWallet->logs()->create([
                    'transaction_id' => $transaction->id,
                    'amount' => $data['amount_sent'],
                    'type' => 'Withdrawal',
                ]);
            }
        }
        if ($fromWallet && $fromWallet->key === 'cashBank360' && $toWallet && $toWallet->key === 'cashBank360') {
            $recipientWallet = UserWallet::where('number', $data['to_wallet_number'])->first();
            if ($recipientWallet) {
                $recipientWallet->logs()->create([
                    'transaction_id' => $transaction->id,
                    'amount' => $data['amount_received'],
                    'type' => 'deposit',
                ]);
            }
            $recipientUser = $recipientWallet->user;
            $userFcmTokens = $recipientUser->fcmTokens()->pluck('fcm_token');
            foreach ($userFcmTokens as $fcmToken) {
                pushFirebaseNotification($fcmToken, "عملية تحويل جديدة", "", [
                    'transaction_id' => strval($transaction->id),
                    'type' => Notifications::NEW_TRANSFORM['TYPE'],
                    'state' => Notifications::NEW_TRANSFORM['STATE'],
                    'icon' => 'notification_icon',
                    'sound' => 'notification_sound'
                ]);
            }
            $this->notificationService->pushNotification("عملية تحويل جديدة", "", Notifications::NEW_TRANSFORM['TYPE'], Notifications::NEW_TRANSFORM['STATE'], $recipientUser, class_basename($transaction), $transaction->id);
            $this->notificationService->pushAdminsNotifications(Notifications::NEW_TRANSACTION, $transaction);
        }

        return TransactionResource::make($transaction->load('user'));
    }
    public function confirmTransformation(Transaction $transaction)
    {
        $transaction = $this->user->transactions()->where('transactions.id', $transaction->id)->first();
        if (!$transaction)
            throw new \Exception(__('messages.invalid_transaction_id'));
        $transaction->update(['status' => 'processing']);
        $userFcmTokens = $this->user->fcmTokens()->pluck('fcm_token');
        foreach ($userFcmTokens as $fcmToken) {
            pushFirebaseNotification($fcmToken, "جار معالجة معاملتك", "", [
                'transaction_id' => strval($transaction->id),
                'type' => Notifications::NEW_TRANSFORM['TYPE'],
                'state' => Notifications::NEW_TRANSFORM['STATE'],
                'icon' => 'notification_icon',
                'sound' => 'notification_sound'
            ]);
        }
        $this->notificationService->pushNotification("جار معالجة معاملتك", "", Notifications::NEW_TRANSFORM['TYPE'], Notifications::NEW_TRANSFORM['STATE'], $this->user, class_basename($transaction), $transaction->id);
        $this->notificationService->pushAdminsNotifications(Notifications::NEW_TRANSACTION, $transaction);
        
        return TransactionResource::make($transaction->load('user'));
    }
    public function cancelTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending')
            throw new \Exception(__('messages.can_not_change'));
        $transaction = $this->user->transactions()->where('transactions.id', $transaction->id)->first();
        if (!$transaction)
            throw new \Exception(__('messages.invalid_transactionId'));
        $transaction->update(['status' => 'cancelled']);
        $fromWallet = Wallet::where('id', $transaction->from_wallet_id)->first();
        if ($fromWallet && $fromWallet->key === 'cashBank360') {
            $userWallet = UserWallet::where('number', $transaction->from_wallet_number)->first();
            if ($userWallet) {
                $userWallet->logs()->create([
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount_sent,
                    'type' => 'deposit',
                ]);
            }
        }
        return TransactionResource::make($transaction->load('user'));
    }

}
