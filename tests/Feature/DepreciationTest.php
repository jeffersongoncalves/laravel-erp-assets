<?php

use Illuminate\Support\Carbon;
use JeffersonGoncalves\Erp\Accounting\Models\Account;
use JeffersonGoncalves\Erp\Accounting\Models\GlEntry;
use JeffersonGoncalves\Erp\Assets\Enums\AssetStatus;
use JeffersonGoncalves\Erp\Assets\Models\Asset;
use JeffersonGoncalves\Erp\Assets\Models\AssetCategory;
use JeffersonGoncalves\Erp\Assets\Services\DepreciationService;
use JeffersonGoncalves\Erp\Core\Enums\DocStatus;
use JeffersonGoncalves\Erp\Core\Models\Company;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->depreciationExpense = Account::factory()->create(['company_id' => $this->company->id]);
    $this->accumulated = Account::factory()->create(['company_id' => $this->company->id]);
    $this->category = AssetCategory::factory()->create([
        'depreciation_account_id' => $this->depreciationExpense->id,
        'accumulated_depreciation_account_id' => $this->accumulated->id,
    ]);
});

function depreciableAsset(Company $company, AssetCategory $category): Asset
{
    $asset = Asset::factory()->create([
        'company_id' => $company->id,
        'asset_category_id' => $category->id,
        'gross_purchase_amount' => 12000,
        'salvage_value' => 0,
        'total_number_of_depreciations' => 12,
        'frequency_of_depreciation' => 1,
        'available_for_use_date' => '2024-01-01',
    ]);

    $asset->submit();

    return $asset;
}

it('posts balanced gl entries for every due period', function () {
    $asset = depreciableAsset($this->company, $this->category);

    app(DepreciationService::class)->postDepreciation($asset, Carbon::parse('2030-01-01'));

    expect(GlEntry::query()->count())->toBe(24)
        ->and((float) GlEntry::query()->sum('debit'))->toBe((float) GlEntry::query()->sum('credit'))
        ->and((float) GlEntry::query()->where('account_id', $this->depreciationExpense->id)->sum('debit'))->toBe(12000.0)
        ->and((float) GlEntry::query()->where('account_id', $this->accumulated->id)->sum('credit'))->toBe(12000.0)
        ->and($asset->depreciationSchedules()->where('gl_posted', true)->count())->toBe(12)
        ->and($asset->refresh()->status)->toBe(AssetStatus::FullyDepreciated);
});

it('flags only the periods due on or before the cut-off and goes partial', function () {
    $asset = depreciableAsset($this->company, $this->category);

    app(DepreciationService::class)->postDepreciation($asset, Carbon::parse('2024-06-30'));

    expect(GlEntry::query()->where('is_cancelled', false)->count())->toBe(10)
        ->and($asset->depreciationSchedules()->where('gl_posted', true)->count())->toBe(5)
        ->and($asset->refresh()->status)->toBe(AssetStatus::PartiallyDepreciated);
});

it('is idempotent and never double-posts a period', function () {
    $asset = depreciableAsset($this->company, $this->category);
    $service = app(DepreciationService::class);

    $service->postDepreciation($asset, Carbon::parse('2030-01-01'));
    $service->postDepreciation($asset, Carbon::parse('2030-01-01'));

    expect(GlEntry::query()->count())->toBe(24);
});

it('reverses every posted period on cancel', function () {
    $asset = depreciableAsset($this->company, $this->category);
    app(DepreciationService::class)->postDepreciation($asset, Carbon::parse('2030-01-01'));

    $asset->cancel();

    expect($asset->docstatus)->toBe(DocStatus::Cancelled)
        ->and(GlEntry::query()->where('is_cancelled', false)->count())->toBe(0)
        ->and((float) GlEntry::query()->sum('debit'))->toBe((float) GlEntry::query()->sum('credit'))
        ->and($asset->depreciationSchedules()->where('gl_posted', true)->count())->toBe(0);
});
