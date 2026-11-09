<x-store.auth-card :title="__('Confirm Password')" :subtitle="__('For your security, please confirm your password to continue.')">
    <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-5 space-y-4" data-submit-once>
        @csrf

        <x-store.form-field name="password" :label="__('Password')" type="password" required autofocus autocomplete="current-password" />

        <x-store.submit-button>
            <x-store.icon name="shield" class="size-4" /> {{ __('Confirm') }}
        </x-store.submit-button>
    </form>
</x-store.auth-card>
