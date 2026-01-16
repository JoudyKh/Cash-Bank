<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Traits\HandlesValidationErrorsTrait;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    use HandlesValidationErrorsTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'username' => 'string|max:255|unique:users,username,' . request()->user()->id,
            'phone_number' => 'string|max:255|min:9',
            'password' => 'nullable|min:8|confirmed',
            'old_password' => 'required_with:password',
        ];
    }
}
