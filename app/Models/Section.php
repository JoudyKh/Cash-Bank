<?php

namespace App\Models;

use App\Constants\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Section extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected function asJson($value): bool|string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public $translatable = ['name'];

    protected $fillable = [
        'parent_id',
        'type',
        'name',
        'image',
    ];
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model) {
            $model->hidden = $model->getHiddenAttributes();
        });

        static::saving(function ($model) {
            $model->hidden = $model->getHiddenAttributes();
        });
    }



    public function getHiddenAttributes(): array
    {
        $sectionAttributes = Constants::SECTIONS_TYPES[$this->type]['attributes'];
        $sectionAttributes[] = 'id';
        $sectionAttributes[] = 'type';
        $allAttributes = Schema::getColumnListing($this->getTable());

        return array_diff($allAttributes, $sectionAttributes);
    }


    public function subSections()
    {
        return $this->hasMany(Section::class, 'parent_id');
    }


}
