@php
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;
    use App\Models\OrderStatusHistory;

    $histories = $getRecord()->statusHistories->reverse();
@endphp

@if ($histories->isEmpty())
    <p class="gx-timeline-meta">No activity recorded yet.</p>
@else
    <ol class="gx-timeline">
        @foreach ($histories as $history)
            @php
                $enum = $history->type === OrderStatusHistory::TYPE_PAYMENT ? PaymentStatus::class : OrderStatus::class;
                $to = $enum::tryFrom((string) $history->to_status);
                $from = $enum::tryFrom((string) $history->from_status);
            @endphp

            <li class="gx-timeline-item" data-color="{{ $to?->getColor() ?? 'gray' }}">
                <div class="gx-timeline-head">
                    @if ($history->type === OrderStatusHistory::TYPE_PAYMENT)
                        <span class="gx-timeline-kind">Payment</span>
                    @endif

                    @if ($from)
                        <x-filament::badge color="gray" size="sm">{{ $from->getLabel() }}</x-filament::badge>
                        <x-filament::icon icon="heroicon-m-arrow-long-right" class="gx-timeline-arrow" />
                    @endif

                    <x-filament::badge :color="$to?->getColor() ?? 'gray'" size="sm">
                        {{ $to?->getLabel() ?? $history->to_status }}
                    </x-filament::badge>
                </div>

                @if (filled($history->note))
                    <p class="gx-timeline-note">{{ $history->note }}</p>
                @endif

                <p class="gx-timeline-meta">
                    {{ $history->created_at->timezone(config('app.display_timezone'))->format('d M Y, h:i A') }}
                    @if ($history->user)
                        · {{ $history->user->name }}
                    @endif
                </p>
            </li>
        @endforeach
    </ol>
@endif
