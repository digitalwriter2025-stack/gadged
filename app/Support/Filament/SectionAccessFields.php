<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Enums\Permission;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Grid;

/**
 * One checkbox group per admin section (Products, Orders, Settings…), stored as
 * `access.<section> => [permission, …]` in the form state.
 */
class SectionAccessFields
{
    public const STATE_PATH = 'access';

    /**
     * @param  array<string, array<string, string>>  $sections  section => [permission value => ability label]
     */
    public static function make(array $sections, bool|Closure $isDisabled = false): Grid
    {
        $fields = [];

        foreach ($sections as $section => $abilities) {
            $fields[] = CheckboxList::make(self::STATE_PATH.'.'.$section)
                ->label(Permission::sectionLabel($section))
                ->options($abilities)
                ->bulkToggleable()
                ->disabled($isDisabled);
        }

        return Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema($fields);
    }

    /**
     * @param  iterable<string>  $permissionNames
     * @return array<string, list<string>>
     */
    public static function group(iterable $permissionNames): array
    {
        $grouped = [];

        foreach ($permissionNames as $name) {
            $permission = Permission::tryFrom($name);

            if ($permission !== null) {
                $grouped[$permission->section()][] = $permission->value;
            }
        }

        return $grouped;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public static function flatten(array $data): array
    {
        $permissions = [];

        foreach ((array) ($data[self::STATE_PATH] ?? []) as $abilities) {
            array_push($permissions, ...array_values((array) $abilities));
        }

        return array_values(array_intersect(array_unique($permissions), Permission::values()));
    }
}
