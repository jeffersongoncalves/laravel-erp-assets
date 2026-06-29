<?php

use JeffersonGoncalves\Erp\Assets\Models\Asset;
use JeffersonGoncalves\Erp\Assets\Models\AssetCategory;
use JeffersonGoncalves\Erp\Assets\Models\AssetDepreciationSchedule;
use JeffersonGoncalves\Erp\Assets\Models\AssetMovement;
use JeffersonGoncalves\Erp\Assets\Models\AssetRepair;

return [
    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix applied to all tables created by the package. This is shared with
    | laravel-erp-core and laravel-erp-accounting so that foreign keys across
    | the ERP ecosystem resolve against a single set of prefixed tables. Set
    | to null to disable.
    |
    */
    'table_prefix' => 'erp_',

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Models used by the package. Can be overridden to extend the default
    | behavior. Swappable models that ship a contract must implement it
    | (see src/Models/Contracts/).
    |
    */
    'models' => [
        'asset_category' => AssetCategory::class,
        'asset' => Asset::class,
        'asset_depreciation_schedule' => AssetDepreciationSchedule::class,
        'asset_movement' => AssetMovement::class,
        'asset_repair' => AssetRepair::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | Default depreciation cadence applied to assets and categories when no
    | explicit value is supplied: the number of months between two consecutive
    | depreciation postings.
    |
    */
    'default_frequency_of_depreciation' => 12,
];
