<?php

namespace App\Http\Requests\Store;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Order number and the mobile number used at checkout, submitted from the Track Order page.
 */
class TrackOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'regex:/^01[3-9]\d{8}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order_number' => __('order number'),
            'phone' => __('mobile number'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => __('Enter the 11-digit mobile number you used at checkout, e.g. 01712345678.'),
        ];
    }

    /**
     * Accepts "#GD261108-0001" / lower case order numbers and "+880 1712-345678" style phones.
     */
    protected function prepareForValidation(): void
    {
        $orderNumber = $this->input('order_number');
        $phone = $this->input('phone');

        if (is_string($orderNumber)) {
            $this->merge(['order_number' => strtoupper(ltrim(trim($orderNumber), '#'))]);
        }

        if (is_string($phone)) {
            $phone = preg_replace('/[\s\-()]/', '', $phone);
            $this->merge(['phone' => preg_replace('/^\+?88(?=01)/', '', $phone)]);
        }
    }
}
