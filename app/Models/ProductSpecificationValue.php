<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One specification value of a product. Select-type specs point at an option;
 * free-text specs use `value`, and `value_numeric` backs range filters.
 */
#[Fillable(['product_id', 'specification_id', 'specification_option_id', 'value', 'value_numeric'])]
#[WithoutTimestamps]
class ProductSpecificationValue extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['value_numeric' => 'decimal:4'];
    }

    /**
     * Text shown to shoppers: option label for select specs, raw value otherwise.
     */
    public function displayValue(): string
    {
        $value = $this->option?->value ?? (string) $this->value;
        $unit = $this->specification?->unit;

        return $unit && $this->option === null && is_numeric($value) ? "{$value} {$unit}" : $value;
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Specification, $this>
     */
    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }

    /**
     * @return BelongsTo<SpecificationOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(SpecificationOption::class, 'specification_option_id');
    }
}
