<?php

namespace App\Http\Requests\Api\App\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

/**  
 * @OA\Schema(  
 *     schema="CreateTransactionRequest",  
 *     type="object",  
 *     @OA\Property(  
 *         property="amount_sent",  
 *         type="double",  
 *     ),
 *     @OA\Property(  
 *         property="amount_received",  
 *         type="double",  
 *     ),
 *     @OA\Property(  
 *         property="from_wallet_id",  
 *         type="integer",  
 *     ),
 *     @OA\Property(  
 *         property="to_wallet_id",  
 *         type="integer",  
 *     ),
 *     @OA\Property(  
 *         property="from_wallet_number",  
 *         type="string",  
 *     ),
 *     @OA\Property(  
 *         property="to_wallet_number",  
 *         type="string",  
 *     ),
 * )  
 */
class CreateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount_sent' => 'required|numeric',
            'amount_received' => 'required|numeric',
            'from_wallet_id' => 'required|exists:wallets,id',
            'to_wallet_id' => 'required|exists:wallets,id',
            'from_wallet_number' => 'required|string',
            'to_wallet_number' => 'required|string',
        ];
    }
    public function withValidator($validator)
    {
        $validator->sometimes('from_wallet_number', [
            'required',
            'string',
            'regex:/^(EG\d+|\d{4}|.+)$/',
        ], function ($input) {
            $wallet = DB::table('wallets')->where('id', $input->from_wallet_id)->first();
    
            return $wallet && $wallet->key === 'instapay';
        });
    
        $validator->after(function ($validator) {
            $fromWallet = DB::table('wallets')->where('id', $this->from_wallet_id)->first();    
            if ($fromWallet && $fromWallet->key === 'cashBank360') {
                $user = auth()->user();
    
                if ($user->wallet->number !== $this->from_wallet_number) {
                    $validator->errors()->add('from_wallet_number', 'خطأ في رقم المحفظة');
                }
                if ($user->wallet->amount < $this->amount_sent) {
                    $validator->errors()->add('amount_sent', 'ليس لديك رصيد كافٍ');
                }
            }
            $toWallet = DB::table('wallets')->where('id', $this->to_wallet_id)->first();
            if ($toWallet && $toWallet->key === 'cashBank360') {
                $walletExists = DB::table('user_wallets')
                    ->where('number', $this->to_wallet_number)
                    ->exists();
    
                if (!$walletExists) {
                    $validator->errors()->add('to_wallet_number', 'خطأ في رقم المحفظة');
                }
            }
        });
    }
}
