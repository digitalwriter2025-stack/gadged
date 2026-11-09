<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use Throwable;

/**
 * An inclusive range of local shop days (app.display_timezone) for reports.
 * Timestamp columns are stored in UTC, so queries use startUtc() / endUtc().
 */
final readonly class ReportPeriod
{
    public const TODAY = 'today';

    public const YESTERDAY = 'yesterday';

    public const LAST_7_DAYS = 'last_7_days';

    public const THIS_MONTH = 'this_month';

    public const LAST_MONTH = 'last_month';

    public const THIS_YEAR = 'this_year';

    public const CUSTOM = 'custom';

    public function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
    ) {}

    /**
     * @return array<string, string>
     */
    public static function presets(): array
    {
        return [
            self::TODAY => 'Today',
            self::YESTERDAY => 'Yesterday',
            self::LAST_7_DAYS => 'Last 7 days',
            self::THIS_MONTH => 'This month',
            self::LAST_MONTH => 'Last month',
            self::THIS_YEAR => 'This year',
            self::CUSTOM => 'Custom range',
        ];
    }

    /**
     * Builds the period from the finance page filters, defaulting to this month.
     *
     * @param  array{period?: ?string, start_date?: ?string, end_date?: ?string}|null  $filters
     */
    public static function fromFilters(?array $filters): self
    {
        $today = CarbonImmutable::now(self::timezone())->startOfDay();

        return match ($filters['period'] ?? self::THIS_MONTH) {
            self::TODAY => new self($today, $today),
            self::YESTERDAY => new self($today->subDay(), $today->subDay()),
            self::LAST_7_DAYS => new self($today->subDays(6), $today),
            self::LAST_MONTH => new self($today->subMonthNoOverflow()->startOfMonth(), $today->subMonthNoOverflow()->endOfMonth()->startOfDay()),
            self::THIS_YEAR => new self($today->startOfYear(), $today),
            self::CUSTOM => self::custom($filters['start_date'] ?? null, $filters['end_date'] ?? null, $today),
            default => new self($today->startOfMonth(), $today),
        };
    }

    public function startUtc(): CarbonImmutable
    {
        return $this->start->startOfDay()->utc();
    }

    /**
     * Exclusive upper bound: the start of the day after the last day.
     */
    public function endUtc(): CarbonImmutable
    {
        return $this->end->addDay()->startOfDay()->utc();
    }

    public function days(): int
    {
        return (int) $this->start->diffInDays($this->end) + 1;
    }

    /**
     * Charts switch from daily to monthly buckets for ranges longer than two months.
     */
    public function isMonthly(): bool
    {
        return $this->days() > 62;
    }

    public function label(): string
    {
        if ($this->start->isSameDay($this->end)) {
            return $this->start->format('d M Y');
        }

        return $this->start->format($this->start->isSameYear($this->end) ? 'd M' : 'd M Y').' – '.$this->end->format('d M Y');
    }

    private static function custom(?string $start, ?string $end, CarbonImmutable $today): self
    {
        $startDay = self::parseDay($start) ?? $today->startOfMonth();
        $endDay = self::parseDay($end) ?? $today;

        return $startDay->greaterThan($endDay) ? new self($endDay, $startDay) : new self($startDay, $endDay);
    }

    private static function parseDay(?string $value): ?CarbonImmutable
    {
        if (blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value, self::timezone())->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    private static function timezone(): string
    {
        return (string) config('app.display_timezone', config('app.timezone'));
    }
}
