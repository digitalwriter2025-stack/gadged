<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Heading that groups specifications on a product page (Display, Processor, ...).
 */
#[Fillable(['name', 'name_bn', 'slug', 'sort_order'])]
class SpecificationGroup extends Model
{
    use HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /**
     * @return HasMany<Specification, $this>
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(Specification::class)->orderBy('sort_order');
    }
}
