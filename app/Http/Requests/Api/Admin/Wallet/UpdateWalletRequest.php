<?php

namespace App\Http\Requests\Api\Admin\Wallet;

use Illuminate\Foundation\Http\FormRequest;

/**  
 * @OA\Schema(  
 *     schema="UpdateWalletRequest",  
 *     type="object",  
 *     @OA\Property(  
 *         property="name",  
 *         type="string",  
 *     ),
 *     @OA\Property(  
 *         property="number",  
 *         type="string",  
 *     ),
 *     @OA\Property(  
 *         property="icon",  
 *         type="string",  
 *         format="binary",  
 *     ),
 * )  
 */
class UpdateWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
    * Prepare the data for validation.
    */
    protected function prepareForValidation(): void
    {
        
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'number' => 'sometimes|string',
            'icon' => 'sometimes|image|mimes:png,jpg,jpeg',
        ];
    }
}
