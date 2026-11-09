<x-layouts.store :title="__('My Account').' | '.config('app.name')">
    <x-slot:head>
        <meta name="robots" content="noindex, nofollow">
    </x-slot:head>

    @php
        $statusTones = [
            'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            'info' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            'primary' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
            'gray' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
        ];

        $statTiles = [
            ['label' => __('Total Orders'), 'value' => $stats['total'], 'icon' => 'package'],
            ['label' => __('In Progress'), 'value' => $stats['active'], 'icon' => 'truck'],
            ['label' => __('Delivered'), 'value' => $stats['delivered'], 'icon' => 'check'],
        ];

        $quickLinks = [
            ['label' => __('Track Order'), 'hint' => __('Where is my order'), 'icon' => 'truck', 'url' => route('store.orders.track')],
            ['label' => __('Campaign'), 'hint' => __('Running Offers'), 'icon' => 'gift', 'url' => route('store.offers.index')],
            ['label' => __('Combo'), 'hint' => __('Bundle Deals'), 'icon' => 'package', 'url' => route('store.combos.index')],
        ];
    @endphp

    <x-store.breadcrumbs :items="$breadcrumbs" container="max-w-hero" />

    <div class="bg-surface py-6 lg:py-10">
        <div class="mx-auto max-w-hero space-y-5 px-4 lg:space-y-6">
            {{-- Profile hero --}}
            <section class="relative isolate animate-fade-up overflow-hidden rounded-2xl bg-linear-to-br from-accent via-accent to-accent-dark p-5 text-white shadow-lg shadow-accent/20 sm:p-7" aria-labelledby="account-heading">
                <span class="pointer-events-none absolute -top-16 -right-10 -z-10 size-56 rounded-full bg-white/10" aria-hidden="true"></span>
                <span class="pointer-events-none absolute -bottom-24 left-1/3 -z-10 size-64 rounded-full bg-white/5" aria-hidden="true"></span>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="grid size-16 shrink-0 place-items-center rounded-full bg-white text-xl font-bold text-accent shadow-md ring-4 ring-white/25 sm:size-20 sm:text-2xl">
                            @if ($initials !== '')
                                {{ $initials }}
                            @else
                                <x-store.icon name="user" class="size-8" />
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-white/75">{{ __('Welcome back') }}</p>
                            <h1 id="account-heading" class="truncate text-xl font-bold sm:text-2xl">{{ $user->name }}</h1>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-white/85">
                                @if ($user->phone)
                                    <span class="inline-flex items-center gap-1.5"><x-store.icon name="phone" class="size-3.5" /> {{ \App\Support\Localization::digits($user->phone) }}</span>
                                @endif
                                @if ($user->email)
                                    <span class="inline-flex min-w-0 items-center gap-1.5"><x-store.icon name="mail" class="size-3.5" /> <span class="truncate">{{ $user->email }}</span></span>
                                @endif
                                @if ($memberSince)
                                    <span class="inline-flex items-center gap-1.5"><x-store.icon name="calendar" class="size-3.5" /> {{ __('Member since :date', ['date' => $memberSince]) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-white/40 bg-white/10 px-4 py-2 text-xs font-semibold text-white backdrop-blur-sm transition hover:bg-white hover:text-accent active:scale-95">
                            <x-store.icon name="arrow-right" class="size-3.5" /> {{ __('Logout') }}
                        </button>
                    </form>
                </div>

                <div class="mt-6 grid grid-cols-3 gap-2 sm:gap-3">
                    @foreach ($statTiles as $tile)
                        <div class="rounded-xl bg-white/10 p-3 ring-1 ring-white/15 backdrop-blur-sm sm:flex sm:items-center sm:gap-3 sm:p-4">
                            <span class="hidden size-10 shrink-0 place-items-center rounded-lg bg-white/15 sm:grid"><x-store.icon :name="$tile['icon']" class="size-5" /></span>
                            <div class="min-w-0">
                                <p class="text-xl font-bold leading-none sm:text-2xl">{{ \App\Support\Localization::digits($tile['value']) }}</p>
                                <p class="mt-1 truncate text-[11px] text-white/80 sm:text-xs">{{ $tile['label'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-start lg:gap-6">
                <div class="space-y-5 lg:space-y-6">
                    {{-- Quick links --}}
                    <nav class="grid grid-cols-3 gap-2 sm:gap-3" aria-label="{{ __('Quick links') }}">
                        @foreach ($quickLinks as $link)
                            <a href="{{ $link['url'] }}" class="group flex flex-col items-center gap-2 rounded-xl border border-gray-200 bg-white p-3 text-center transition hover:-translate-y-0.5 hover:border-accent/40 hover:shadow-md sm:flex-row sm:p-4 sm:text-left">
                                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-accent/10 text-accent transition group-hover:bg-accent group-hover:text-white"><x-store.icon :name="$link['icon']" class="size-5" /></span>
                                <span class="min-w-0">
                                    <span class="block truncate text-xs font-semibold text-gray-900 sm:text-sm">{{ $link['label'] }}</span>
                                    <span class="hidden truncate text-[11px] text-gray-500 sm:block">{{ $link['hint'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </nav>

                    {{-- Orders --}}
                    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" aria-labelledby="orders-heading">
                        <header class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="grid size-9 place-items-center rounded-lg bg-accent/10 text-accent"><x-store.icon name="package" class="size-[18px]" /></span>
                                <div>
                                    <h2 id="orders-heading" class="text-sm font-bold text-gray-900 sm:text-base">{{ __('My Orders') }}</h2>
                                    <p class="text-[11px] text-gray-500">{{ __('Your recent purchases') }}</p>
                                </div>
                            </div>
                            <a href="{{ route('store.orders.track') }}" class="inline-flex items-center gap-1.5 rounded-full bg-accent/10 px-3 py-1.5 text-xs font-semibold text-accent transition hover:bg-accent hover:text-white">
                                <x-store.icon name="truck" class="size-3.5" /> {{ __('Track an order') }}
                            </a>
                        </header>

                        @if ($orders->isEmpty())
                            <div class="px-4 py-12 text-center sm:px-6">
                                <span class="mx-auto grid size-16 place-items-center rounded-full bg-surface text-gray-400"><x-store.icon name="cart" class="size-7" /></span>
                                <p class="mt-4 text-sm font-semibold text-gray-900">{{ __('You have not placed any orders yet.') }}</p>
                                <a href="{{ route('store.home') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-button px-5 py-2.5 text-sm font-semibold text-button-text transition hover:bg-button-hover active:scale-95">
                                    {{ __('Start shopping') }} <x-store.icon name="arrow-right" class="size-3.5" />
                                </a>
                            </div>
                        @else
                            <ul class="divide-y divide-gray-100">
                                @foreach ($orders as $order)
                                    <li class="flex items-center gap-3 px-4 py-4 transition hover:bg-gray-50/80 sm:gap-4 sm:px-6">
                                        <span class="hidden size-11 shrink-0 place-items-center rounded-lg bg-surface text-gray-500 sm:grid"><x-store.icon name="package" class="size-5" /></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-semibold text-gray-900">#{{ $order['number'] }}</p>
                                            <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-[11px] text-gray-500">
                                                <span class="inline-flex items-center gap-1"><x-store.icon name="calendar" class="size-3" /> {{ $order['placed_at'] }}</span>
                                                <span aria-hidden="true">·</span>
                                                <span>{{ trans_choice(':count item|:count items', $order['item_count'], ['count' => \App\Support\Localization::digits($order['item_count'])]) }}</span>
                                            </p>
                                        </div>
                                        <div class="flex shrink-0 flex-col items-end gap-1.5 sm:flex-row sm:items-center sm:gap-4">
                                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $statusTones[$order['tone']] ?? $statusTones['gray'] }}">{{ $order['status'] }}</span>
                                            <span class="min-w-16 text-right text-sm font-bold text-gray-900">{{ $order['total'] }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                <div class="space-y-5 lg:space-y-6">
                    {{-- Profile --}}
                    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="profile-heading">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 place-items-center rounded-lg bg-accent/10 text-accent"><x-store.icon name="user" class="size-[18px]" /></span>
                            <div>
                                <h2 id="profile-heading" class="text-sm font-bold text-gray-900 sm:text-base">{{ __('Profile') }}</h2>
                                <p class="text-[11px] text-gray-500">{{ __('Keep your contact details up to date') }}</p>
                            </div>
                        </div>

                        @if (session('status') === 'profile-information-updated')
                            <p class="mt-4 flex items-center gap-2 rounded-lg bg-savings/10 px-3 py-2 text-xs font-medium text-savings" role="status"><x-store.icon name="check" class="size-3.5" /> {{ __('Profile updated.') }}</p>
                        @endif

                        <form method="POST" action="{{ route('user-profile-information.update') }}" class="mt-5 space-y-4" data-submit-once>
                            @csrf
                            @method('PUT')

                            <x-store.form-field name="name" :label="__('Full Name')" bag="updateProfileInformation" :value="old('name', $user->name)" required maxlength="255" autocomplete="name" />
                            <x-store.form-field name="phone" :label="__('Mobile Number')" type="tel" bag="updateProfileInformation" :value="old('phone', $user->phone)" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" />
                            <x-store.form-field name="email" :label="__('Email')" type="email" bag="updateProfileInformation" :value="old('email', $user->email)" maxlength="255" autocomplete="email" />

                            <x-store.submit-button>{{ __('Save Profile') }}</x-store.submit-button>
                        </form>
                    </section>

                    {{-- Password --}}
                    <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6" aria-labelledby="password-heading">
                        <div class="flex items-center gap-3">
                            <span class="grid size-9 place-items-center rounded-lg bg-builder/10 text-builder"><x-store.icon name="shield" class="size-[18px]" /></span>
                            <div>
                                <h2 id="password-heading" class="text-sm font-bold text-gray-900 sm:text-base">{{ __('Change Password') }}</h2>
                                <p class="text-[11px] text-gray-500">{{ __('Use a strong password you do not use elsewhere') }}</p>
                            </div>
                        </div>

                        @if (session('status') === 'password-updated')
                            <p class="mt-4 flex items-center gap-2 rounded-lg bg-savings/10 px-3 py-2 text-xs font-medium text-savings" role="status"><x-store.icon name="check" class="size-3.5" /> {{ __('Password changed.') }}</p>
                        @endif

                        <form method="POST" action="{{ route('user-password.update') }}" class="mt-5 space-y-4" data-submit-once>
                            @csrf
                            @method('PUT')

                            <x-store.form-field name="current_password" :label="__('Current Password')" type="password" bag="updatePassword" required autocomplete="current-password" />
                            <x-store.form-field name="password" :label="__('New Password')" type="password" bag="updatePassword" required autocomplete="new-password" :hint="__('At least 8 characters.')" />
                            <x-store.form-field name="password_confirmation" :label="__('Confirm New Password')" type="password" bag="updatePassword" required autocomplete="new-password" />

                            <x-store.submit-button>{{ __('Change Password') }}</x-store.submit-button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-layouts.store>
