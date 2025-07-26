<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category IDs
        $glassCategoryId = Category::where('name', 'Glass')->first()?->id ?? 1;
        $aluminumCategoryId = Category::where('name', 'Aluminum')->first()?->id ?? 2;
        $setCategoryId = Category::where('name', 'Set')->first()?->id ?? 3;

        // Generate mixed products (default: 20 products)
        $this->generateMixedProducts(20);

        // Generate specific category products
        $this->generateGlassProducts(10);
        $this->generateAluminumProducts(10);
        $this->generateSetProducts(5);
    }

    /**
     * Generate mixed products with random categories
     */
    private function generateMixedProducts(int $count): void
    {
        Product::factory($count)->create();
        $this->command->info("Generated {$count} mixed products");
    }

    /**
     * Generate glass products only
     */
    private function generateGlassProducts(int $count): void
    {
        Product::factory($count)->glass()->create();
        $this->command->info("Generated {$count} glass products");
    }

    /**
     * Generate aluminum products only
     */
    private function generateAluminumProducts(int $count): void
    {
        Product::factory($count)->aluminum()->create();
        $this->command->info("Generated {$count} aluminum products");
    }

    /**
     * Generate set products only
     */
    private function generateSetProducts(int $count): void
    {
        Product::factory($count)->isSet()->create();
        $this->command->info("Generated {$count} set products");
    }

    /**
     * Generate products with custom count (for manual testing)
     */
    public function generateCustom(int $count, string $type = 'mixed'): void
    {
        switch ($type) {
            case 'glass':
                Product::factory($count)->glass()->create();
                break;
            case 'aluminum':
                Product::factory($count)->aluminum()->create();
                break;
            case 'set':
                Product::factory($count)->isSet()->create();
                break;
            default:
                Product::factory($count)->create();
                break;
        }
        
        $this->command->info("Generated {$count} {$type} products");
    }
}
