<x-store.auth-card :title="__('Reset Password')" :subtitle="__('Choose a new password for your account.')">
    <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4" data-submit-once>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-store.form-field name="email" :label="__('Email')" type="email" :value="old('email', $request->query('email'))" required maxlength="255" autocomplete="email" />
        <x-store.form-field name="password" :label="__('New Password')" type="password" required autofocus autocomplete="new-password" :hint="__('At least 8 characters.')" />
        <x-store.form-field name="password_confirmation" :label="__('Confirm New Password')" type="password" required autocomplete="new-password" />

        <x-store.submit-button>
            <x-store.icon name="shield" class="size-4" /> {{ __('Reset Password') }}
        </x-store.submit-button>
    </form>
</x-store.auth-card>
