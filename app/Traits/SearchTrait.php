<?php

namespace App\Traits;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait SearchTrait
{
    public function applySearchAndSort(&$query, Request $request, array $searchable)
    {
        if ($request->has('search')) {
            $searchTerm = $request->input('search');

            if ($request->has('sort_by') && !empty($request->sort_by)) {
                $this->validateSortBy($searchable);
                $query->where($request->sort_by, 'LIKE', "%$searchTerm%");
            } else {
                $query->where(function ($q)use($searchable, $searchTerm) {
                    foreach ($searchable as $field) {
                        $q->orWhere($field, 'LIKE', "%$searchTerm%");
                    }
                });

            }
        }

        if ($request->has('trash') && $request->input('trash') == 1 && Auth::user()?->hasRole(Constants::ADMIN_ROLE)) {
            $query->onlyTrashed();
        }
    }
    protected function validateSortBy($searchable)
    {
        $validator = validator()->make(
            ['sort_by' => 'in:' . implode(',', $searchable)],
            ['sort_by.in' => 'The selected sort_by field is invalid. Please choose from data base column']
        );

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
    }
}
