<div class="filament-hidden">

![Laravel ERP Assets](https://raw.githubusercontent.com/jeffersongoncalves/laravel-erp-assets/main/art/jeffersongoncalves-laravel-erp-assets.png)

</div>

# Laravel ERP Assets

ERP assets — fixed asset register, depreciation and movements for the Laravel ERP ecosystem.

This package is the fixed-assets module of the Laravel ERP ecosystem. It depends on [`jeffersongoncalves/laravel-erp-core`](https://github.com/jeffersongoncalves/laravel-erp-core) for the submittable-document foundation and on [`jeffersongoncalves/laravel-erp-accounting`](https://github.com/jeffersongoncalves/laravel-erp-accounting) for the general ledger that absorbs depreciation postings.

## Features

- **Asset Register** — Fixed assets built on the core `IsSubmittable` lifecycle (`Draft → Submitted → Cancelled`), grouped under asset categories that carry the depreciation defaults and ledger accounts
- **Depreciation Engine** — A single `DepreciationService` projects the full depreciation schedule on submit (Straight Line, Written Down Value, Double Declining Balance) and posts each due period to the general ledger
- **Native GL Integration** — Depreciation posts a balanced entry (debit depreciation expense, credit accumulated depreciation) through the accounting `GeneralLedgerService`, with the asset acting as the GL voucher
- **Movements & Repairs** — Submittable asset movements (issue / receipt / transfer) and asset repair documents
- **Customizable Models** — Override any model via config (ModelResolver pattern); `Asset` and `AssetCategory` ship swappable contracts
- **Translations** — English and Brazilian Portuguese

## Compatibility

| Package | PHP | Laravel |
|---------|-----|---------|
| `^1.0`  | `^8.2` | `^11.0 \| ^12.0 \| ^13.0` |

## Installation

```bash
composer require jeffersongoncalves/laravel-erp-assets
```

Publish and run the migrations (the core and accounting package migrations must be published too):

```bash
php artisan vendor:publish --tag="erp-core-migrations"
php artisan vendor:publish --tag="erp-accounting-migrations"
php artisan vendor:publish --tag="erp-assets-migrations"
php artisan migrate
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag="erp-assets-config"
```

## Depreciation Flow

1. **Submit an asset** — `Asset::submit()` builds the `asset_depreciation_schedules` rows from the chosen method, period count and frequency. No money moves at this point (`postLedgerEntries()` is wired to schedule generation, not a purchase entry).
2. **Post depreciation** — `app(DepreciationService::class)->postDepreciation($asset, $upto)` posts every period due on or before `$upto` to the general ledger and advances the asset to `Partially Depreciated` / `Fully Depreciated`.
3. **Cancel** — Cancelling the asset reverses every posted period (mirror GL rows, net zero) and clears the schedule's posted flags.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Jefferson Simão Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
