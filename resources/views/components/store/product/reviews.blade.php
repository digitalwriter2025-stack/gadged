@props(['reviews', 'rating' => 0, 'ratingCount' => 0, 'action' => null, 'ownReviewStatus' => null])

@php
    use App\Enums\ReviewStatus;

    $canWriteReview = $action !== null && ($ownReviewStatus === null || $ownReviewStatus === ReviewStatus::Rejected);
    $hasReviewErrors = $errors->hasAny(['rating', 'title', 'body']);
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 transition focus:border-builder focus:ring-2 focus:ring-builder/20 focus:outline-none aria-invalid:border-price';
    $labelClass = 'block text-xs font-medium text-gray-700';
    $errorClass = 'mt-1 text-[11px] text-price';
@endphp

<div class="flex flex-wrap items-center justify-between gap-2">
    @if ($ratingCount > 0)
        <p class="flex items-center gap-2 text-sm text-gray-700">
            <span class="text-2xl font-bold text-gray-900">{{ \App\Support\Localization::digits(number_format($rating, 1)) }}</span>
            <x-store.product.rating-stars :rating="$rating" />
            <span class="text-xs text-gray-500">({{ trans_choice(':count review|:count reviews', $ratingCount, ['count' => \App\Support\Localization::digits($ratingCount)]) }})</span>
        </p>
    @else
        <span></span>
    @endif

    @guest
        <a href="{{ route('login') }}" class="text-xs font-medium text-gray-600 transition hover:text-accent">{{ __('Login to write a review.') }}</a>
    @endguest
    @if ($ownReviewStatus === ReviewStatus::Approved)
        <span class="flex items-center gap-1 text-xs text-savings"><x-store.icon name="check" class="size-3.5" /> {{ __('You have reviewed this product.') }}</span>
    @endif
</div>

@auth
    @if (session('status') === 'review-submitted')
        <p class="mt-4 rounded-md bg-savings/10 px-3 py-2.5 text-xs leading-5 text-savings" role="status" data-review-notice>{{ __('Thanks for your review! It will appear here once our team approves it.') }}</p>
    @elseif ($ownReviewStatus === ReviewStatus::Pending)
        <p class="mt-4 rounded-md bg-amber-50 px-3 py-2.5 text-xs leading-5 text-amber-800" role="status" data-review-notice>{{ __('Your review is awaiting approval.') }}</p>
    @elseif ($ownReviewStatus === ReviewStatus::Rejected)
        <p class="mt-4 rounded-md bg-price/5 px-3 py-2.5 text-xs leading-5 text-gray-800" role="status" data-review-notice>{{ __('Your previous review was not approved. You can write a new one below.') }}</p>
    @endif

    @error('review')
        <p class="mt-4 rounded-md bg-price/5 px-3 py-2.5 text-xs leading-5 text-price" role="alert">{{ $message }}</p>
    @enderror

    @if ($canWriteReview)
        <details class="group/review mt-4 rounded-md border border-gray-200" @if ($hasReviewErrors) open @endif data-review-form>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-50 [&::-webkit-details-marker]:hidden">
                <span class="flex items-center gap-2"><x-store.icon name="star" class="size-4 text-amber-400" /> {{ __('Write a review') }}</span>
                <x-store.icon name="chevron-down" class="size-4 text-gray-500 transition duration-200 group-open/review:rotate-180" />
            </summary>

            <form method="POST" action="{{ $action }}" class="space-y-4 border-t border-gray-100 px-4 py-4" data-submit-once>
                @csrf

                <fieldset>
                    <legend class="{{ $labelClass }}">{{ __('Your rating') }} <span class="text-price">*</span></legend>
                    {{-- Stars run 5 → 1 in the DOM and are reversed visually, so a checked or hovered star lights every star after it. --}}
                    <div class="mt-1.5 flex w-fit flex-row-reverse justify-end gap-1 rounded-md has-focus-visible:ring-2 has-focus-visible:ring-builder/30">
                        @for ($star = 5; $star >= 1; $star--)
                            <input id="review-rating-{{ $star }}" type="radio" name="rating" value="{{ $star }}" class="peer sr-only" @checked((int) old('rating') === $star) required @error('rating') aria-invalid="true" aria-describedby="review-rating-error" @enderror>
                            <label for="review-rating-{{ $star }}" class="peer cursor-pointer text-gray-300 transition hover:text-amber-400 peer-checked:text-amber-400 peer-hover:text-amber-400">
                                <x-store.icon name="star" class="size-7" />
                                <span class="sr-only">{{ trans_choice(':count star|:count stars', $star, ['count' => \App\Support\Localization::digits($star)]) }}</span>
                            </label>
                        @endfor
                    </div>
                    @error('rating') <p id="review-rating-error" class="{{ $errorClass }}" role="alert">{{ $message }}</p> @enderror
                </fieldset>

                <div>
                    <label for="review-title" class="{{ $labelClass }}">{{ __('Review title') }}</label>
                    <input id="review-title" name="title" type="text" value="{{ old('title') }}" maxlength="150" placeholder="{{ __('e.g. Great battery life') }}" class="{{ $inputClass }}" @error('title') aria-invalid="true" aria-describedby="review-title-error" @enderror>
                    @error('title') <p id="review-title-error" class="{{ $errorClass }}" role="alert">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="review-body" class="{{ $labelClass }}">{{ __('Your review') }} <span class="text-price">*</span></label>
                    <textarea id="review-body" name="body" rows="4" required minlength="10" maxlength="2000" placeholder="{{ __('What did you like or dislike about this product?') }}" class="{{ $inputClass }} resize-y" @error('body') aria-invalid="true" aria-describedby="review-body-error" @enderror>{{ old('body') }}</textarea>
                    @error('body') <p id="review-body-error" class="{{ $errorClass }}" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-store.submit-button class="sm:w-auto">{{ __('Submit review') }}</x-store.submit-button>
                    <p class="text-[11px] text-gray-500">{{ __('Your review will be published after our team checks it.') }}</p>
                </div>
            </form>
        </details>
    @endif
@endauth

@if ($reviews === [])
    <div class="mt-4 rounded-md bg-surface px-4 py-10 text-center">
        <x-store.icon name="star" class="mx-auto size-8 text-gray-300" />
        <p class="mt-2 text-sm font-medium text-gray-800">{{ __('No reviews yet') }}</p>
        <p class="mt-1 text-xs text-gray-500">{{ __('Be the first to share your thoughts about this product.') }}</p>
    </div>
@else
    <ul class="mt-4 divide-y divide-gray-100">
        @foreach ($reviews as $review)
            <li class="py-4 first:pt-0">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <x-store.product.rating-stars :rating="$review['rating']" />
                    <span class="text-sm font-medium text-gray-900">{{ $review['author'] }}</span>
                    @if ($review['verified'])
                        <span class="flex items-center gap-0.5 text-[11px] font-medium text-savings"><x-store.icon name="check" class="size-3" /> {{ __('Verified purchase') }}</span>
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
