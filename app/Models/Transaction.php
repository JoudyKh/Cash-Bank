<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount_sent',
        'amount_received',
        'amount_confirmed',
        'from_wallet_id',
        'to_wallet_id',
        'from_wallet_number',
        'to_wallet_number',
        'status',
        'note', 
        'key',
        ] ;
        public static $searchable = [
            'user_id',
            'status',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->key = self::generateUniqueKey();
        });
    }

    private static function generateUniqueKey($length = 10)
    {
        do {
            $key = Str::random($length);
        } while (self::where('key', $key)->exists());

        return $key;
    }
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i A');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i A');
    }
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function fromWallet(){
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }
    public function toWallet(){
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }
    public function walletLogs(){
        return $this->hasMany(UserWalletLog::class, 'transaction_id');
    }
}
