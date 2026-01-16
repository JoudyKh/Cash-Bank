<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWalletLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_wallet_id',
        'amount',
        'type',
        'transaction_id',
    ] ;
    protected $casts = [
        'wallet_amount' => 'float',
    ];
    public function getWalletAmountAttribute()
    {
        $currentBalance = $this->userWallet->logs()  
            ->where('id', '<=', $this->id)  
            ->orderBy('id')  
            ->get() 
            ->reduce(function ($carry, $log) {  
                return $carry + ($log->type === 'deposit' ? $log->amount : -$log->amount);  
            }, 0);  
        
        return $currentBalance;  
    }
    public function userWallet(){
        return $this->belongsTo(UserWallet::class, 'user_wallet_id');
    }
    public function transaction(){
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
