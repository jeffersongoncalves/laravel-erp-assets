<?php

namespace JeffersonGoncalves\Erp\Assets\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Erp\Assets\Enums\AssetStatus;
use JeffersonGoncalves\Erp\Assets\Enums\DepreciationMethod;
use JeffersonGoncalves\Erp\Assets\Models\Contracts\AssetContract;
use JeffersonGoncalves\Erp\Assets\Services\DepreciationService;
use JeffersonGoncalves\Erp\Assets\Support\ModelResolver;
use JeffersonGoncalves\Erp\Core\Concerns\HasCompany;
use JeffersonGoncalves\Erp\Core\Concerns\HasNamingSeries;
use JeffersonGoncalves\Erp\Core\Concerns\IsSubmittable;
use JeffersonGoncalves\Erp\Core\Contracts\PostsToLedger;
use JeffersonGoncalves\Erp\Core\Contracts\SubmittableDocument;
use JeffersonGoncalves\Erp\Core\Enums\DocStatus;

/**
 * A fixed asset in the register.
 *
 * Submitting an asset is a non-monetary event: instead of posting a purchase
 * entry it builds the full depreciation schedule (see {@see DepreciationService}).
 * Depreciation is posted to the general ledger later, period by period, with the
 * asset acting as the GL voucher; cancelling reverses any posted depreciation.
 *
 * @property int $id
 * @property string|null $naming_series
 * @property string $asset_name
 * @property string|null $item_code
 * @property int $asset_category_id
 * @property int|null $company_id
 * @property string|null $location
 * @property string|null $custodian
 * @property Carbon|null $purchase_date
 * @property Carbon|null $available_for_use_date
 * @property float $gross_purchase_amount
 * @property float $opening_accumulated_depreciation
 * @property DepreciationMethod|null $depreciation_method
 * @property int $total_number_of_depreciations
 * @property int $frequency_of_depreciation
 * @property float $salvage_value
 * @property AssetStatus $status
 * @property float $value_after_depreciation
 * @property DocStatus $docstatus
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AssetCategory $assetCategory
 * @property-read Collection<int, AssetDepreciationSchedule> $depreciationSchedules
 * @property-read Collection<int, AssetMovement> $movements
 * @property-read Collection<int, AssetRepair> $repairs
 */
class Asset extends Model implements AssetContract, PostsToLedger, SubmittableDocument
{
    use HasCompany;
    use HasFactory;
    use HasNamingSeries;
    use IsSubmittable;

    protected $fillable = [
        'naming_series',
        'asset_name',
        'item_code',
        'asset_category_id',
        'company_id',
        'location',
        'custodian',
        'purchase_date',
        'available_for_use_date',
        'gross_purchase_amount',
        'opening_accumulated_depreciation',
        'depreciation_method',
        'total_number_of_depreciations',
        'frequency_of_depreciation',
        'salvage_value',
        'status',
        'value_after_depreciation',
        'docstatus',
    ];

    protected $attributes = [
        'gross_purchase_amount' => 0,
        'opening_accumulated_depreciation' => 0,
        'total_number_of_depreciations' => 0,
        'frequency_of_depreciation' => 12,
        'salvage_value' => 0,
        'status' => 'Draft',
        'value_after_depreciation' => 0,
        'docstatus' => 0,
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'available_for_use_date' => 'date',
        'gross_purchase_amount' => 'float',
        'opening_accumulated_depreciation' => 'float',
        'depreciation_method' => DepreciationMethod::class,
        'total_number_of_depreciations' => 'integer',
        'frequency_of_depreciation' => 'integer',
        'salvage_value' => 'float',
        'status' => AssetStatus::class,
        'value_after_depreciation' => 'float',
        'docstatus' => DocStatus::class,
    ];

    public function getTable(): string
    {
        return (config('erp-assets.table_prefix') ?? '').'assets';
    }

    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::assetCategory(), 'asset_category_id');
    }

    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(ModelResolver::assetDepreciationSchedule(), 'asset_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(ModelResolver::assetMovement(), 'asset_id');
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(ModelResolver::assetRepair(), 'asset_id');
    }

    /**
     * On submit an asset does not post a purchase entry; it builds the
     * depreciation schedule that depreciation postings will later draw from.
     */
    public function postLedgerEntries(): void
    {
        app(DepreciationService::class)->buildSchedule($this);
    }

    public function reverseLedgerEntries(): void
    {
        app(DepreciationService::class)->reverseDepreciation($this);
    }
}
