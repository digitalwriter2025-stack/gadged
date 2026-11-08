<?php

namespace App\Http\Requests\Store;

use App\Enums\PaymentMethod;
use App\Services\CheckoutService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Customer and delivery details submitted from the checkout page.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(CheckoutService $checkout): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'regex:/^01[3-9]\d{8}$/'],
            'customer_email' => ['nullable', 'email', 'max:191'],
            'district_id' => ['required', 'integer', Rule::exists('districts', 'id')->where('is_active', true)],
            'shipping_area' => ['nullable', 'string', 'max:150'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', Rule::in(array_map(fn (PaymentMethod $method): string => $method->value, $checkout->paymentMethods()))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'full name',
            'customer_phone' => 'mobile number',
            'customer_email' => 'email',
            'district_id' => 'district',
            'shipping_area' => 'area / thana',
            'shipping_address' => 'address',
            'customer_note' => 'order note',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Enter a valid 11-digit Bangladeshi mobile number, e.g. 01712345678.',
        ];
    }

    /**
     * Accepts "+880 1712-345678" style input and keeps the local 11-digit form.
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->input('customer_phone');

        if (is_string($phone)) {
            $phone = preg_replace('/[\s\-()]/', '', $phone);
            $phone = preg_replace('/^\+?88(?=01)/', '', $phone);

            $this->merge(['customer_phone' => $phone]);
        }
    }

    /**
     * @return array{customer_name: string, customer_phone: string, customer_email: ?string, district_id: int, shipping_area: ?string, shipping_address: string, customer_note: ?string, payment_method: string}
     */
    public function details(): array
    {
        $data = $this->validated();

        return [
            'customer_name' => trim($data['customer_name']),
            'customer_phone' => $data['customer_phone'],
            'customer_email' => filled($data['customer_email'] ?? null) ? trim($data['customer_email']) : null,
            'district_id' => (int) $data['district_id'],
            'shipping_area' => filled($data['shipping_area'] ?? null) ? trim($data['shipping_area']) : null,
            'shipping_address' => trim($data['shipping_address']),
            'customer_note' => filled($data['customer_note'] ?? null) ? trim($data['customer_note']) : null,
            'payment_method' => $data['payment_method'],
        ];
    }
}
