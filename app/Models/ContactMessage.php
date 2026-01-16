<?php

namespace App\Models;

use App\Enums\ContactType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactMessage extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'first_name' , 
        'last_name' , 
        'message' ,
        'type' ,
        'email' , 
        'phone' ,
    ] ;
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Get all possible types.
     *
     * @return array
     */
    public static function typesArray(): array
    {
        return ContactType::all();
    }
}
