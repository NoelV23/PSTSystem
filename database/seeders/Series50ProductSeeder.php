<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class Series50ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'PANEL FRAME 21FT', 'color' => 'PCW'],
            ['name' => 'PANEL FRAME 21FT', 'color' => 'HA'],
            ['name' => 'PERIMETER FRAME 21FT', 'color' => 'PCW'],
            ['name' => 'PERIMETER FRAME 21FT', 'color' => 'HA'],
            ['name' => 'CENTER I-BAR 21FT', 'color' => 'HA'],
            ['name' => 'CENTER I-BAR 21FT', 'color' => 'PCW'],
            ['name' => 'MOULDING 21FT', 'color' => 'HA'],
            ['name' => 'MOULDING 21FT', 'color' => 'PCW'],
            ['name' => 'VINYL', 'color' => null],
            ['name' => 'CAM HANDLE RIGHT', 'color' => 'HA'],
            ['name' => 'CAM HANDLE RIGHT', 'color' => 'PCW'],
        ];

        $skuCounter = 1;

        foreach ($products as $product) {
            $cleanName = str_replace('21FT', '', $product['name']);
            $cleanName = trim($cleanName);

            $isCamHandle = strtoupper($cleanName) === 'CAM HANDLE RIGHT';

            Product::create([
                'name' => $cleanName,
                'sku' => '5S' . str_pad($skuCounter, 6, '0', STR_PAD_LEFT),
                'category_id' => 6,
                'base_unit' => $isCamHandle ? 'per pc' : 'per length',
                'measurement_unit' => $isCamHandle ? null : 'ft',
                'default_length' => $isCamHandle ? null : 21,
                'default_width' => null,
                'default_height' => null,
                'color' => $product['color'],
                'description' => '',
            ]);

            $skuCounter++;
        }

        $this->command->info('Seeded ' . count($products) . ' 50 Series products.');
    }
}
