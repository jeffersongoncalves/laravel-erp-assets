<?php

namespace JeffersonGoncalves\Erp\Assets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Erp\Assets\Support\ModelResolver;

/**
 * @property int $id
 * @property int $asset_id
 * @property Carbon $schedule_date
 * @property float $depreciation_amount
 * @property float $accumulated_depreciation_amount
 * @property bool $journal_entry_posted
 * @property bool $gl_posted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Asset|null $asset
 */
class AssetDepreciationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'schedule_date',
        'depreciation_amount',
        'accumulated_depreciation_amount',
        'journal_entry_posted',
        'gl_posted',
    ];

    protected $attributes = [
        'depreciation_amount' => 0,
        'accumulated_depreciation_amount' => 0,
        'journal_entry_posted' => false,
        'gl_posted' => false,
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'depreciation_amount' => 'float',
        'accumulated_depreciation_amount' => 'float',
        'journal_entry_posted' => 'boolean',
        'gl_posted' => 'boolean',
    ];

    public function getTable(): string
    {
        return (config('erp-assets.table_prefix') ?? '').'asset_depreciation_schedules';
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::asset(), 'asset_id');
    }
}
