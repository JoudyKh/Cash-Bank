<?php

namespace App\Http\Requests\Api\Admin\Transaction;

use Illuminate\Foundation\Http\FormRequest;

/**  
 * @OA\Schema(  
 *     schema="UpdateStatusRequest",  
 *     type="object",  
 *     required={  
 *         "status",
 *     },  
 *     @OA\Property(  
 *         property="status",  
 *         type="string", 
 *          enum={"cancelled","completed"}, 
 *     ),
 *     @OA\Property(  
 *         property="note",  
 *         type="string",  
 *     ),
 *     @OA\Property(  
 *         property="amount_confirmed",  
 *         type="double",  
 *     ),
 * )  
 */
class UpdateStatusRequest extends FormRequest
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
            'status' => 'required|string|in:cancelled,completed',
            'note' => 'sometimes|string',
            'amount_confirmed' => 'numeric|required_if:status,completed',
        ];
    }
}
