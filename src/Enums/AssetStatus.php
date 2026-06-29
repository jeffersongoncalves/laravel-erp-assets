<?php

namespace JeffersonGoncalves\Erp\Assets\Enums;

enum AssetStatus: string
{
    case Draft = 'Draft';
    case Submitted = 'Submitted';
    case PartiallyDepreciated = 'Partially Depreciated';
    case FullyDepreciated = 'Fully Depreciated';
    case Scrapped = 'Scrapped';
    case Sold = 'Sold';

    public function label(): string
    {
        return __('erp-assets::erp-assets.asset_status.'.$this->value);
    }
}
