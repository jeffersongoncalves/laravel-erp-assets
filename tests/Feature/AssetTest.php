<?php

use JeffersonGoncalves\Erp\Assets\Enums\AssetStatus;
use JeffersonGoncalves\Erp\Assets\Enums\DepreciationMethod;
use JeffersonGoncalves\Erp\Assets\Models\Asset;

it('uses the configured table prefix', function () {
    expect((new Asset)->getTable())->toBe('erp_assets');
});

it('builds a straight-line schedule on submit', function () {
    $asset = Asset::factory()->create([
        'gross_purchase_amount' => 12000,
        'salvage_value' => 0,
        'total_number_of_depreciations' => 12,
        'frequency_of_depreciation' => 1,
        'depreciation_method' => DepreciationMethod::StraightLine,
    ]);

    $asset->submit();

    $rows = $asset->depreciationSchedules()->orderBy('schedule_date')->get();

    expect($rows)->toHaveCount(12)
        ->and(round((float) $rows->sum('depreciation_amount'), 2))->toBe(12000.0)
        ->and($asset->refresh()->status)->toBe(AssetStatus::Submitted);

    $previous = 0.0;
    foreach ($rows as $row) {
        expect((float) $row->accumulated_depreciation_amount)->toBeGreaterThan($previous);
        $previous = (float) $row->accumulated_depreciation_amount;
    }
});

it('subtracts the salvage value from the depreciable base', function () {
    $asset = Asset::factory()->create([
        'gross_purchase_amount' => 10000,
        'salvage_value' => 1000,
        'total_number_of_depreciations' => 9,
        'frequency_of_depreciation' => 1,
        'depreciation_method' => DepreciationMethod::StraightLine,
    ]);

    $asset->submit();

    expect(round((float) $asset->depreciationSchedules()->sum('depreciation_amount'), 2))->toBe(9000.0)
        ->and(round($asset->refresh()->value_after_depreciation, 2))->toBe(1000.0);
});

it('builds a declining-balance schedule that never crosses salvage', function () {
    $asset = Asset::factory()->create([
        'gross_purchase_amount' => 10000,
        'salvage_value' => 1000,
        'total_number_of_depreciations' => 5,
        'frequency_of_depreciation' => 1,
        'depreciation_method' => DepreciationMethod::DoubleDecliningBalance,
    ]);

    $asset->submit();

    $rows = $asset->depreciationSchedules()->orderBy('schedule_date')->get();

    expect($rows)->toHaveCount(5)
        ->and((float) $asset->refresh()->value_after_depreciation)->toBeGreaterThanOrEqual(1000.0);
});
