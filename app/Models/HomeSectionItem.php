<?php

namespace App\Models;

use App\Observers\CatalogCacheObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['home_section_id', 'itemable_type', 'itemable_id', 'sort_order'])]
#[WithoutTimestamps]
#[ObservedBy(CatalogCacheObserver::class)]
class HomeSectionItem extends Model
{
    /**
     * @return BelongsTo<HomeSection, $this>
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(HomeSection::class, 'home_section_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
