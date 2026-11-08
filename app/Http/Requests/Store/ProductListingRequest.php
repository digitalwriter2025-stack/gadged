<?php

namespace App\Http\Requests\Store;

use App\Enums\StockStatus;
use App\Services\ProductFilterService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query-string filters for product listings (category page and friends).
 * Invalid values are dropped rather than rejected, so a stale or hand-edited
 * URL still shows the listing.
 */
class ProductListingRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', Rule::in(array_keys(ProductFilterService::SORTS))],
            'stock' => ['nullable', 'array'],
            'stock.*' => ['string', Rule::enum(StockStatus::class)],
            'min' => ['nullable', 'integer', 'min:0'],
            'max' => ['nullable', 'integer', 'min:0'],
            'brand' => ['nullable', 'array', 'max:50'],
            'brand.*' => ['string', 'max:191'],
            'spec' => ['nullable', 'array', 'max:30'],
            'spec.*' => ['array', 'max:50'],
            'spec.*.*' => ['string', 'max:170'],
        ];
    }

    /**
     * Never redirect: filters() reads only the keys that passed.
     */
    protected function failedValidation(Validator $validator): void {}

    /**
     * @return array{q: ?string, sort: string, stock: list<string>, min: ?int, max: ?int, brands: list<string>, specs: array<string, list<string>>}
     */
    public function filters(): array
    {
        $data = $this->validator->valid();

        return [
            'q' => filled($data['q'] ?? null) ? trim($data['q']) : null,
            'sort' => $data['sort'] ?? ProductFilterService::DEFAULT_SORT,
            'stock' => array_values(array_unique($data['stock'] ?? [])),
            'min' => isset($data['min']) ? (int) $data['min'] : null,
            'max' => isset($data['max']) ? (int) $data['max'] : null,
            'brands' => array_values(array_unique($data['brand'] ?? [])),
            'specs' => collect($data['spec'] ?? [])
                ->filter(fn (mixed $values, mixed $slug): bool => is_string($slug) && is_array($values))
                ->map(fn (array $values): array => array_values(array_unique(array_filter($values, 'is_string'))))
                ->filter()
                ->all(),
        ];
    }
}
