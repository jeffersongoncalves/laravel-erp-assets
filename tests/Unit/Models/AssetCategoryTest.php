<?php

use JeffersonGoncalves\Erp\Accounting\Models\Account;
use JeffersonGoncalves\Erp\Assets\Enums\DepreciationMethod;
use JeffersonGoncalves\Erp\Assets\Models\Asset;
use JeffersonGoncalves\Erp\Assets\Models\AssetCategory;

it('uses the configured table prefix', function () {
    expect((new AssetCategory)->getTable())->toBe('erp_asset_categories');
});

it('casts the depreciation method to an enum', function () {
    $category = AssetCategory::factory()->create([
        'depreciation_method' => DepreciationMethod::WrittenDownValue,
    ]);

    expect($category->refresh()->depreciation_method)->toBe(DepreciationMethod::WrittenDownValue);
});

it('relates to its ledger accounts', function () {
    $account = Account::factory()->create();
    $category = AssetCategory::factory()->create(['depreciation_account_id' => $account->id]);

    expect($category->depreciationAccount->id)->toBe($account->id);
});

it('has many assets', function () {
    $category = AssetCategory::factory()->create();
    Asset::factory()->count(2)->create(['asset_category_id' => $category->id]);

    expect($category->assets)->toHaveCount(2);
});
