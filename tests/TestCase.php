<?php

namespace JeffersonGoncalves\Erp\Assets\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffersonGoncalves\Erp\Accounting\ErpAccountingServiceProvider;
use JeffersonGoncalves\Erp\Assets\ErpAssetsServiceProvider;
use JeffersonGoncalves\Erp\Core\ErpCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            foreach (['Assets', 'Accounting', 'Core'] as $module) {
                $factory = 'JeffersonGoncalves\\Erp\\'.$module.'\\Database\\Factories\\'.class_basename($modelName).'Factory';

                if (class_exists($factory)) {
                    return $factory;
                }
            }

            return 'JeffersonGoncalves\\Erp\\Assets\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            ErpCoreServiceProvider::class,
            ErpAccountingServiceProvider::class,
            ErpAssetsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $coreConfig = $this->erpDepPath('laravel-erp-core').'/config/erp-core.php';
        if (file_exists($coreConfig)) {
            $app['config']->set('erp-core', require $coreConfig);
        }

        $accountingConfig = $this->erpDepPath('laravel-erp-accounting').'/config/erp-accounting.php';
        if (file_exists($accountingConfig)) {
            $app['config']->set('erp-accounting', require $accountingConfig);
        }

        $configPath = __DIR__.'/../config/erp-assets.php';
        if (file_exists($configPath)) {
            $app['config']->set('erp-assets', require $configPath);
        }
    }

    protected function defineDatabaseMigrations(): void
    {
        $tempPath = sys_get_temp_dir().'/laravel-erp-assets-migrations';

        if (is_dir($tempPath)) {
            array_map('unlink', (array) glob($tempPath.'/*.php'));
        } else {
            mkdir($tempPath, 0755, true);
        }

        $corePath = $this->erpDepPath('laravel-erp-core').'/database/migrations';
        $accountingPath = $this->erpDepPath('laravel-erp-accounting').'/database/migrations';
        $packagePath = __DIR__.'/../database/migrations';

        // Foreign-key-safe order. loadMigrationsFrom sorts by filename, so each
        // stub is copied with a numeric prefix that preserves dependency order
        // across the core, accounting and this package.
        $ordered = array_merge(
            array_map(fn (string $name) => [$corePath, $name], $this->coreMigrations()),
            array_map(fn (string $name) => [$accountingPath, $name], $this->accountingMigrations()),
            array_map(fn (string $name) => [$packagePath, $name], $this->packageMigrations()),
        );

        foreach ($ordered as $index => [$path, $name]) {
            $stub = $path.'/'.$name.'.php.stub';

            if (file_exists($stub)) {
                copy($stub, sprintf('%s/%04d_%s.php', $tempPath, $index, $name));
            }
        }

        $this->loadMigrationsFrom($tempPath);
    }

    /** @return list<string> */
    protected function coreMigrations(): array
    {
        return [
            'create_erp_companies_table',
            'create_erp_currencies_table',
            'create_erp_currency_exchanges_table',
            'create_erp_uoms_table',
            'create_erp_uom_conversions_table',
            'create_erp_fiscal_years_table',
            'create_erp_departments_table',
            'create_erp_designations_table',
            'create_erp_brands_table',
            'create_erp_terms_and_conditions_table',
            'create_erp_addresses_table',
            'create_erp_contacts_table',
            'create_erp_naming_series_table',
        ];
    }

    /** @return list<string> */
    protected function accountingMigrations(): array
    {
        return [
            'create_erp_accounts_table',
            'create_erp_cost_centers_table',
            'create_erp_payment_terms_table',
            'create_erp_modes_of_payment_table',
            'create_erp_tax_templates_table',
            'create_erp_tax_template_taxes_table',
            'create_erp_banks_table',
            'create_erp_bank_accounts_table',
            'create_erp_budgets_table',
            'create_erp_budget_accounts_table',
            'create_erp_gl_entries_table',
            'create_erp_journal_entries_table',
            'create_erp_journal_entry_accounts_table',
            'create_erp_payment_entries_table',
            'create_erp_sales_invoices_table',
            'create_erp_sales_invoice_items_table',
            'create_erp_sales_invoice_taxes_table',
            'create_erp_purchase_invoices_table',
            'create_erp_purchase_invoice_items_table',
            'create_erp_purchase_invoice_taxes_table',
            'create_erp_period_closing_vouchers_table',
            'create_erp_bank_transactions_table',
        ];
    }

    /** @return list<string> */
    protected function packageMigrations(): array
    {
        return [
            'create_erp_asset_categories_table',
            'create_erp_assets_table',
            'create_erp_asset_depreciation_schedules_table',
            'create_erp_asset_movements_table',
            'create_erp_asset_repairs_table',
        ];
    }

    /**
     * Resolve a sibling ERP package directory.
     *
     * Works both standalone (dependency installed under vendor/) and inside the
     * monorepo (sibling under packages/, where directories drop the laravel-erp- prefix).
     */
    private function erpDepPath(string $package): string
    {
        $vendor = __DIR__.'/../vendor/jeffersongoncalves/'.$package;

        if (is_dir($vendor)) {
            return $vendor;
        }

        return __DIR__.'/../../'.str_replace('laravel-erp-', '', $package);
    }
}
