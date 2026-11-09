<x-store.auth-card :title="__('Forgot Password')" :subtitle="__('Enter the email on your account and we will send you a link to reset your password.')">
    <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4" data-submit-once>
        @csrf

        <x-store.form-field name="email" :label="__('Email')" type="email" :value="old('email')" required autofocus maxlength="255" autocomplete="email" />

        <x-store.submit-button>
            <x-store.icon name="mail" class="size-4" /> {{ __('Email Reset Link') }}
        </x-store.submit-button>
    </form>

    @if ($contact['hotline'])
        <p class="mt-5 border-t border-gray-100 pt-4 text-xs leading-5 text-gray-500">
            {!! __('Registered with a mobile number only? Call :hotline and we will help you reset it.', ['hotline' => '<a href="tel:'.e($contact['hotline_tel']).'" class="font-semibold text-gray-900 hover:text-accent">'.e($contact['hotline']).'</a>']) !!}
        </p>
    @endif

    <x-slot:footer>
        {{ __('Remembered it?') }} <a href="{{ route('login') }}" class="font-semibold text-accent hover:underline">{{ __('Back to login') }}</a>
    </x-slot:footer>
</x-store.auth-card>
