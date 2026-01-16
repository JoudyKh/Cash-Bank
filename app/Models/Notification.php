<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Spatie\Translatable\HasTranslations;

class Notification extends Model
{
    use HasFactory;
    // , HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'has_read',
        'type',
        'state',
        'user_id',
        'model_id',
        'model_type',
        'additional_data',
    ];

    protected function asJson($value): bool|string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    // public $translatable = ['title', 'description'];

    protected $appends = [
        'additional_data',
    ];
    protected $hidden = [
        'deleted_at',
    ];

    public function getAdditionalDataAttribute()
    {
        return isset($this->attributes['additional_data']) ? json_decode($this->attributes['additional_data'], true) : null;
    }
}
