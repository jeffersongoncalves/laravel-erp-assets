<?php

namespace JeffersonGoncalves\Erp\Assets;

use JeffersonGoncalves\Erp\Assets\Models\Contracts\AssetCategoryContract;
use JeffersonGoncalves\Erp\Assets\Models\Contracts\AssetContract;
use JeffersonGoncalves\Erp\Assets\Services\DepreciationService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ErpAssetsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'erp-assets';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations([
                'create_erp_asset_categories_table',
                'create_erp_assets_table',
                'create_erp_asset_depreciation_schedules_table',
                'create_erp_asset_movements_table',
                'create_erp_asset_repairs_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(DepreciationService::class);
    }

    public function packageBooted(): void
    {
        $this->registerModelBindings();
    }

    protected function registerModelBindings(): void
    {
        $bindings = [
            AssetCategoryContract::class => 'asset_category',
            AssetContract::class => 'asset',
        ];

        foreach ($bindings as $contract => $configKey) {
            $this->app->bind($contract, config("erp-assets.models.{$configKey}"));
        }
    }
}
