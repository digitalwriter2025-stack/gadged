<x-store.auth-card :title="__('Login')" :subtitle="__('Sign in with your mobile number or email to see your orders and check out faster.')">
    <form method="POST" action="{{ route('login.store') }}" class="mt-5 space-y-4" data-submit-once>
        @csrf

        <x-store.form-field name="login" :label="__('Mobile Number or Email')" :value="old('login')" required autofocus maxlength="255" autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="{{ __('01XXXXXXXXX or you@example.com') }}" />

        <div>
            <x-store.form-field name="password" :label="__('Password')" type="password" required autocomplete="current-password" />

            <div class="mt-2.5 flex items-center justify-between gap-3">
                <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-gray-700">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-gray-300 accent-accent" @checked(old('remember'))>
                    {{ __('Remember me') }}
                </label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-accent hover:underline">{{ __('Forgot password?') }}</a>
            </div>
        </div>

        <x-store.submit-button>
            <x-store.icon name="user" class="size-4" /> {{ __('Login') }}
        </x-store.submit-button>
    </form>

    <x-slot:footer>
        {{ __('New here?') }} <a href="{{ route('register') }}" class="font-semibold text-accent hover:underline">{{ __('Create an account') }}</a>
    </x-slot:footer>
</x-store.auth-card>
