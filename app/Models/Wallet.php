<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'number',
        'icon',
        'key',
    ];
    public function WithdrawalTransactions(){
        return $this->hasMany(Transaction::class, 'from_wallet_id');
    }
    public function depositTransactions(){
        return $this->hasMany(Transaction::class, 'to_wallet_id');
    }
}
