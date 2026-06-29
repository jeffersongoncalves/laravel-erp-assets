<?php

namespace JeffersonGoncalves\Erp\Assets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JeffersonGoncalves\Erp\Assets\Enums\DepreciationMethod;
use JeffersonGoncalves\Erp\Assets\Models\AssetCategory;

/** @extends Factory<AssetCategory> */
class AssetCategoryFactory extends Factory
{
    protected $model = AssetCategory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'depreciation_method' => DepreciationMethod::StraightLine,
            'total_number_of_depreciations' => 12,
            'frequency_of_depreciation' => 1,
        ];
    }
}
