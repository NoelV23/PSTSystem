<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class AcpProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'ACP FRAME', 'color' => 'PCW'],
            ['name' => 'ACP FRAME', 'color' => 'WOOD FINISH'],
            ['name' => 'ACP FRAME', 'color' => 'HA'],
            ['name' => 'ACP HANDLE', 'color' => 'PCW'],
            ['name' => 'ACP HANDLE', 'color' => 'WOOD FINISH'],
            ['name' => 'ACP HANDLE', 'color' => 'HA'],
        ];

        $skuCounter = 1;

        foreach ($products as $product) {
            Product::create([
                'sku' => 'ACP' . str_pad($skuCounter++, 6, '0', STR_PAD_LEFT),
                'name' => $product['name'],
                'color' => $product['color'],
                'base_unit' => 'per length',
                'measurement_unit' => 'ft',
                'category_id' => 12,
            ]);
        }
    }
}
