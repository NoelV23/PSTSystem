<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * One-time: set variant_group_label (and thickness when empty) for catalog rows
 * shaped like "Ribtype (1.05m x 0.35mm)" or "Plain Sheets - Metal (0.30mm Thickness)"
 * so SQ/PO/Sales show one grouped line per variant group.
 */
return new class extends Migration
{
    private const CATEGORY_IDS = [1, 2, 3, 7, 9];

    public function up(): void
    {
        if (! Schema::hasColumn('products', 'variant_group_label')) {
            return;
        }

        Product::query()
            ->whereIn('category_id', self::CATEGORY_IDS)
            ->whereNull('variant_group_label')
            ->orderBy('id')
            ->chunkById(200, function ($products) {
                foreach ($products as $product) {
                    $name = trim((string) $product->name);
                    if ($name === '' || ! preg_match('/^([^(]+)\s*\(\s*([^)]*)\)\s*$/u', $name, $m)) {
                        continue;
                    }
                    $group = trim($m[1]);
                    $inner = trim($m[2]);
                    if (strlen($group) < 2) {
                        continue;
                    }
                    $product->variant_group_label = $group;
                    $thicknessEmpty = $product->thickness === null || $product->thickness === '';
                    if ($thicknessEmpty && $inner !== '') {
                        if (preg_match('/^([\d.]+)\s*mm\s*Thickness$/i', $inner, $tm)) {
                            $product->thickness = $tm[1].'mm';
                        } else {
                            $product->thickness = $inner;
                        }
                    }
                    $product->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        // Non-destructive: we cannot safely strip variant_group_label without losing manual edits.
    }
};
