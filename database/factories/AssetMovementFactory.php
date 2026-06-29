<?php

namespace JeffersonGoncalves\Erp\Assets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JeffersonGoncalves\Erp\Assets\Enums\AssetMovementPurpose;
use JeffersonGoncalves\Erp\Assets\Models\Asset;
use JeffersonGoncalves\Erp\Assets\Models\AssetMovement;
use JeffersonGoncalves\Erp\Core\Models\Company;

/** @extends Factory<AssetMovement> */
class AssetMovementFactory extends Factory
{
    protected $model = AssetMovement::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'purpose' => AssetMovementPurpose::Transfer,
            'transaction_date' => now(),
            'from_location' => fake()->city(),
            'to_location' => fake()->city(),
            'company_id' => Company::factory(),
        ];
    }
}
