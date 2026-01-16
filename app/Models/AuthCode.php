<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model
{
    use HasFactory;
    protected $table='auth_codes';
    protected $fillable = [
        'email',
        'user_id',
        'code',
        'expired_at',
    ];
    protected $casts = [
        'created_at' => 'date:Y-m-d h:i a',
        'updated_at' => 'date:Y-m-d h:i a',
    ];
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
