@props(['groups'])

@if ($groups === [])
    <p class="rounded-md bg-surface px-4 py-6 text-center text-sm text-gray-500">{{ __('Specifications will be added soon.') }}</p>
@else
    <div class="overflow-hidden rounded-md border border-gray-200">
        <table class="w-full text-left text-xs sm:text-[13px]">
            @foreach ($groups as $group)
                <tbody>
                    <tr>
                        <th colspan="2" scope="colgroup" class="bg-gray-200/70 px-3 py-2.5 font-semibold text-gray-900 sm:px-4">{{ $group['name'] }}</th>
                    </tr>
                    @foreach ($group['rows'] as $row)
                        <tr class="border-t border-gray-100 transition-colors hover:bg-gray-50">
                            <th scope="row" class="w-2/5 px-3 py-3 align-top font-normal text-gray-500 sm:w-1/3 sm:px-4">{{ $row['label'] }}</th>
                            <td class="px-3 py-3 font-medium text-gray-900 sm:px-4">
                                @foreach ($row['values'] as $value)
                                    <span class="block not-first:mt-2">{{ $value }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @endforeach
        </table>
    </div>
@endif
