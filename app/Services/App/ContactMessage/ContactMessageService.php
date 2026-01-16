<?php

namespace App\Services\App\ContactMessage;

use App\Models\ContactMessage;
use App\Http\Requests\Api\App\ContactMessage\StoreContactMessageRequest;

class ContactMessageService
{

    public function store(StoreContactMessageRequest &$request): ContactMessage
    {
        return ContactMessage::create($request->validated());
    }
}
