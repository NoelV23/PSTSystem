<?php

namespace App\Support;

class MeasurementUnit
{
    public static function normalize(?string $unit): ?string
    {
        if ($unit === null || trim($unit) === '') {
            return null;
        }

        $u = strtolower(trim($unit));

        return match ($u) {
            'inch', 'inches', 'in' => 'inches',
            'foot', 'feet', 'ft' => 'ft',
            'millimeter', 'millimeters', 'mm' => 'mm',
            'centimeter', 'centimeters', 'cm' => 'cm',
            'meter', 'meters', 'm' => 'm',
            'sq ft', 'sqft', 'square feet', 'square foot' => 'sq ft',
            default => trim($unit),
        };
    }

    public static function displayLabel(?string $unit): string
    {
        return match (self::normalize($unit)) {
            'inches' => 'inches',
            'ft' => 'ft',
            'mm' => 'mm',
            'cm' => 'cm',
            'm' => 'm',
            'sq ft' => 'sq ft',
            default => (string) ($unit ?? ''),
        };
    }

    /**
     * Unit used for default_length / default_width / default_height on the product.
     * Sheet products (measurement_unit sq ft) store linear sheet dimensions in feet.
     */
    public static function productLinearStorageUnit(object $product): string
    {
        $mu = self::normalize($product->measurement_unit ?? null);

        if ($mu === 'sq ft') {
            return 'ft';
        }

        if ($mu && ! in_array($mu, ['kg', 'g', 'liter', 'ml', 'pail', 'gallon'], true)) {
            return $mu;
        }

        return 'ft';
    }

    /** @return list<string> */
    public static function allowedCutUnitsForProduct(object $product): array
    {
        $storage = self::productLinearStorageUnit($product);
        $mu = self::normalize($product->measurement_unit ?? null);
        $units = [$storage];

        if ($mu && $mu !== $storage) {
            $units[] = $mu;
        }

        if ($mu === 'sq ft' || $storage === 'ft') {
            $units[] = 'inches';
            $units[] = 'ft';
        } elseif ($storage === 'ft' || $mu === 'ft') {
            $units[] = 'inches';
        } elseif ($storage === 'inches' || $mu === 'inches') {
            $units[] = 'ft';
        } elseif (in_array($storage, ['mm', 'cm', 'm'], true)) {
            $units[] = 'inches';
            $units[] = 'ft';
        }

        $normalized = [];
        foreach ($units as $unit) {
            $n = self::normalize($unit);
            if ($n && $n !== 'sq ft' && ! in_array($n, $normalized, true)) {
                $normalized[] = $n;
            }
        }

        return $normalized ?: ['ft'];
    }

    public static function convertLinear(float $value, ?string $from, ?string $to): float
    {
        $from = self::normalize($from);
        $to = self::normalize($to);

        if ($from === null || $to === null || $from === $to) {
            return $value;
        }

        $inches = self::toInches($value, $from);

        return self::fromInches($inches, $to);
    }

    public static function remainderUnit(object $remainder, object $product): string
    {
        if (! empty($remainder->cut_measurement_unit)) {
            return (string) self::normalize($remainder->cut_measurement_unit);
        }

        return self::productLinearStorageUnit($product);
    }

    private static function toInches(float $value, string $from): float
    {
        return match (self::normalize($from)) {
            'inches' => $value,
            'ft' => $value * 12,
            'mm' => $value / 25.4,
            'cm' => $value / 2.54,
            'm' => $value * 39.3700787,
            default => $value,
        };
    }

    private static function fromInches(float $inches, string $to): float
    {
        return match (self::normalize($to)) {
            'inches' => $inches,
            'ft' => $inches / 12,
            'mm' => $inches * 25.4,
            'cm' => $inches * 2.54,
            'm' => $inches / 39.3700787,
            default => $inches,
        };
    }
}
