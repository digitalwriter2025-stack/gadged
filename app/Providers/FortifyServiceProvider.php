<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Support\PhoneNumber;
use App\Support\Storefront\StoreLayout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::authenticateUsing($this->authenticate(...));

        Fortify::loginView(fn (): View => view('store.auth.login'));
        Fortify::registerView(fn (): View => view('store.auth.register'));
        Fortify::requestPasswordResetLinkView(fn (): View => view('store.auth.forgot-password', [
            'contact' => $this->app->make(StoreLayout::class)->contact(),
        ]));
        Fortify::resetPasswordView(fn (Request $request): View => view('store.auth.reset-password', ['request' => $request]));
        Fortify::confirmPasswordView(fn (): View => view('store.auth.confirm-password'));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }

    /**
     * Customers sign in with their mobile number or email address.
     *
     * @throws ValidationException when the password is right but the account is blocked
     */
    private function authenticate(Request $request): ?User
    {
        $login = trim((string) $request->input(Fortify::username()));

        $user = str_contains($login, '@')
            ? User::query()->where('email', Str::lower($login))->first()
            : User::query()->where('phone', PhoneNumber::normalize($login))->first();

        if ($user === null || ! Hash::check((string) $request->input('password'), $user->password)) {
            return null;
        }

        if (! $user->isActive()) {
            throw ValidationException::withMessages([
                Fortify::username() => __('This account has been blocked. Please contact support.'),
            ]);
        }

        return $user;
    }
}
