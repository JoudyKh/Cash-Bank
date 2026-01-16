<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'number',
    ];

    protected $casts = [
        'amount' => 'float', 
    ];
    public static function boot()
    {
        parent::boot();

        static::creating(function ($userWallet) {
            // Generate a unique 12-digit number
            do {
                $userWallet->number = self::generateUniqueNumber();
            } while (self::where('number', $userWallet->number)->exists());
        });
    }
    /**
     * Generate a random 12-digit number
     *
     * @return string
     */
    private static function generateUniqueNumber()
    {
        return str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function logs()
    {
        return $this->hasMany(UserWalletLog::class, 'user_wallet_id');
    }
    public function getAmountAttribute()
    {
        $deposits = $this->logs()
            ->where('type', 'deposit')
            ->sum('amount');

        $withdrawals = $this->logs()
            ->where('type', 'withdrawal')
            ->sum('amount');

        return $deposits - $withdrawals;
    }
}
