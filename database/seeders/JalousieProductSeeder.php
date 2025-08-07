<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class JalousieProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jalousies = [
            ['name' => 'JALOUPLUS ORDINARY # 4 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #5 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #6 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #7 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #8 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #9 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #10 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #11 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #12 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #13 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #14 BLADES', 'color' => null],
            ['name' => 'JALOUPLUS ORDINARY #15 BLADES', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #4 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #5 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #6 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #7 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #8 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #9 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #10 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #11 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #12 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #13 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #14 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #15 BLD', 'color' => null],
            ['name' => 'MIST JALOUPLUS HEAVY DUTY #16 BLD', 'color' => null],
            ['name' => 'JALOUSIE #8 BLADES', 'color' => null],
            ['name' => 'JALOUSIE #9 BLADES', 'color' => null],
            ['name' => 'JALOUSIE #10 BLADES', 'color' => null],
            ['name' => 'JALOUSIE #11 BLADES', 'color' => null],
            ['name' => 'JALOUSIE #14 BLADES', 'color' => null],
        ];

        $skuCounter = 1;

        foreach ($jalousies as $jalousie) {
            Product::create([
                'name' => $jalousie['name'],
                'sku' => 'JAL' . str_pad($skuCounter, 6, '0', STR_PAD_LEFT),
                'category_id' => 3, // Accessories category
                'base_unit' => 'per set',
                'measurement_unit' => null,
                'default_length' => null,
                'default_width' => null,
                'default_height' => null,
                'color' => $jalousie['color'],
                'description' => '',
            ]);

            $skuCounter++;
        }

        $this->command->info('Generated ' . count($jalousies) . ' jalousies products with SKU starting from JAL000001');
    }
} 