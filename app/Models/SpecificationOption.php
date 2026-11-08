<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['specification_id', 'value', 'slug', 'sort_order'])]
class SpecificationOption extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /**
     * @return BelongsTo<Specification, $this>
     */
    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }
}
