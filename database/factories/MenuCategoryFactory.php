<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuCategory>
 */
class MenuCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Appetizers',
            'Main Courses',
            'Desserts',
            'Beverages',
            'Salads',
            'Soups',
            'Pizza',
            'Pasta',
            'Burgers',
            'Sandwiches',
        ];

        return [
            'business_id' => \App\Models\Business::factory(),
            'name' => fake()->randomElement($categories),
            'order' => 0,
        ];
    }
}
