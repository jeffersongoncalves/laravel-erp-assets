<?php

namespace JeffersonGoncalves\Erp\Assets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JeffersonGoncalves\Erp\Assets\Models\Asset;
use JeffersonGoncalves\Erp\Assets\Models\AssetRepair;
use JeffersonGoncalves\Erp\Core\Models\Company;

/** @extends Factory<AssetRepair> */
class AssetRepairFactory extends Factory
{
    protected $model = AssetRepair::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'failure_date' => now(),
            'repair_status' => 'Pending',
            'repair_cost' => fake()->randomFloat(2, 50, 500),
            'description' => fake()->sentence(),
            'company_id' => Company::factory(),
        ];
    }
}
