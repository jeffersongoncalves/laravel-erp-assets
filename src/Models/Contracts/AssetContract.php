<?php

namespace JeffersonGoncalves\Erp\Assets\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface AssetContract
{
    public function assetCategory(): BelongsTo;

    public function depreciationSchedules(): HasMany;
}
