<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class SDSeriesTradProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'SD LOCKSTILE 21FT', 'color' => 'HA'],
            ['name' => 'SD LOCKSTILE 21FT', 'color' => 'PCW'],
            ['name' => 'SD INTERLOCKER 21FT', 'color' => 'HA'],
            ['name' => 'SD INTERLOCKER 21FT', 'color' => 'PCW'],
            ['name' => 'SD DOUBLE JAMB 21FT', 'color' => 'HA'],
            ['name' => 'SD DOUBLE JAMB 21FT', 'color' => 'PCW'],
            ['name' => 'SD DOUBLE SILL 21FT', 'color' => 'HA'],
            ['name' => 'SD DOUBLE SILL 21FT', 'color' => 'PCW'],
            ['name' => 'SD BOTTOM RAIL 21FT', 'color' => 'HA'],
            ['name' => 'SD BOTTOM RAIL 21FT', 'color' => 'PCW'],
            ['name' => 'SD TOP RAIL 21FT', 'color' => 'HA'],
            ['name' => 'SD TOP RAIL 21FT', 'color' => 'PCW'],
            ['name' => 'SD DOUBLE HEAD 21FT', 'color' => 'HA'],
            ['name' => 'SD DOUBLE HEAD 21FT', 'color' => 'PCW'],
            ['name' => 'SD DOUBLE HEAD W/SCREEN 21FT', 'color' => 'HA'],
            ['name' => 'SD DOUBLE HEAD W/SCREEN 21FT', 'color' => 'PCW'],
            ['name' => 'SD DOUBLE JAMB W/SCREEN 21FT', 'color' => 'HA'],
            ['name' => 'SD DOUBLE JAMB W/SCREEN 21FT', 'color' => 'PCW'],
            ['name' => 'SD SINGLE HEAD 21FT', 'color' => 'HA'],
            ['name' => 'SD SINGLE HEAD 21FT', 'color' => 'PCW'],
            ['name' => 'SD SINGLE SILL 21FT', 'color' => 'HA'],
            ['name' => 'SD SINGLE SILL 21FT', 'color' => 'PCW'],
            ['name' => 'SD SINGLE JAMB 21FT', 'color' => 'HA'],
            ['name' => 'SD SINGLE JAMB 21FT', 'color' => 'PCW'],
            ['name' => 'SD DOUBLE FLAT SILL', 'color' => 'PCW'],
            ['name' => 'SD DOUBLE FLAT SILL', 'color' => 'HA'],
            ['name' => 'SD SCREEN ASTRAGAL', 'color' => 'HA'],
            ['name' => 'SD SCREEN ASTRAGAL', 'color' => 'PCW'],
            ['name' => 'SD ASTRAGAL', 'color' => 'HA'],
            ['name' => 'SD ASTRAGAL', 'color' => 'WOOD FINISH'],
            ['name' => 'SD ASTRAGAL', 'color' => 'PCW'],
            ['name' => 'SCREEN FRAME', 'color' => 'HA'],
            ['name' => 'SCREEN FRAME', 'color' => 'PCW'],
            ['name' => 'SCREEN FRAME', 'color' => 'WOOD FINISH'],
            ['name' => 'SCREEN FRAME', 'color' => 'A'],
            ['name' => 'YC PANEL', 'color' => 'HA'],
            ['name' => 'YC PANEL', 'color' => 'PCW'],
            ['name' => 'YC PERIMETER', 'color' => 'HA'],
            ['name' => 'YC PERIMETER', 'color' => 'PCW'],
            ['name' => 'YC MOULDING', 'color' => 'HA'],
            ['name' => 'YC MOULDING', 'color' => 'PCW'],
            ['name' => 'YS221', 'color' => 'WOOD FINISH'],
        ];

        $skuCounter = 1;

        foreach ($products as $product) {
            $cleanName = str_replace('21FT', '', $product['name']);
            $cleanName = trim($cleanName);

            Product::create([
                'name' => $cleanName,
                'sku' => 'SST' . str_pad($skuCounter, 6, '0', STR_PAD_LEFT),
                'category_id' => 4,
                'base_unit' => 'per length',
                'measurement_unit' => 'ft',
                'default_length' => 21,
                'default_width' => null,
                'default_height' => null,
                'color' => $product['color'],
                'description' => '',
            ]);

            $skuCounter++;
        }

        $this->command->info('Seeded ' . count($products) . ' SD Series Traditional products.');
    }
}
