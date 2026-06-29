<?php

use JeffersonGoncalves\Erp\Assets\Enums\AssetMovementPurpose;
use JeffersonGoncalves\Erp\Assets\Models\AssetMovement;
use JeffersonGoncalves\Erp\Core\Enums\DocStatus;

it('uses the configured table prefix', function () {
    expect((new AssetMovement)->getTable())->toBe('erp_asset_movements');
});

it('casts the purpose to an enum', function () {
    $movement = AssetMovement::factory()->create(['purpose' => AssetMovementPurpose::Issue]);

    expect($movement->refresh()->purpose)->toBe(AssetMovementPurpose::Issue);
});

it('submits a movement', function () {
    $movement = AssetMovement::factory()->create();

    $movement->submit();

    expect($movement->docstatus)->toBe(DocStatus::Submitted)
        ->and($movement->isSubmitted())->toBeTrue();
});

it('refuses to modify a submitted movement', function () {
    $movement = AssetMovement::factory()->create();
    $movement->submit();

    $movement->to_location = 'Warehouse B';

    expect(fn () => $movement->save())
        ->toThrow(DomainException::class, 'Cannot modify a submitted document');
});
