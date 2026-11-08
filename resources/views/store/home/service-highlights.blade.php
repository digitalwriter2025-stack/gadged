<section class="bg-hero">
    <div class="mx-auto max-w-hero px-3 py-3 sm:px-4 lg:px-3">
        <ul class="grid grid-cols-2 gap-2 rounded-md bg-white px-3 py-2.5 text-xs font-medium text-gray-800 shadow-sm md:flex md:items-center md:justify-center md:divide-x md:divide-gray-200 md:gap-0 lg:text-[13px]">
            @foreach ($section['items'] as $item)
                <li class="md:px-6">
                    <a @if ($item['url']) href="{{ $item['url'] }}" @endif class="flex items-center gap-2 transition hover:text-accent">
                        <x-store.icon :name="$item['icon']" class="size-4 text-accent" />
                        <span>
                            {{ $item['title'] }}
                            @if ($item['subtitle'])
                                <span class="block text-[10px] font-normal text-gray-500">{{ $item['subtitle'] }}</span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
