<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'phone_number',
        'email_verified_at',
        'password',
        'is_active',
        'last_active_at',
        'referral_code',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()  
    {  
        parent::boot();  

        static::creating(function ($user) {  
            // Generate a unique 10-character referral code  
            $user->referral_code = self::generateUniqueReferralCode();  
        });  
    }  
    private static function generateUniqueReferralCode()  
    {  
        do {  
            // Generate a random 10-character string  
            $referralCode = Str::random(10);  
        } while (self::where('referral_code', $referralCode)->exists());  

        return $referralCode;  
    }  

    public function images():HasMany
    {
        return $this->hasMany(UserImage::class);
    }

    public function fcmTokens():HasMany
    {
        return $this->hasMany(UserFcmToken::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
    public function wallet(){
        return $this->hasOne(UserWallet::class, 'user_id');
    }
    public function transactions(){
        return $this->hasMany(Transaction::class, 'user_id');
    }
}
