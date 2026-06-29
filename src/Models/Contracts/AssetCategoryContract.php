<?php

namespace JeffersonGoncalves\Erp\Assets\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface AssetCategoryContract
{
    public function assets(): HasMany;

    public function depreciationAccount(): BelongsTo;

    public function accumulatedDepreciationAccount(): BelongsTo;

    public function fixedAssetAccount(): BelongsTo;
}
