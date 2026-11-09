<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\LedgerAccount;
use App\Enums\LedgerCategory;
use App\Enums\LedgerEntryType;
use App\Enums\StaffRole;
use App\Filament\Resources\LedgerEntries\LedgerEntryResource;
use App\Filament\Resources\LedgerEntries\Pages\CreateLedgerEntry;
use App\Filament\Resources\LedgerEntries\Pages\EditLedgerEntry;
use App\Filament\Resources\LedgerEntries\Pages\ListLedgerEntries;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LedgerEntryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_record_an_expense(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin);

        Livewire::test(CreateLedgerEntry::class)
            ->fillForm([
                'type' => LedgerEntryType::Expense->value,
                'category' => LedgerCategory::Rent->value,
                'title' => 'November shop rent',
                'amount' => '15000',
                'entry_date' => '2026-11-01',
                'account' => LedgerAccount::Bkash->value,
                'reference' => 'TRX123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $entry = LedgerEntry::query()->sole();

        $this->assertSame(LedgerEntryType::Expense, $entry->type);
        $this->assertSame(LedgerCategory::Rent, $entry->category);
        $this->assertSame('15000.00', $entry->amount);
        $this->assertSame('2026-11-01', $entry->entry_date->toDateString());
        $this->assertSame(LedgerAccount::Bkash, $entry->account);
        $this->assertSame($admin->id, $entry->recorded_by);
    }

    public function test_category_must_belong_to_the_selected_type(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateLedgerEntry::class)
            ->fillForm([
                'type' => LedgerEntryType::Income->value,
                'category' => LedgerCategory::Rent->value,
                'title' => 'Wrong type',
                'amount' => '100',
                'entry_date' => '2026-11-01',
                'account' => LedgerAccount::Cash->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['category']);

        $this->assertDatabaseCount('ledger_entries', 0);
    }

    public function test_type_always_follows_the_category(): void
    {
        $entry = LedgerEntry::factory()->category(LedgerCategory::ServiceIncome)->create();
        $this->assertSame(LedgerEntryType::Income, $entry->type);

        $entry->update(['category' => LedgerCategory::Packaging]);
        $this->assertSame(LedgerEntryType::Expense, $entry->fresh()->type);
    }

    public function test_admin_can_edit_an_entry(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $entry = LedgerEntry::factory()->category(LedgerCategory::ServiceIncome)->create(['amount' => '700.00']);

        Livewire::test(EditLedgerEntry::class, ['record' => $entry->getRouteKey()])
            ->assertSchemaStateSet(['type' => LedgerEntryType::Income])
            ->fillForm(['amount' => '950'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('950.00', $entry->fresh()->amount);
    }

    public function test_list_shows_entries_with_income_and_expense_tabs(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $income = LedgerEntry::factory()->category(LedgerCategory::ServiceIncome)->create(['title' => 'Laptop repair']);
        $expense = LedgerEntry::factory()->category(LedgerCategory::Salary)->create(['title' => 'Staff salary Nov']);

        Livewire::test(ListLedgerEntries::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$income, $expense])
            ->assertSee('Total Income')
            ->assertSee('Total Expense')
            ->set('activeTab', LedgerEntryType::Income->value)
            ->assertCanSeeTableRecords([$income])
            ->assertCanNotSeeTableRecords([$expense]);
    }

    public function test_only_staff_with_finance_access_can_open_entries(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::Admin)->create())
            ->get(LedgerEntryResource::getUrl())
            ->assertSuccessful();

        $this->actingAs(User::factory()->staff(StaffRole::OrderManager)->create())
            ->get(LedgerEntryResource::getUrl())
            ->assertForbidden();
    }
}
