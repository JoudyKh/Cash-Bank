<?php

namespace App\Http\Requests;

use App\Models\Info;
use App\Rules\OneOrNone;
use Illuminate\Validation\Rule;
use App\Constants\InfoValidationRules;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="UpdateInfoRequest",
 *     type="object",
 *     title="Update Info Request",
 *     description="Request body for updating information",
 *     @OA\Property(
 *         property="transformation-referral_amount",
 *         type="string",
 *         description="referral amount",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="transaction-process_time",
 *         type="string",
 *         description="Number of hours",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="transaction-commission_rate",
 *         type="string",
 *         description="commission rate",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="about-customer_service",
 *         type="string",
 *         description="Application app store link",
 *         nullable=true,
 *         default="http://www.google.com",
 *     ),
 *     @OA\Property(
 *         property="about-transfer_service",
 *         type="string",
 *         description="Application app store link",
 *         nullable=true,
 *         default="http://www.google.com",
 *     ),
 * )
 */
class UpdateInfoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request-
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request-
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'about-customer_service' => ['string', 'url'],
            'about-transfer_service' => ['string', 'url'],
            'transaction-commission_rate' => ['numeric'],
            'transaction-process_time' => ['string'],
            'transformation-referral_amount' => ['numeric']
        ];
    }
}
