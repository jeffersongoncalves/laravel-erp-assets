<?php

namespace JeffersonGoncalves\Erp\Assets\Enums;

enum AssetMovementPurpose: string
{
    case Issue = 'Issue';
    case Receipt = 'Receipt';
    case Transfer = 'Transfer';

    public function label(): string
    {
        return __('erp-assets::erp-assets.asset_movement_purpose.'.$this->value);
    }
}
