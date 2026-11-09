<x-store.auth-card :title="__('Create Account')" :subtitle="__('Register with your mobile number to follow your orders in one place.')">
    <form method="POST" action="{{ route('register.store') }}" class="mt-5 space-y-4" data-submit-once>
        @csrf

        <x-store.form-field name="name" :label="__('Full Name')" :value="old('name')" required autofocus maxlength="255" autocomplete="name" />
        <x-store.form-field name="phone" :label="__('Mobile Number')" type="tel" :value="old('phone')" required maxlength="20" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" />
        <x-store.form-field name="email" :label="__('Email')" type="email" :value="old('email')" maxlength="255" autocomplete="email" :hint="__('Optional. Needed to reset your password by email.')" />
        <x-store.form-field name="password" :label="__('Password')" type="password" required autocomplete="new-password" :hint="__('At least 8 characters.')" />
        <x-store.form-field name="password_confirmation" :label="__('Confirm Password')" type="password" required autocomplete="new-password" />

        <x-store.submit-button>
            <x-store.icon name="user" class="size-4" /> {{ __('Create Account') }}
        </x-store.submit-button>
    </form>

    <x-slot:footer>
        {{ __('Already have an account?') }} <a href="{{ route('login') }}" class="font-semibold text-accent hover:underline">{{ __('Login') }}</a>
    </x-slot:footer>
</x-store.auth-card>
