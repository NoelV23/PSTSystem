<?php

namespace App\Services;

use App\Models\CutRemainder;
use App\Models\Product;
use App\Support\MeasurementUnit;

class CutRemainderService
{
    public function itemHasCut(array $item): bool
    {
        return ($item['cut_length'] ?? null) > 0
            || ($item['cut_width'] ?? null) > 0
            || ($item['cut_height'] ?? null) > 0;
    }

    public function resolveCutMeasurementUnit(object $product, array $item): string
    {
        if (! empty($item['cut_measurement_unit'])) {
            return (string) MeasurementUnit::normalize($item['cut_measurement_unit']);
        }

        $allowed = MeasurementUnit::allowedCutUnitsForProduct($product);

        return $allowed[0] ?? MeasurementUnit::productLinearStorageUnit($product);
    }

    public function createFromCutItem(Product $product, int $branchId, array $item): ?CutRemainder
    {
        $cutUnit = $this->resolveCutMeasurementUnit($product, $item);
        $storageUnit = MeasurementUnit::productLinearStorageUnit($product);

        $remainderData = [
            'product_id' => $product->id,
            'branch_id' => $branchId,
            'location_note' => $item['location_note'] ?? null,
            'status' => $item['status'] ?? 'available',
            'cut_measurement_unit' => $cutUnit,
        ];

        if (isset($item['cut_length']) && $item['cut_length'] > 0 && $product->default_length) {
            $cutLength = (float) $item['cut_length'];
            $defaultLength = MeasurementUnit::convertLinear(
                (float) $product->default_length,
                $storageUnit,
                $cutUnit
            );

            if ($cutLength < $defaultLength) {
                $remainderData['length_remaining'] = round($defaultLength - $cutLength, 2);
            }
        }

        if ((isset($item['cut_width']) && $item['cut_width'] > 0) &&
            (isset($item['cut_height']) && $item['cut_height'] > 0) &&
            $product->default_width && $product->default_height) {

            $cutWidth = (float) $item['cut_width'];
            $cutHeight = (float) $item['cut_height'];
            $defaultWidth = MeasurementUnit::convertLinear((float) $product->default_width, $storageUnit, $cutUnit);
            $defaultHeight = MeasurementUnit::convertLinear((float) $product->default_height, $storageUnit, $cutUnit);

            if ($cutWidth < $defaultWidth || $cutHeight < $defaultHeight) {
                $remainderData['width_remaining'] = round(max(0, $defaultWidth - $cutWidth), 2);
                $remainderData['height_remaining'] = round(max(0, $defaultHeight - $cutHeight), 2);
            }
        }

        if (isset($remainderData['length_remaining']) || isset($remainderData['width_remaining'])) {
            if ($remainderData['status'] === 'discarded') {
                $remainderData['discard_reason'] = $item['discard_reason'] ?? null;
                $remainderData['discarded_at'] = now();
            }

            return CutRemainder::create($remainderData);
        }

        return null;
    }

    /**
     * @return list<CutRemainder>
     */
    public function createFromPurchaseLine(Product $product, int $branchId, array $item): array
    {
        if (! $this->itemHasCut($item)) {
            return [];
        }

        $qty = max(1, (int) floor((float) ($item['quantity'] ?? 1)));
        $created = [];

        for ($i = 0; $i < $qty; $i++) {
            $remainder = $this->createFromCutItem($product, $branchId, $item);
            if ($remainder) {
                $created[] = $remainder;
            }
        }

        return $created;
    }
}
