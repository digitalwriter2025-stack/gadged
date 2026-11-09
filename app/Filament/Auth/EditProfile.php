<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Components\Component;

/**
 * The Super Admin account is locked: on its profile only the password can be changed.
 */
class EditProfile extends BaseEditProfile
{
    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()->disabled($this->isLockedAccount());
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()->disabled($this->isLockedAccount());
    }

    private function isLockedAccount(): bool
    {
        $user = $this->getUser();

        return $user instanceof User && $user->isSuperAdmin();
    }
}
