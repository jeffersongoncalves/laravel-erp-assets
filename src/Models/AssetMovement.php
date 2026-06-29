<?php

namespace JeffersonGoncalves\Erp\Assets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Erp\Assets\Enums\AssetMovementPurpose;
use JeffersonGoncalves\Erp\Assets\Support\ModelResolver;
use JeffersonGoncalves\Erp\Core\Concerns\HasCompany;
use JeffersonGoncalves\Erp\Core\Concerns\HasNamingSeries;
use JeffersonGoncalves\Erp\Core\Concerns\IsSubmittable;
use JeffersonGoncalves\Erp\Core\Contracts\SubmittableDocument;
use JeffersonGoncalves\Erp\Core\Enums\DocStatus;

/**
 * @property int $id
 * @property string|null $naming_series
 * @property int $asset_id
 * @property AssetMovementPurpose $purpose
 * @property Carbon $transaction_date
 * @property string|null $from_location
 * @property string|null $to_location
 * @property string|null $from_custodian
 * @property string|null $to_custodian
 * @property int|null $company_id
 * @property DocStatus $docstatus
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Asset|null $asset
 */
class AssetMovement extends Model implements SubmittableDocument
{
    use HasCompany;
    use HasFactory;
    use HasNamingSeries;
    use IsSubmittable;

    protected $fillable = [
        'naming_series',
        'asset_id',
        'purpose',
        'transaction_date',
        'from_location',
        'to_location',
        'from_custodian',
        'to_custodian',
        'company_id',
        'docstatus',
    ];

    protected $attributes = [
        'docstatus' => 0,
    ];

    protected $casts = [
        'purpose' => AssetMovementPurpose::class,
        'transaction_date' => 'datetime',
        'docstatus' => DocStatus::class,
    ];

    public function getTable(): string
    {
        return (config('erp-assets.table_prefix') ?? '').'asset_movements';
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::asset(), 'asset_id');
    }
}
