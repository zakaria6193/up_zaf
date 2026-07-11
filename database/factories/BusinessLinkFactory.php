<?php

namespace Database\Factories;

use App\Models\BusinessLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessLink>
 */
class BusinessLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['google_reviews', 'google_maps', 'menu', 'instagram', 'whatsapp', 'website', 'other'];

        return [
            'business_id' => \App\Models\Business::factory(),
            'type' => fake()->randomElement($types),
            'label' => fake()->sentence(3),
            'url' => fake()->url(),
            'is_active' => true,
            'order' => 0,
        ];
    }

    /**
     * Indicate that the link is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a Google Reviews link.
     */
    public function googleReviews(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'google_reviews',
            'label' => 'Leave us a review on Google',
            'url' => 'https://g.page/r/example',
        ]);
    }

    /**
     * Create an Instagram link.
     */
    public function instagram(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'instagram',
            'label' => 'Follow us on Instagram',
            'url' => 'https://instagram.com/'.fake()->userName(),
        ]);
    }

    /**
     * Create a WhatsApp link.
     */
    public function whatsapp(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'whatsapp',
            'label' => 'Contact us on WhatsApp',
            'url' => 'https://wa.me/212600000000',
        ]);
    }
}
