<?php

namespace JeffersonGoncalves\Erp\Assets\Support;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JeffersonGoncalves\Erp\Assets\Models\Contracts\AssetCategoryContract;
use JeffersonGoncalves\Erp\Assets\Models\Contracts\AssetContract;

class ModelResolver
{
    /** @var array<string, string> */
    protected static array $cache = [];

    /** @return class-string<Model&AssetCategoryContract> */
    public static function assetCategory(): string
    {
        return static::resolve('asset_category', AssetCategoryContract::class);
    }

    /** @return class-string<Model&AssetContract> */
    public static function asset(): string
    {
        return static::resolve('asset', AssetContract::class);
    }

    /** @return class-string<Model> */
    public static function assetDepreciationSchedule(): string
    {
        return static::resolve('asset_depreciation_schedule');
    }

    /** @return class-string<Model> */
    public static function assetMovement(): string
    {
        return static::resolve('asset_movement');
    }

    /** @return class-string<Model> */
    public static function assetRepair(): string
    {
        return static::resolve('asset_repair');
    }

    /**
     * @param  class-string|null  $contract
     * @return class-string
     *
     * @throws InvalidArgumentException
     */
    protected static function resolve(string $key, ?string $contract = null): string
    {
        if (isset(static::$cache[$key])) {
            return static::$cache[$key];
        }

        /** @var class-string|null $model */
        $model = config("erp-assets.models.{$key}");

        if (! $model || ! class_exists($model)) {
            throw new InvalidArgumentException(
                "Model class for [{$key}] does not exist: {$model}"
            );
        }

        if ($contract !== null && ! is_a($model, $contract, true)) {
            throw new InvalidArgumentException(
                "Model [{$model}] must implement [{$contract}]."
            );
        }

        return static::$cache[$key] = $model;
    }

    public static function flushCache(): void
    {
        static::$cache = [];
    }
}
