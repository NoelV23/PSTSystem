<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ThirtyEightSeriesProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'PANEL', 'color' => 'PCW'],
            ['name' => 'PANEL', 'color' => 'HA'],
            ['name' => 'PERIMETER', 'color' => 'PCW'],
            ['name' => 'PERIMETER', 'color' => 'HA'],
            ['name' => 'I BAR', 'color' => 'PCW'],
            ['name' => 'I BAR', 'color' => 'HA'],
            ['name' => 'MOULDING', 'color' => 'PCW'],
            ['name' => 'MOULDING', 'color' => 'HA'],
            ['name' => 'PANEL COVER', 'color' => 'HA'],
            ['name' => 'PANEL COVER', 'color' => 'PCW'],
        ];

        $skuCounter = 1;

        foreach ($products as $product) {
            Product::create([
                'sku' => '3S' . str_pad($skuCounter++, 6, '0', STR_PAD_LEFT),
                'name' => $product['name'],
                'color' => $product['color'],
                'base_unit' => 'per length',
                'measurement_unit' => 'ft',
                'category_id' => 11,
            ]);
        }
    }
}
