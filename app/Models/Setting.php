<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Grouped key/value setting. Read through SettingsService (cached), never directly.
 */
#[Fillable(['group', 'key', 'value'])]
class Setting extends Model
{
    protected static function booted(): void
    {
        $flush = fn () => app(SettingsService::class)->flush();

        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['value' => 'json'];
    }
}
