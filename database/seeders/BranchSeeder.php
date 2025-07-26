<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'John Glass',
                'location' => '123 Main Street, Metro Manila',
                'phone' => '+63 912 345 6789',
                'social_media' => 'john.glass.ph',
                'status' => 'active',
            ],
            [
                'name' => 'RV Glass',
                'location' => '456 Business Avenue, Quezon City',
                'phone' => '+63 923 456 7890',
                'social_media' => 'rv.glass.ph',
                'status' => 'active',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::create($branch);
        }
    }
} 