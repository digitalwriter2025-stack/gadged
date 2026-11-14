<footer class="bg-linear-to-b from-footer to-[color-mix(in_srgb,var(--color-footer),white_4%)] text-white">
    <div class="mx-auto max-w-store px-4 pt-8 pb-5 lg:pt-6">
        <div class="grid sm:grid-cols-2 sm:gap-8 lg:grid-cols-[1.05fr_0.9fr_0.9fr_1fr] lg:gap-0 lg:divide-x lg:divide-white/10" data-reveal-group>
            <section class="pb-4 sm:pb-0 lg:pr-6" data-reveal>
                <h2 class="text-lg font-semibold lg:text-xl">{{ __('Contact Us') }}</h2>
                <ul class="mt-3 space-y-3 text-[15px] font-medium !text-white sm:text-sm">
                    @if ($contact['hotline'])
                        <li>
                            <a href="tel:{{ $contact['hotline_tel'] }}" class="group flex items-center gap-2 !text-white hover:underline">
                                <x-store.icon name="phone" class="size-4 shrink-0 transition group-hover:animate-wiggle" /> {{ __('Hotline') }}: {{ $contact['hotline'] }}
                            </a>
                        </li>
                    @endif
                    @if ($contact['email'])
                        <li>
                            <a href="mailto:{{ $contact['email'] }}" class="group flex items-center gap-2 break-all !text-white hover:underline">
                                <x-store.icon name="mail" class="size-4 shrink-0 transition group-hover:animate-wiggle" /> {{ __('Email') }}: {{ $contact['email'] }}
                            </a>
                        </li>
                    @endif
                    @if ($contact['address'])
                        <li class="flex items-start gap-2 !text-white">
                            <x-store.icon name="map-pin" class="mt-0.5 size-4 shrink-0" /> <span class="whitespace-pre-line">{{ $contact['address'] }}</span>
                        </li>
                    @endif
                </ul>

                @if ($socialLinks !== [])
                    <ul class="mt-4 flex flex-wrap gap-2" aria-label="{{ __('Social media') }}">
                        @foreach ($socialLinks as $social)
                            <li>
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="{{ $social['background'] }} grid size-9 place-items-center rounded-full transition duration-300 ease-spring hover:-translate-y-1 hover:scale-110 hover:shadow-lg active:scale-95 lg:size-8" aria-label="{{ $social['label'] }}">
                                    <x-store.icon :name="$social['icon']" class="size-4" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @php
                    $footerButtons = array_filter([
                        ['map-pin', __('Our Location'), $contact['map_url']],
                        ['chat', __('Complain Box'), $contact['complain_url']],
                    ], fn (array $button): bool => filled($button[2]));
                @endphp
                @if ($footerButtons !== [])
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($footerButtons as [$icon, $label, $url])
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 rounded-sm border !border-white px-3 py-2 text-xs font-medium !text-white transition hover:-translate-y-0.5 hover:!bg-white hover:!text-footer lg:px-3 lg:py-1.5 lg:text-[13px]">
                                <x-store.icon :name="$icon" class="size-4" /> {{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            @foreach ($footerMenus as $menu)
                <details data-footer-accordion class="footer-accordion group border-t border-white/10 sm:border-0 lg:px-6" data-reveal>
                    <summary class="flex cursor-pointer list-none items-center justify-between py-3.5 sm:cursor-default sm:py-0">
                        <h2 class="text-lg font-semibold lg:text-xl">{{ $menu['title'] }}</h2>
                        <x-store.icon name="chevron-right" class="size-4 text-gray-400 transition duration-300 group-open:rotate-90 sm:hidden" />
                    </summary>
                    <ul class="space-y-1 pb-3 text-[15px] text-gray-200 sm:mt-3 sm:space-y-2.5 sm:pb-0 sm:text-sm">
                        @foreach ($menu['links'] as $link)
                            <li><a href="{{ $link['url'] }}" @if ($link['new_tab']) target="_blank" rel="noopener" @endif class="inline-block py-1 transition duration-200 hover:translate-x-1 hover:text-footer-link sm:py-0">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </details>
            @endforeach

            @if ($affiliations !== [])
                <section class="border-t border-white/10 pt-4 sm:border-0 sm:pt-0 lg:pl-6" data-reveal>
                    <h2 class="text-lg font-semibold lg:text-xl">{{ __('Affiliation') }}</h2>
                    <div class="mt-3 max-w-[216px] space-y-2">
                        @php [$featuredAffiliation, $otherAffiliations] = [$affiliations[0], array_slice($affiliations, 1)]; @endphp
                        <a @if ($featuredAffiliation['url']) href="{{ $featuredAffiliation['url'] }}" target="_blank" rel="noopener" @endif class="grid h-[56px] place-items-center rounded-sm bg-white px-3 transition duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                            <img src="{{ $featuredAffiliation['image'] }}" alt="{{ $featuredAffiliation['name'] }}" width="150" height="53" loading="lazy" class="h-11 w-auto">
                        </a>
                        @if ($otherAffiliations !== [])
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ($otherAffiliations as $logo)
                                    <a @if ($logo['url']) href="{{ $logo['url'] }}" target="_blank" rel="noopener" @endif class="grid h-[46px] place-items-center rounded-sm bg-white transition duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                                        <img src="{{ $logo['image'] }}" alt="{{ $logo['name'] }}" width="38" height="38" loading="lazy" class="size-9 object-contain">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </div>

        <div class="mt-6 flex flex-col items-center gap-3 border-t border-white/10 pt-4 text-center lg:grid lg:grid-cols-3 lg:text-left">
            <p class="text-xs text-gray-300 lg:text-[13px]">{{ $footer['copyright'] }}</p>
            <p class="text-xs text-gray-400 lg:text-center lg:text-[13px]">
                @if ($footer['developer_name'])
                    {{ __('Designed and Developed By') }}
                    <a @if ($footer['developer_url']) href="{{ $footer['developer_url'] }}" target="_blank" rel="noopener" @endif class="font-semibold text-footer-link hover:underline">{{ $footer['developer_name'] }}</a>
                @endif
            </p>
            @if ($footer['payment_methods_image'])
                <img src="{{ $footer['payment_methods_image'] }}" alt="{{ __('Accepted payment methods') }}" width="231" height="26" loading="lazy" class="h-[30px] w-auto max-w-full object-contain lg:justify-self-end">
            @endif
        </div>
    </div>
</footer>
