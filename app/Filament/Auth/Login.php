<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable|null
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return parent::getHeading();
        }

        return 'Welcome back';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return parent::getSubheading() ?? 'Sign in to manage your store.';
    }
}
