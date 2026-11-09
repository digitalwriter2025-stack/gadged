<?php

namespace App\Http\Requests\Store;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Star rating and text of a product review, submitted from the product page.
 */
class StoreReviewRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'rating' => __('rating'),
            'title' => __('title'),
            'body' => __('review'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => __('Please choose a star rating.'),
        ];
    }

    /**
     * Validation errors land back on the review form, not the top of the product page.
     */
    protected function getRedirectUrl(): string
    {
        $product = $this->route('product');

        return $product instanceof Product
            ? route('store.products.show', $product->slug).'#reviews'
            : parent::getRedirectUrl();
    }

    protected function prepareForValidation(): void
    {
        foreach (['title', 'body'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }
}
