<?php

namespace App\Models;

use App\Enums\SpecificationInputType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single specification such as "Screen Size" or "Processor Model".
 */
#[Fillable([
    'specification_group_id', 'name', 'name_bn', 'slug', 'unit', 'input_type', 'is_filterable', 'sort_order',
])]
class Specification extends Model
{
    use HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_type' => SpecificationInputType::class,
            'is_filterable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function hasOptions(): bool
    {
        return in_array($this->input_type, [SpecificationInputType::Select, SpecificationInputType::MultiSelect], true);
    }

    /**
     * @return BelongsTo<SpecificationGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SpecificationGroup::class, 'specification_group_id');
    }

    /**
     * @return HasMany<SpecificationOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(SpecificationOption::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot(['is_filterable', 'sort_order']);
    }

    /**
     * @return HasMany<ProductSpecificationValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductSpecificationValue::class);
    }
}
