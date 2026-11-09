<?php

namespace App\Models;

use App\Enums\LedgerAccount;
use App\Enums\LedgerCategory;
use App\Enums\LedgerEntryType;
use Database\Factories\LedgerEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A manually recorded income or expense. The type always follows the category,
 * so an entry can never be filed as "income" under an expense category.
 */
#[Fillable(['category', 'title', 'amount', 'entry_date', 'account', 'reference', 'note', 'recorded_by'])]
class LedgerEntry extends Model
{
    /** @use HasFactory<LedgerEntryFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (LedgerEntry $entry): void {
            $entry->type = $entry->category->type();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LedgerEntryType::class,
            'category' => LedgerCategory::class,
            'account' => LedgerAccount::class,
            'amount' => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ofType(Builder $query, LedgerEntryType $type): void
    {
        $query->where('type', $type);
    }
}
