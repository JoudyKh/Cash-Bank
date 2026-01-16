<?php

namespace App\Http\Requests\Api\App\ContactMessage;

use App\Enums\ContactType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Schema(
 *     schema="StoreContactMessageRequest",
 *     title="Store Contact Message Request",
 *     description="Request body for storing a contact message",
 *     type="object",
 *     required={"type", "first_name", "email", "phone", "message"},
 *     
 *     @OA\Property(
 *         property="first_name",
 *         type="string",
 *         description="First name of the sender",
 *         example="John",
 *         minLength=3,
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         description="Last name of the sender",
 *         example="Doe",
 *         minLength=3,
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email address of the sender",
 *         example="john.doe@example.com",
 *         format="email",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="Phone number of the sender",
 *         example="+1234567890",
 *         minLength=8,
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Content of the message",
 *         example="This is a test message.",
 *         maxLength=65535
 *     ),
 * )
 */
class StoreContactMessageRequest extends FormRequest
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
            'type' => ['nullable', 'string', Rule::in(ContactType::all())],
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['nullable', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:8', 'max:255'],
            'message' => ['required', 'string', 'max:65535'],
        ];
    }
}