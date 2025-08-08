<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class EDSeriesProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'ED BULLNOSE', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED BULLNOSE', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED STILE W/ GROVE', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED STILE W/ GROVE', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED TOP RAIL', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED TOP RAIL', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED BOTTOM RAIL', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED BOTTOM RAIL', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED SPLIT TUBE', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED SPLIT TUBE', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'SPLITTUBE COVER', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'SPLITTUBE COVER', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED TRESHOLD', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED TRESHOLD', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED FD 100', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED FD 100', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED OPEN BACK 1 3/4X 3', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED OPEN BACK 1 3/4X 3', 'color' => 'PCW', 'base_unit' => 'per length'],
            ['name' => 'ED OPEN BACK 1 3/4X 4', 'color' => 'HA', 'base_unit' => 'per length'],
            ['name' => 'ED OPEN BACK 1 3/4X 4', 'color' => 'PCW', 'base_unit' => 'per length'],

            ['name' => 'FORMICA', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => '1/8 ACRYLIC BLACK', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => '1/8 ACRYLIC BROWN', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => '1/8 ACRYLIC CLEAR', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => '1/4 ACRYLIC CLEAR', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING YELLOW', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING RED', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING BLUE', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING WHITE', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING BACK TO BACK PCW', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING BLACK', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING GRAY', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'CLADING WOOD FINISH', 'color' => null, 'base_unit' => 'per sheet'],
            ['name' => 'ALCOMESH 3x14 (36)', 'color' => 'HA', 'base_unit' => 'per sheet'],
            ['name' => 'ALCOMESH 3x14 (36)', 'color' => 'PCW', 'base_unit' => 'per sheet'],
            ['name' => 'ALCOMESH 4x14 (48)', 'color' => 'HA', 'base_unit' => 'per sheet'],
            ['name' => 'ALCOMESH 4x14 (48)', 'color' => 'PCW', 'base_unit' => 'per sheet'],

            ['name' => 'EXPANDED WIRE 36"', 'color' => 'HA', 'base_unit' => 'per roll'],
            ['name' => 'EXPANDED WIRE 36"', 'color' => 'A', 'base_unit' => 'per roll'],
            ['name' => 'EXPANDED WIRE 36"', 'color' => 'PCW', 'base_unit' => 'per roll'],
            ['name' => 'EXPANDED WIRE 48"', 'color' => 'HA', 'base_unit' => 'per roll'],
            ['name' => 'EXPANDED WIRE 48"', 'color' => 'A', 'base_unit' => 'per roll'],
            ['name' => 'EXPANDED WIRE 48"', 'color' => 'PCW', 'base_unit' => 'per roll'],
            ['name' => 'SCREEN MESH 36"', 'color' => 'HA', 'base_unit' => 'per roll'],
            ['name' => 'SCREEN MESH 36"', 'color' => 'A', 'base_unit' => 'per roll'],
            ['name' => 'SCREEN MESH 48"', 'color' => 'HA', 'base_unit' => 'per roll'],
            ['name' => 'SCREEN MESH 48"', 'color' => 'A', 'base_unit' => 'per roll'],
            ['name' => 'STUCCO 36"', 'color' => 'HA', 'base_unit' => 'per roll'],
            ['name' => 'STUCCO 36"', 'color' => 'A', 'base_unit' => 'per roll'],
            ['name' => 'STUCCO 36"', 'color' => 'PCW', 'base_unit' => 'per roll'],
        ];

        $skuCounter = 1;

        foreach ($products as $product) {
            $name = $product['name'];
            $baseUnit = $product['base_unit'];

            $measurementUnit = $baseUnit === 'per sheet' ? 'sq ft' : 'ft';

            // Handle dimensions like "1 3/4X 4"
            preg_match('/(\d+\s?\d*\/?\d*)X\s?(\d+)/i', $name, $matches);
            $width = null;
            $height = null;

            if ($matches) {
                $width = eval('return ' . str_replace(' ', '+', str_replace('/', '/1.0', $matches[1])) . ';');
                $height = (float) $matches[2];
                $name = trim(str_replace($matches[0], '', $name));
            }

            Product::create([
                'name' => $name,
                'sku' => 'ES' . str_pad($skuCounter++, 6, '0', STR_PAD_LEFT),
                'category_id' => 5,
                'base_unit' => $baseUnit,
                'measurement_unit' => $measurementUnit,
                'default_length' => null,
                'default_width' => $width,
                'default_height' => $height,
                'color' => $product['color'],
                'description' => '',
            ]);
        }

        $this->command->info('ED Series products seeded.');
    }
}
