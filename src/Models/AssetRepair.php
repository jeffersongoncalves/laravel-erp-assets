<?php

namespace JeffersonGoncalves\Erp\Assets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
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
 * @property Carbon|null $failure_date
 * @property string $repair_status
 * @property float $repair_cost
 * @property string|null $description
 * @property int|null $company_id
 * @property DocStatus $docstatus
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Asset|null $asset
 */
class AssetRepair extends Model implements SubmittableDocument
{
    use HasCompany;
    use HasFactory;
    use HasNamingSeries;
    use IsSubmittable;

    protected $fillable = [
        'naming_series',
        'asset_id',
        'failure_date',
        'repair_status',
        'repair_cost',
        'description',
        'company_id',
        'docstatus',
    ];

    protected $attributes = [
        'repair_status' => 'Pending',
        'repair_cost' => 0,
        'docstatus' => 0,
    ];

    protected $casts = [
        'failure_date' => 'datetime',
        'repair_cost' => 'float',
        'docstatus' => DocStatus::class,
    ];

    public function getTable(): string
    {
        return (config('erp-assets.table_prefix') ?? '').'asset_repairs';
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::asset(), 'asset_id');
    }
}
