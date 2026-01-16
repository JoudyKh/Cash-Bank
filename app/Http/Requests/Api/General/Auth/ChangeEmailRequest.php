<?php

namespace App\Http\Requests\Api\General\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**  
 * @OA\Schema(  
 *     schema="ChangeEmailRequest",  
 *     type="object",  
 *     required={  
 *         "email", "verification_code",
 *     },  
 *     @OA\Property(property="email", type="email", example="email@example.com"),  
 *     @OA\Property(property="verification_code", type="number", example=10),  
 * )  
 */
class ChangeEmailRequest extends FormRequest
{
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
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255|unique:users,email,' . request()->user()->id,
            'verification_code' => 'required',
        ];
    }
}
