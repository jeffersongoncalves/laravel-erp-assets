<?php

namespace JeffersonGoncalves\Erp\Assets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JeffersonGoncalves\Erp\Assets\Enums\AssetStatus;
use JeffersonGoncalves\Erp\Assets\Enums\DepreciationMethod;
use JeffersonGoncalves\Erp\Assets\Models\Asset;
use JeffersonGoncalves\Erp\Assets\Models\AssetCategory;
use JeffersonGoncalves\Erp\Core\Models\Company;

/** @extends Factory<Asset> */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'asset_name' => fake()->words(2, true),
            'asset_category_id' => AssetCategory::factory(),
            'company_id' => Company::factory(),
            'location' => fake()->city(),
            'purchase_date' => '2024-01-01',
            'available_for_use_date' => '2024-01-01',
            'gross_purchase_amount' => 12000,
            'opening_accumulated_depreciation' => 0,
            'depreciation_method' => DepreciationMethod::StraightLine,
            'total_number_of_depreciations' => 12,
            'frequency_of_depreciation' => 1,
            'salvage_value' => 0,
            'status' => AssetStatus::Draft,
        ];
    }
}
