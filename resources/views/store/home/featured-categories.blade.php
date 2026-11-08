<section class="bg-surface">
    <div class="mx-auto max-w-hero px-4 pt-7 pb-5 lg:px-3">
        <x-store.section-heading :title="$section['title'] ?? 'Featured Categories'" :subtitle="$section['subtitle']" />

        <ul class="mt-5 grid grid-cols-3 gap-2.5 min-[480px]:grid-cols-4 sm:gap-3 md:grid-cols-6 lg:grid-cols-8 lg:gap-x-[17px] lg:gap-y-3" data-reveal-group>
            @php
                $categoryIconBackgrounds = ['bg-red-50', 'bg-gray-100'];
                $mobileCategoryLimit = max(3, intdiv(count($section['categories']), 3) * 3);
            @endphp
            @foreach ($section['categories'] as $category)
                <li data-reveal @class(['max-[480px]:hidden' => $loop->iteration > $mobileCategoryLimit])>
                    <a href="{{ $category['url'] }}" class="group flex h-[92px] flex-col items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-1 transition duration-300 ease-out-expo hover:-translate-y-1 hover:border-accent/50 hover:shadow-[0_10px_24px_-10px_rgba(223,1,0,0.35)] active:scale-95 lg:h-[85px] lg:rounded-md">
                        <span class="flex size-12 items-center justify-center rounded-full {{ $categoryIconBackgrounds[$loop->index % count($categoryIconBackgrounds)] }} transition duration-300 group-hover:scale-110 lg:size-11">
                            @if ($category['image'])
                                <img src="{{ $category['image'] }}" alt="" width="32" height="32" loading="lazy" class="size-8 object-contain transition duration-300 group-hover:animate-wiggle lg:size-7">
                            @else
                                <span class="text-lg font-bold text-gray-700" aria-hidden="true">{{ mb_substr($category['name'], 0, 1) }}</span>
                            @endif
                        </span>
                        <span class="text-center text-[11px] leading-tight font-medium text-gray-800 transition group-hover:text-accent lg:text-[10px]">{{ $category['name'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
