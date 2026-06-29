<?php

namespace JeffersonGoncalves\Erp\Assets\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Erp\Accounting\Support\ModelResolver as AccountingModelResolver;
use JeffersonGoncalves\Erp\Assets\Enums\DepreciationMethod;
use JeffersonGoncalves\Erp\Assets\Models\Contracts\AssetCategoryContract;
use JeffersonGoncalves\Erp\Assets\Support\ModelResolver;

/**
 * @property int $id
 * @property string $name
 * @property DepreciationMethod $depreciation_method
 * @property int $total_number_of_depreciations
 * @property int $frequency_of_depreciation
 * @property int|null $depreciation_account_id
 * @property int|null $accumulated_depreciation_account_id
 * @property int|null $fixed_asset_account_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Asset> $assets
 */
class AssetCategory extends Model implements AssetCategoryContract
{
    use HasFactory;

    protected $fillable = [
        'name',
        'depreciation_method',
        'total_number_of_depreciations',
        'frequency_of_depreciation',
        'depreciation_account_id',
        'accumulated_depreciation_account_id',
        'fixed_asset_account_id',
    ];

    protected $attributes = [
        'depreciation_method' => 'Straight Line',
        'total_number_of_depreciations' => 0,
        'frequency_of_depreciation' => 12,
    ];

    protected $casts = [
        'depreciation_method' => DepreciationMethod::class,
        'total_number_of_depreciations' => 'integer',
        'frequency_of_depreciation' => 'integer',
    ];

    public function getTable(): string
    {
        return (config('erp-assets.table_prefix') ?? '').'asset_categories';
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ModelResolver::asset(), 'asset_category_id');
    }

    public function depreciationAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingModelResolver::account(), 'depreciation_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingModelResolver::account(), 'accumulated_depreciation_account_id');
    }

    public function fixedAssetAccount(): BelongsTo
    {
        return $this->belongsTo(AccountingModelResolver::account(), 'fixed_asset_account_id');
    }
}
