@props(['items', 'alignRight' => false])

<div @class([
    'mega-menu invisible absolute top-full z-40 min-w-[220px] translate-y-1 rounded-b-md border-t-2 border-accent bg-white py-2 opacity-0 shadow-xl ring-1 ring-black/5 transition duration-200',
    'group-hover/nav:visible group-hover/nav:translate-y-0 group-hover/nav:opacity-100 group-focus-within/nav:visible group-focus-within/nav:translate-y-0 group-focus-within/nav:opacity-100',
    'right-0' => $alignRight,
    'left-0' => ! $alignRight,
]) data-mega-panel>
    <ul>
        @foreach ($items as $item)
            <li class="group/sub relative">
                <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-4 px-4 py-2.5 text-[13px] whitespace-nowrap text-gray-800 transition hover:bg-surface hover:text-accent">
                    {{ $item['label'] }}
                    @if (($item['children'] ?? []) !== [])
                        <x-store.icon name="chevron-right" class="size-3.5 text-gray-400 group-hover/sub:text-accent" />
                    @endif
                </a>

                @if (($item['children'] ?? []) !== [])
                    <ul @class([
                        'invisible absolute top-0 min-w-[200px] rounded-md bg-white py-2 opacity-0 shadow-xl ring-1 ring-black/5 transition duration-200 group-hover/sub:visible group-hover/sub:opacity-100 group-focus-within/sub:visible group-focus-within/sub:opacity-100',
                        'right-full' => $alignRight,
                        'left-full' => ! $alignRight,
                    ])>
                        @foreach ($item['children'] as $child)
                            <li>
                                <a href="{{ $child['url'] }}" class="block px-4 py-2.5 text-[13px] whitespace-nowrap text-gray-700 transition hover:bg-surface hover:text-accent">{{ $child['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
</div>
