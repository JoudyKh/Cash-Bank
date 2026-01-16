<?php

namespace App\Services\Admin\ContactMessage;

use App\Models\ContactMessage;
use Illuminate\Pagination\AbstractPaginator;
use App\Http\Resources\ContactMessageResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactMessageService
{


    public function getAll($trashOnly = false): ContactMessageResource|AnonymousResourceCollection|AbstractPaginator
    {
        $messages = ContactMessage::orderByDesc($trashOnly ? 'deleted_at' : 'created_at');
        if ($trashOnly) {
            $messages->onlyTrashed();
        }
        $messages = $messages->paginate(config('app.pagination_limit'));
        return ContactMessageResource::collection($messages);
    }


}
