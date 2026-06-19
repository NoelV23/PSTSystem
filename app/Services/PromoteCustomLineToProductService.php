<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;

class PromoteCustomLineToProductService
{
    /**
     * Find an existing catalog match or create a product from a custom PO/SQ line.
     */
    public function findOrCreateFromCustomLine(array $item, int $categoryId): Product
    {
        $name = trim((string) ($item['custom_item_name'] ?? $item['description'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Custom line needs an item name to add to catalog.');
        }

        Category::findOrFail($categoryId);

        $color = $this->nullableString($item['custom_color'] ?? null);
        $thickness = $this->nullableString($item['custom_thickness'] ?? null);
        $dims = $this->parseCustomMeasurement($item['custom_measurement'] ?? null);

        $existing = $this->findMatchingProduct($name, $color, $thickness, $dims);
        if ($existing) {
            return $existing;
        }

        return Product::create([
            'name' => $name,
            'variant_group_label' => $name,
            'sku' => $this->generateSku($categoryId),
            'category_id' => $categoryId,
            'base_unit' => $dims['base_unit'],
            'color' => $color,
            'thickness' => $thickness,
            'measurement_unit' => $dims['measurement_unit'],
            'default_length' => $dims['default_length'],
            'default_width' => $dims['default_width'],
            'default_height' => $dims['default_height'],
            'description' => $this->nullableString($item['description'] ?? null),
        ]);
    }

    protected function findMatchingProduct(string $name, ?string $color, ?string $thickness, array $dims): ?Product
    {
        $query = Product::query()->where('name', $name);

        if ($color !== null) {
            $query->where('color', $color);
        } else {
            $query->where(function ($q) {
                $q->whereNull('color')->orWhere('color', '');
            });
        }

        if ($thickness !== null) {
            $query->where('thickness', $thickness);
        } else {
            $query->where(function ($q) {
                $q->whereNull('thickness')->orWhere('thickness', '');
            });
        }

        if ($dims['default_length'] !== null) {
            $query->where('default_length', $dims['default_length']);
        }

        return $query->first();
    }

    /**
     * @return array{base_unit: string, measurement_unit: ?string, default_length: ?float, default_width: ?float, default_height: ?float}
     */
    protected function parseCustomMeasurement(?string $raw): array
    {
        $defaults = [
            'base_unit' => 'per pc',
            'measurement_unit' => null,
            'default_length' => null,
            'default_width' => null,
            'default_height' => null,
        ];

        if ($raw === null || trim($raw) === '') {
            return $defaults;
        }

        $s = trim($raw);

        if (preg_match('/sq\s*ft/i', $s)) {
            if (preg_match('/([\d.]+)\s*[×x]\s*([\d.]+)/u', $s, $m)) {
                return [
                    'base_unit' => 'per sq ft',
                    'measurement_unit' => 'sq ft',
                    'default_length' => null,
                    'default_width' => (float) $m[1],
                    'default_height' => (float) $m[2],
                ];
            }

            return [
                'base_unit' => 'per sq ft',
                'measurement_unit' => 'sq ft',
                'default_length' => null,
                'default_width' => null,
                'default_height' => null,
            ];
        }

        if (preg_match('/^([\d.]+)\s*(ft|feet|m|meter|meters|mm|cm|inches?|in)?\b/i', $s, $m)) {
            $len = (float) $m[1];
            $unitRaw = strtolower($m[2] ?? 'ft');
            $unit = match (true) {
                in_array($unitRaw, ['ft', 'feet'], true) => 'ft',
                in_array($unitRaw, ['m', 'meter', 'meters'], true) => 'm',
                $unitRaw === 'mm' => 'mm',
                $unitRaw === 'cm' => 'cm',
                in_array($unitRaw, ['in', 'inch', 'inches'], true) => 'inches',
                default => 'ft',
            };

            return [
                'base_unit' => $unit === 'ft' ? 'per ft' : 'per length',
                'measurement_unit' => $unit,
                'default_length' => $len,
                'default_width' => null,
                'default_height' => null,
            ];
        }

        return $defaults;
    }

    protected function generateSku(int $categoryId): string
    {
        $category = Category::findOrFail($categoryId);
        $words = explode(' ', trim($category->name));
        if (count($words) === 1) {
            $skuPrefix = strtoupper(substr($words[0], 0, 3));
        } else {
            $skuPrefix = implode('', array_map(fn ($w) => strtoupper(substr($w, 0, 1)), $words));
        }

        $existingSKUs = Product::where('sku', 'like', $skuPrefix.'%')
            ->where('sku', 'regexp', '^'.$skuPrefix.'[0-9]+$')
            ->pluck('sku')
            ->toArray();

        $nextNumber = 1;
        if ($existingSKUs !== []) {
            $numbers = array_map(function ($sku) use ($skuPrefix) {
                return (int) substr($sku, strlen($skuPrefix));
            }, $existingSKUs);
            $nextNumber = max($numbers) + 1;
        }

        return $skuPrefix.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $t = trim($value);

        return $t === '' ? null : $t;
    }
}
