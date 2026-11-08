<?php

namespace App\Support\Storefront;

use App\Enums\SpecificationInputType;
use App\Models\Product;
use App\Models\ProductSpecificationValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Formats a product's specification values for the storefront. Shared by the
 * product page (full table) and listing cards (short feature bullets).
 *
 * Expects `specificationValues.specification.group` and `specificationValues.option` to be loaded.
 */
class ProductSpecifications
{
    /**
     * Specification values grouped under their headings, in admin-defined order.
     * Multi-select specifications keep every selected option as its own line.
     *
     * @return list<array{name: string, rows: list<array{label: string, values: list<string>}>}>
     */
    public function groups(Product $product): array
    {
        return $product->specificationValues
            ->filter(fn (ProductSpecificationValue $value): bool => $value->specification?->group !== null)
            ->sortBy([
                fn (ProductSpecificationValue $a, ProductSpecificationValue $b): int => [$a->specification->group->sort_order, $a->specification->group->id] <=> [$b->specification->group->sort_order, $b->specification->group->id],
                fn (ProductSpecificationValue $a, ProductSpecificationValue $b): int => [$a->specification->sort_order, $a->specification->id] <=> [$b->specification->sort_order, $b->specification->id],
            ])
            ->groupBy(fn (ProductSpecificationValue $value): int => $value->specification->specification_group_id)
            ->map(fn (Collection $values): array => [
                'name' => $values->first()->specification->group->name,
                'rows' => $values
                    ->groupBy('specification_id')
                    ->map(fn (Collection $specValues): array => [
                        'label' => $specValues->first()->specification->name,
                        'values' => $specValues->map(fn (ProductSpecificationValue $value): string => $this->value($value))
                            ->filter(fn (string $text): bool => $text !== '')
                            ->values()
                            ->all(),
                    ])
                    ->filter(fn (array $row): bool => $row['values'] !== [])
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $group): bool => $group['rows'] !== [])
            ->values()
            ->all();
    }

    /**
     * The first specifications as "Label: value" lines, skipping warranty rows.
     *
     * @param  list<array{name: string, rows: list<array{label: string, values: list<string>}>}>  $groups
     * @return list<string>
     */
    public function keyFeatures(array $groups, int $count): array
    {
        return collect($groups)
            ->flatMap(fn (array $group): array => $group['rows'])
            ->reject(fn (array $row): bool => $this->isWarranty($row['label']))
            ->take($count)
            ->map(fn (array $row): string => $row['label'].': '.implode(', ', $row['values']))
            ->values()
            ->all();
    }

    public function isWarranty(string $label): bool
    {
        return Str::contains(Str::lower($label), 'warranty');
    }

    private function value(ProductSpecificationValue $value): string
    {
        $text = trim($value->displayValue());

        if ($value->specification->input_type === SpecificationInputType::Boolean) {
            return in_array(Str::lower($text), ['1', 'yes', 'true'], true) ? 'Yes' : 'No';
        }

        return $text;
    }
}
