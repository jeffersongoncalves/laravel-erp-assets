<?php

namespace JeffersonGoncalves\Erp\Assets\Enums;

enum DepreciationMethod: string
{
    case StraightLine = 'Straight Line';
    case WrittenDownValue = 'Written Down Value';
    case DoubleDecliningBalance = 'Double Declining Balance';

    public function label(): string
    {
        return __('erp-assets::erp-assets.depreciation_method.'.$this->value);
    }
}
