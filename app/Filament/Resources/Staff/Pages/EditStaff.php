<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use App\Services\StaffAccessService;
use App\Support\Filament\SectionAccessFields;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property User $record
 */
class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->roles->first()?->name;
        $data[SectionAccessFields::STATE_PATH] = SectionAccessFields::group($this->record->permissions->pluck('name'));

        return $data;
    }

    protected function beforeSave(): void
    {
        if (! $this->canChangeAccess()) {
            return;
        }

        if (blank($this->data['role'] ?? null) && SectionAccessFields::flatten($this->data) === []) {
            Notification::make()
                ->title('Choose a role or tick at least one section')
                ->body('Without any access the account could not open the dashboard. Block the account instead.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    /**
     * @param  User  $record
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update(Arr::except($data, ['role', SectionAccessFields::STATE_PATH]));

        if ($this->canChangeAccess()) {
            app(StaffAccessService::class)->sync($this->actor(), $record, $data['role'] ?? null, SectionAccessFields::flatten($data));
        }

        return $record;
    }

    private function canChangeAccess(): bool
    {
        return app(StaffAccessService::class)->canChangeAccessOf($this->actor(), $this->record);
    }

    private function actor(): User
    {
        /** @var User */
        return auth()->user();
    }
}
