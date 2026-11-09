<?php

namespace App\Support\Storefront;

use App\Support\Money;
use Illuminate\Support\Arr;

/**
 * The removable "chips" shown above a product listing: one per ticked filter
 * option plus one for a custom price range. Each chip links to the current
 * listing with only that filter dropped, so removing one works without JS.
 *
 * @phpstan-type ActiveFilterChip array{group: string, label: string, url: string}
 */
final class ActiveListingFilters
{
    /**
     * @param  list<array{name: string, input: string, options: list<array{value: string, label: string, checked: bool}>}>  $groups
     * @param  array{min: ?int, max: ?int}  $filters
     * @param  array{min: int, max: int}  $price
     * @param  array<string, mixed>  $query  The listing's current query string.
     * @return list<ActiveFilterChip>
     */
    public static function chips(array $groups, array $filters, array $price, string $url, array $query): array
    {
        $query = Arr::except($query, ['page']);
        $chips = [];

        foreach ($groups as $group) {
            $path = self::queryPath($group['input']);

            foreach ($group['options'] as $option) {
                if (! $option['checked']) {
                    continue;
                }

                $remaining = $query;
                $values = array_values(array_diff(Arr::wrap(Arr::get($remaining, $path, [])), [$option['value']]));

                if ($values === []) {
                    Arr::forget($remaining, $path);
                } else {
                    Arr::set($remaining, $path, $values);
                }

                $chips[] = ['group' => $group['name'], 'label' => $option['label'], 'url' => self::url($url, $remaining)];
            }
        }

        if ($filters['min'] !== null || $filters['max'] !== null) {
            $chips[] = [
                'group' => __('Price'),
                'label' => Money::format($filters['min'] ?? $price['min']).' – '.Money::format($filters['max'] ?? $price['max']),
                'url' => self::url($url, Arr::except($query, ['min', 'max'])),
            ];
        }

        return $chips;
    }

    /**
     * Turn a checkbox name into a dot path: "spec[memory-type][]" becomes "spec.memory-type".
     */
    private static function queryPath(string $input): string
    {
        return str_replace(['[]', '[', ']'], ['', '.', ''], $input);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private static function url(string $url, array $query): string
    {
        $queryString = Arr::query($query);

        return $queryString === '' ? $url : $url.'?'.$queryString;
    }
}
