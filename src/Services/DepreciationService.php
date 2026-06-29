<?php

namespace JeffersonGoncalves\Erp\Assets\Services;

use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use JeffersonGoncalves\Erp\Accounting\Services\GeneralLedgerService;
use JeffersonGoncalves\Erp\Assets\Enums\AssetStatus;
use JeffersonGoncalves\Erp\Assets\Enums\DepreciationMethod;
use JeffersonGoncalves\Erp\Assets\Models\Asset;
use JeffersonGoncalves\Erp\Assets\Support\ModelResolver;

/**
 * The depreciation engine for fixed assets.
 *
 * Building the schedule is a pure projection performed on submit; posting
 * depreciation hands each due period to the accounting {@see GeneralLedgerService}
 * with the asset as the GL voucher (voucherable morph). Cancelling an asset
 * reverses every posted period and clears the schedule's posted flags.
 */
class DepreciationService
{
    /**
     * Project the full depreciation schedule for an asset.
     *
     * Generates one `asset_depreciation_schedules` row per planned period and
     * stores the resulting book value on the asset. No money moves here.
     */
    public function buildSchedule(Asset $asset): void
    {
        $category = $asset->assetCategory;

        $method = $asset->depreciation_method
            ?? $category->depreciation_method
            ?? DepreciationMethod::StraightLine;

        $count = $asset->total_number_of_depreciations > 0
            ? $asset->total_number_of_depreciations
            : (int) $category->total_number_of_depreciations;

        $frequency = $asset->frequency_of_depreciation > 0
            ? $asset->frequency_of_depreciation
            : (int) $category->frequency_of_depreciation;

        $gross = (float) $asset->gross_purchase_amount;
        $salvage = (float) $asset->salvage_value;
        $opening = (float) $asset->opening_accumulated_depreciation;

        $start = $asset->available_for_use_date
            ?? $asset->purchase_date
            ?? Carbon::now();

        $scheduleModel = ModelResolver::assetDepreciationSchedule();
        $accumulated = $opening;

        if ($count > 0 && $frequency > 0) {
            foreach ($this->depreciationAmounts($method, $gross, $salvage, $opening, $count) as $index => $amount) {
                $accumulated += $amount;

                $scheduleModel::query()->create([
                    'asset_id' => $asset->getKey(),
                    'schedule_date' => $start->copy()->addMonths($frequency * ($index + 1)),
                    'depreciation_amount' => $amount,
                    'accumulated_depreciation_amount' => $accumulated,
                ]);
            }
        }

        $valueAfter = $gross - $accumulated;

        // The asset is already submitted (immutable to model saves), so the
        // derived columns are persisted through the query builder.
        ModelResolver::asset()::query()
            ->whereKey($asset->getKey())
            ->update([
                'status' => AssetStatus::Submitted->value,
                'value_after_depreciation' => $valueAfter,
                'depreciation_method' => $method->value,
                'total_number_of_depreciations' => $count,
                'frequency_of_depreciation' => $frequency,
            ]);

        $asset->setAttribute('status', AssetStatus::Submitted);
        $asset->setAttribute('value_after_depreciation', $valueAfter);
        $asset->setAttribute('depreciation_method', $method);
        $asset->setAttribute('total_number_of_depreciations', $count);
        $asset->setAttribute('frequency_of_depreciation', $frequency);
        $asset->syncOriginal();
    }

    /**
     * Post every depreciation period due on or before $upto to the ledger.
     *
     * Each due, not-yet-posted period posts a balanced entry — debit the
     * category depreciation account, credit accumulated depreciation — and the
     * asset's status advances to partially or fully depreciated.
     */
    public function postDepreciation(Asset $asset, Carbon $upto): void
    {
        $category = $asset->assetCategory;
        $debitAccount = $category->depreciation_account_id;
        $creditAccount = $category->accumulated_depreciation_account_id;

        if ($debitAccount === null || $creditAccount === null) {
            throw new DomainException('Asset category is missing its depreciation accounts');
        }

        $scheduleModel = ModelResolver::assetDepreciationSchedule();

        /** @var Collection<int, Model> $dueRows */
        $dueRows = $scheduleModel::query()
            ->where('asset_id', $asset->getKey())
            ->where('gl_posted', false)
            ->whereDate('schedule_date', '<=', $upto)
            ->orderBy('schedule_date')
            ->get();

        $ledger = app(GeneralLedgerService::class);

        foreach ($dueRows as $row) {
            $amount = (float) $row->getAttribute('depreciation_amount');

            // The ledger reads the posting date from the voucher; each period
            // posts as of its own schedule date without persisting it.
            $asset->setAttribute('posting_date', $row->getAttribute('schedule_date'));

            $ledger->post($asset, [
                [
                    'account_id' => $debitAccount,
                    'debit' => $amount,
                    'credit' => 0,
                    'remarks' => 'Depreciation',
                ],
                [
                    'account_id' => $creditAccount,
                    'debit' => 0,
                    'credit' => $amount,
                    'remarks' => 'Depreciation',
                ],
            ]);

            $row->setAttribute('gl_posted', true);
            $row->setAttribute('journal_entry_posted', true);
            $row->save();
        }

        $this->refreshDepreciationStatus($asset);
    }

    /**
     * Reverse every posted depreciation period and clear the schedule flags.
     */
    public function reverseDepreciation(Asset $asset): void
    {
        app(GeneralLedgerService::class)->reverse($asset);

        ModelResolver::assetDepreciationSchedule()::query()
            ->where('asset_id', $asset->getKey())
            ->update([
                'gl_posted' => false,
                'journal_entry_posted' => false,
            ]);
    }

    /**
     * Compute the per-period depreciation amounts for a method.
     *
     * @return list<float>
     */
    private function depreciationAmounts(DepreciationMethod $method, float $gross, float $salvage, float $opening, int $count): array
    {
        $depreciable = $gross - $salvage;

        if ($method === DepreciationMethod::StraightLine) {
            $perPeriod = $depreciable / $count;
            $amounts = [];
            $allocated = 0.0;

            for ($period = 1; $period <= $count; $period++) {
                if ($period === $count) {
                    // Absorb rounding drift in the final period so the periods
                    // sum to exactly the depreciable amount.
                    $amounts[] = round($depreciable - $allocated, 9);

                    continue;
                }

                $amount = round($perPeriod, 9);
                $amounts[] = $amount;
                $allocated += $amount;
            }

            return $amounts;
        }

        // Declining-balance methods apply a constant rate to the residual book
        // value each period, never crossing below the salvage value.
        $rate = ($method === DepreciationMethod::DoubleDecliningBalance ? 2.0 : 1.0) / $count;

        $bookValue = $gross - $opening;
        $amounts = [];

        for ($period = 1; $period <= $count; $period++) {
            $amount = round($bookValue * $rate, 9);

            if ($bookValue - $amount < $salvage) {
                $amount = round(max($bookValue - $salvage, 0.0), 9);
            }

            $amounts[] = $amount;
            $bookValue -= $amount;
        }

        return $amounts;
    }

    private function refreshDepreciationStatus(Asset $asset): void
    {
        $scheduleModel = ModelResolver::assetDepreciationSchedule();
        $base = $scheduleModel::query()->where('asset_id', $asset->getKey());

        $total = (clone $base)->count();
        $posted = (clone $base)->where('gl_posted', true)->count();

        if ($total === 0 || $posted === 0) {
            return;
        }

        $status = $posted >= $total
            ? AssetStatus::FullyDepreciated
            : AssetStatus::PartiallyDepreciated;

        ModelResolver::asset()::query()
            ->whereKey($asset->getKey())
            ->update(['status' => $status->value]);

        $asset->setAttribute('status', $status);
        $asset->syncOriginal();
    }
}
