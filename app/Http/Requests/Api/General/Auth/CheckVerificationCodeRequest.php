<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Traits\HandlesValidationErrorsTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CheckVerificationCodeRequest extends FormRequest
{
    use HandlesValidationErrorsTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $user = auth('sanctum')->user();
        return array_merge([
            'verification_code' => 'required',

        ], (!$user ? ['email' => 'required|email'] : []));
    }
}
