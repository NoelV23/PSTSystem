<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = Category::pluck('id')->toArray();
        $baseUnits = ['per pc', 'per ft', 'per sq ft', 'per set'];
        $colors = ['Clear', 'Tinted', 'Bronze', 'Gray', 'Green', 'Blue', 'White', 'Black', 'Silver', 'Gold'];
        $productTypes = [
            'Glass' => [
                'Clear Glass Panel', 'Tinted Glass Panel', 'Tempered Glass', 'Laminated Glass', 
                'Insulated Glass Unit', 'Mirror Glass', 'Frosted Glass', 'Stained Glass',
                'Safety Glass', 'Decorative Glass', 'Glass Door', 'Glass Window',
                'Glass Partition', 'Glass Railing', 'Glass Table Top'
            ],
            'Aluminum' => [
                'Aluminum Frame', 'Aluminum Door', 'Aluminum Window', 'Aluminum Railing',
                'Aluminum Partition', 'Aluminum Cladding', 'Aluminum Profile',
                'Aluminum Handrail', 'Aluminum Gate', 'Aluminum Fence',
                'Aluminum Trim', 'Aluminum Molding', 'Aluminum Sheet'
            ],
            'Set' => [
                'Complete Window Set', 'Door and Frame Set', 'Glass and Frame Set',
                'Railing System Set', 'Partition System Set', 'Cladding System Set'
            ]
        ];

        // Randomly select a category
        $categoryId = $categories[array_rand($categories)];
        $category = Category::find($categoryId);
        $categoryName = $category ? $category->name : 'Glass';

        // Get product names for the selected category
        $productNames = $productTypes[$categoryName] ?? $productTypes['Glass'];
        $productName = $productNames[array_rand($productNames)];

        // Determine if it's a set based on category
        $isSet = $categoryName === 'Set';
        $baseUnit = $isSet ? 'per set' : $baseUnits[array_rand($baseUnits)];

        // Generate appropriate dimensions based on base unit
        $length = null;
        $width = null;
        $height = null;

        if ($baseUnit === 'per ft') {
            $length = fake()->randomFloat(2, 1, 20);
        } elseif ($baseUnit === 'per sq ft') {
            $width = fake()->randomFloat(2, 1, 10);
            $height = fake()->randomFloat(2, 1, 10);
        }

        return [
            'name' => $productName,
            'sku' => strtoupper(fake()->bothify('??-####-??')),
            'category_id' => $categoryId,
            'base_unit' => $baseUnit,
            'is_set' => $isSet,
            'color' => $colors[array_rand($colors)],
            'default_length' => $length,
            'default_width' => $width,
            'default_height' => $height,
            'price' => fake()->randomFloat(2, 50, 5000),
            'description' => fake()->optional(0.7)->sentence(),
        ];
    }

    /**
     * Indicate that the product is a set.
     */
    public function isSet(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_set' => true,
            'base_unit' => 'per set',
        ]);
    }

    /**
     * Indicate that the product is glass.
     */
    public function glass(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::where('name', 'Glass')->first()?->id ?? 1,
            'is_set' => false,
        ]);
    }

    /**
     * Indicate that the product is aluminum.
     */
    public function aluminum(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::where('name', 'Aluminum')->first()?->id ?? 2,
            'is_set' => false,
        ]);
    }

    /**
     * Indicate that the product is a set.
     */
    public function setCategory(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::where('name', 'Set')->first()?->id ?? 3,
            'is_set' => true,
            'base_unit' => 'per set',
        ]);
    }
}
