<?php

namespace App\Traits;
use App\Models\Scopes\CreatedAtDescScope;
use Illuminate\Database\Eloquent\Builder;

trait CreatedAtDescScopeTrait
{
    protected static function bootCreatedAtDescScopeTrait()
    {
        static::addGlobalScope('created_at_desc', function (Builder $builder) {
		$builder->orderBy('created_at', 'desc');
	    });
    }
}
