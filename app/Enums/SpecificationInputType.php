<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Input type of a specification attribute.
 */
enum SpecificationInputType: string implements HasLabel
{
    case Text = 'text';
    case Number = 'number';
    case Select = 'select';
    case MultiSelect = 'multiselect';
    case Boolean = 'boolean';

    public function getLabel(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Number => 'Number',
            self::Select => 'Single choice',
            self::MultiSelect => 'Multiple choice',
            self::Boolean => 'Yes / No',
        };
    }
}
