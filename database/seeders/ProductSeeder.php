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
        
        // Generate weight and volume based products
        $this->generateWeightBasedProducts(5);
        $this->generateVolumeBasedProducts(5);
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
        Product::factory($count)->asSet()->create();
        $this->command->info("Generated {$count} set products");
    }

    /**
     * Generate weight-based products (per kg)
     */
    private function generateWeightBasedProducts(int $count): void
    {
        Product::factory($count)->weightBased()->create();
        $this->command->info("Generated {$count} weight-based products");
    }

    /**
     * Generate volume-based products (per liter)
     */
    private function generateVolumeBasedProducts(int $count): void
    {
        Product::factory($count)->volumeBased()->create();
        $this->command->info("Generated {$count} volume-based products");
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
                Product::factory($count)->asSet()->create();
                break;
            case 'weight':
                Product::factory($count)->weightBased()->create();
                break;
            case 'volume':
                Product::factory($count)->volumeBased()->create();
                break;
            default:
                Product::factory($count)->create();
                break;
        }
        
        $this->command->info("Generated {$count} {$type} products");
    }
}
