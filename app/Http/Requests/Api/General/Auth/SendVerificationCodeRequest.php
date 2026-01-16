<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Rules\AdminRoleEmail;
use App\Rules\UserRoleEmail;
use App\Traits\HandlesValidationErrorsTrait;
use Illuminate\Foundation\Http\FormRequest;

class SendVerificationCodeRequest extends FormRequest
{
    use HandlesValidationErrorsTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function prepareForValidation()
    {
        $this->merge([  
            'type' => $this->query('type') ? trim($this->query('type')) : 'new',  
        ]);  
    }
    public function rules()  
    {  
        // Check if the user is authenticated.  
        return array_merge(  
            auth('sanctum')->user() ? [] : [  
                'email' => ['required', 'email'],  
            ],  
            [  
                'type' => 'required|in:new,resetPass',  
            ]  
        );  
    }  
}
