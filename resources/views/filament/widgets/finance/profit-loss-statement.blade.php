@php($statement = $this->getStatement())

<x-filament-widgets::widget>
    <x-filament::section
        heading="Profit & loss · লাভ-ক্ষতি"
        :description="$this->getPeriodLabel()"
        icon="heroicon-o-document-chart-bar"
    >
        <div class="gx-pl">
            @foreach ($statement['rows'] as $row)
                @if (in_array($row['kind'], ['heading', 'empty'], true))
                    <p class="gx-pl-{{ $row['kind'] }}">{{ $row['label'] }}</p>
                @else
                    <div @class(['gx-pl-row', 'gx-pl-'.$row['kind'], 'gx-pl-'.($row['tone'] ?? 'neutral')])>
                        <span>{{ $row['label'] }}</span>
                        <span class="gx-pl-amount">{{ $row['amount'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>

        @if ($statement['items_without_cost'] > 0)
            <p class="gx-pl-warning">
                <x-filament::icon icon="heroicon-m-exclamation-triangle" />
                <span>{{ $statement['items_without_cost'] }} sold {{ str('item')->plural($statement['items_without_cost']) }} had no cost price, so profit shows higher than it is. Add cost prices to the product variants.</span>
            </p>
        @endif

        @if ($statement['not_in_profit'] !== [])
            <div class="gx-pl-aside">
                <p class="gx-pl-heading">Not counted in profit</p>
                <p class="gx-pl-note">Stock purchases are already covered by the cost of products sold; owner investment and withdrawals move capital, not profit.</p>

                <div class="gx-pl">
                    @foreach ($statement['not_in_profit'] as $line)
                        <div class="gx-pl-row gx-pl-line gx-pl-neutral">
                            <span>{{ $line['label'] }}</span>
                            <span class="gx-pl-amount">{{ $line['amount'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
