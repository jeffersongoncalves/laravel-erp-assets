<?php

use JeffersonGoncalves\Erp\Assets\Models\AssetRepair;
use JeffersonGoncalves\Erp\Core\Enums\DocStatus;

it('uses the configured table prefix', function () {
    expect((new AssetRepair)->getTable())->toBe('erp_asset_repairs');
});

it('submits a repair', function () {
    $repair = AssetRepair::factory()->create([
        'repair_status' => 'Completed',
        'repair_cost' => 250.50,
    ]);

    $repair->submit();

    expect($repair->docstatus)->toBe(DocStatus::Submitted)
        ->and($repair->isSubmitted())->toBeTrue()
        ->and($repair->refresh()->repair_cost)->toBe(250.5);
});

it('can be cancelled after submission', function () {
    $repair = AssetRepair::factory()->create();
    $repair->submit();

    $repair->cancel();

    expect($repair->docstatus)->toBe(DocStatus::Cancelled);
});
