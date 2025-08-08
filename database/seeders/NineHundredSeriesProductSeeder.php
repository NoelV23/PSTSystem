<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class NineHundredSeriesProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'INTERLOCKER', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'INTERLOCKER', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'LOCKSTILE', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'LOCKSTILE', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'OUTER TOP/BOTTOM RAIL', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'OUTER TOP/BOTTOM RAIL', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE JAMB', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE JAMB', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE SILL W/SCREEN', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE SILL W/SCREEN', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE HEAD', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE HEAD', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'INNER TOP/BOTTOM RAIL', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'INNER TOP/BOTTOM RAIL', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE SILL', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE SILL', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => 'DOUBLE FLAT SILL WOOD FINISH', 'color' => null, 'unit' => 'LENGTH'],
            ['name' => 'FLAT SILL', 'color' => 'HA', 'unit' => 'LENGTH'],
            ['name' => 'FLAT SILL', 'color' => 'PCW', 'unit' => 'LENGTH'],
            ['name' => '4 KINDS #1', 'color' => null, 'unit' => 'PC'],
            ['name' => '4 KINDS #2', 'color' => null, 'unit' => 'PC'],
            ['name' => '4 KINDS #3', 'color' => null, 'unit' => 'PC'],
            ['name' => '4 KINDS #4', 'color' => null, 'unit' => 'PC'],
            ['name' => '4 KINDS OLD', 'color' => 'PER SET', 'unit' => 'PC'],
            ['name' => 'RUBBER JAMB', 'color' => 'WHITE', 'unit' => 'KG'],
            ['name' => 'RUBBER JAMB', 'color' => 'GRAY', 'unit' => 'KG'],
            ['name' => 'RUBBER JAMB', 'color' => 'CLEAR', 'unit' => 'KG'],
            ['name' => 'RUBBER JAMB', 'color' => 'BLACK', 'unit' => 'KG'],
            ['name' => 'LATCH KEEPER', 'color' => 'HA', 'unit' => 'PC'],
            ['name' => 'LATCH KEEPER', 'color' => 'PCW', 'unit' => 'PC'],
            ['name' => 'NYLON ROLLER SINGLE', 'color' => null, 'unit' => 'PC'],
            ['name' => 'NYLON ROLLER PLASTIC', 'color' => 'SINGLE', 'unit' => 'PC'],
            ['name' => 'NYLON ROLLER PLASTIC', 'color' => 'DOUBLE', 'unit' => 'PC'],
            ['name' => 'FLUSH LOCK W/KEY', 'color' => 'PCW', 'unit' => 'PC'],
            ['name' => 'FLUSH LOCK W/KEY', 'color' => 'HA', 'unit' => 'PC'],
            ['name' => 'FLUSH LOCK #12 HA', 'color' => null, 'unit' => 'PC'],
            ['name' => 'FLUSH LOCK #12 PCW', 'color' => null, 'unit' => 'PC'],
            ['name' => '900 HANDLE', 'color' => null, 'unit' => 'PC'],
            ['name' => 'CAM HANDLE 38 SERIES', 'color' => 'HA', 'unit' => 'PC'],
            ['name' => 'CAM HANDLE 38 SERIES', 'color' => 'PCW', 'unit' => 'PC'],
            ['name' => 'FLUSH LOCK W/KEY', 'color' => 'HA', 'unit' => 'PC'],
            ['name' => 'FLUSH LOCK W/KEY', 'color' => 'PCW', 'unit' => 'PC'],
            ['name' => 'GLACING W/PEN', 'color' => 'BLACK', 'unit' => 'PC'],
            ['name' => 'GLACING W/PEN', 'color' => 'WHITE', 'unit' => 'PC'],
            ['name' => 'GLACING W/PEN', 'color' => 'GRAY', 'unit' => 'PC'],
            ['name' => 'FATS LOCK', 'color' => null, 'unit' => 'PC'],
        ];

        $skuCounter = 1;

        foreach ($products as $product) {
            $baseUnit = match (strtoupper($product['unit'])) {
                'LENGTH' => 'per length',
                'PC'     => 'per pc',
                'KG'     => 'per kg',
                default  => 'per length',
            };

            $measurementUnit = $baseUnit === 'per length' ? 'ft' : null;
            $defaultLength = $baseUnit === 'per length' ? 21 : null;

            Product::create([
                'sku' => '9S' . str_pad($skuCounter++, 6, '0', STR_PAD_LEFT),
                'name' => $product['name'],
                'color' => $product['color'],
                'base_unit' => $baseUnit,
                'measurement_unit' => $measurementUnit,
                'default_length' => $defaultLength,
                'category_id' => 13,
            ]);
        }
    }
}
