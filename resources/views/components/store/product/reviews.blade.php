@props(['reviews', 'rating' => 0, 'ratingCount' => 0])

<div class="flex flex-wrap items-center justify-between gap-2">
    @if ($ratingCount > 0)
        <p class="flex items-center gap-2 text-sm text-gray-700">
            <span class="text-2xl font-bold text-gray-900">{{ number_format($rating, 1) }}</span>
            <x-store.product.rating-stars :rating="$rating" />
            <span class="text-xs text-gray-500">({{ $ratingCount }} {{ Str::plural('review', $ratingCount) }})</span>
        </p>
    @else
        <span></span>
    @endif

    @auth
        <span class="text-xs text-gray-500">Reviews open after your order is delivered.</span>
    @else
        <a href="{{ route('login') }}" class="text-xs font-medium text-gray-600 transition hover:text-accent">Login to write a review.</a>
    @endauth
</div>

@if ($reviews === [])
    <div class="mt-4 rounded-md bg-surface px-4 py-10 text-center">
        <x-store.icon name="star" class="mx-auto size-8 text-gray-300" />
        <p class="mt-2 text-sm font-medium text-gray-800">No reviews yet</p>
        <p class="mt-1 text-xs text-gray-500">Be the first to share your thoughts about this product.</p>
    </div>
@else
    <ul class="mt-4 divide-y divide-gray-100">
        @foreach ($reviews as $review)
            <li class="py-4 first:pt-0">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <x-store.product.rating-stars :rating="$review['rating']" />
                    <span class="text-sm font-medium text-gray-900">{{ $review['author'] }}</span>
                    @if ($review['verified'])
                        <span class="flex items-center gap-0.5 text-[11px] font-medium text-savings"><x-store.icon name="check" class="size-3" /> Verified purchase</span>
                    @endif
                    <time class="text-[11px] text-gray-400">{{ $review['date'] }}</time>
                </div>
                @if ($review['title'])
                    <p class="mt-1.5 text-sm font-semibold text-gray-800">{{ $review['title'] }}</p>
                @endif
                @if ($review['body'])
                    <p class="mt-1 text-[13px] leading-6 text-gray-600">{{ $review['body'] }}</p>
                @endif
            </li>
        @endforeach
    </ul>
@endif
