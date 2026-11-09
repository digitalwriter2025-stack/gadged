<?php

declare(strict_types=1);

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use App\Models\User;
use App\Services\StaffAccessService;
use App\Support\Filament\SectionAccessFields;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function beforeCreate(): void
    {
        if (blank($this->data['role'] ?? null) && SectionAccessFields::flatten($this->data) === []) {
            Notification::make()
                ->title('Choose a role or tick at least one section')
                ->body('Without any access the account could not open the dashboard.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $staff = User::query()->forceCreate([
            ...Arr::except($data, ['role', SectionAccessFields::STATE_PATH]),
            'email_verified_at' => now(),
        ]);

        /** @var User $actor */
        $actor = auth()->user();

        app(StaffAccessService::class)->sync($actor, $staff, $data['role'] ?? null, SectionAccessFields::flatten($data));

        return $staff;
    }
}
