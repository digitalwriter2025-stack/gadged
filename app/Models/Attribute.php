<?php

namespace App\Models;

use App\Enums\AttributeDisplayType;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A variation axis such as RAM, Storage or Color.
 */
#[Fillable(['name', 'slug', 'display_type', 'sort_order', 'name_bn'])]
class Attribute extends Model
{
    use HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'display_type' => AttributeDisplayType::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<AttributeValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }
}
