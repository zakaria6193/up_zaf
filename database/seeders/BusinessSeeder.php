<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample business users
        $user1 = BusinessUser::create([
            'name' => 'Mohamed El Amrani',
            'email' => 'mohamed@example.com',
            'phone' => '+212600111111',
            'password' => bcrypt('password'),
            'is_premium' => true,
            'trial_ends_at' => null,
        ]);

        $user2 = BusinessUser::create([
            'name' => 'Fatima Zahra',
            'email' => 'fatima@example.com',
            'phone' => '+212600222222',
            'password' => bcrypt('password'),
            'is_premium' => true,
            'trial_ends_at' => null,
        ]);

        $business = Business::create([
            'business_user_id' => $user1->id,
            'name' => 'Restaurant Casa Blanca',
            'address' => 'Avenue Mohammed V, Casablanca, Morocco',
            'lat' => 33.5731,
            'lng' => -7.5898,
            'color' => '#f97316',
            'seo_title' => 'Restaurant Casa Blanca - Authentic Moroccan Cuisine',
            'seo_description' => 'Experience the finest Moroccan cuisine in the heart of Casablanca',
            'seo_keywords' => 'moroccan restaurant, casablanca, traditional food',
            'total_views' => 1247,
            'views_this_week' => 89,
            'growth_percentage' => 12.5,
        ]);

        // Add some links
        $business->links()->createMany([
            [
                'type' => 'google_maps',
                'label' => 'Find us on Google Maps',
                'url' => 'https://maps.google.com',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'type' => 'whatsapp',
                'label' => 'WhatsApp Us',
                'url' => 'https://wa.me/212600000000',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'type' => 'menu',
                'label' => 'View Our Menu',
                'url' => url("/{$business->nanoid}/menu"),
                'is_active' => true,
                'order' => 3,
            ],
        ]);

        // Add menu categories and items
        $category1 = $business->menuCategories()->create([
            'name' => 'Appetizers',
            'order' => 1,
        ]);

        $category1->items()->createMany([
            [
                'name' => 'Moroccan Salad',
                'description' => 'Fresh tomatoes, cucumbers, and peppers',
                'price' => 35.00,
                'order' => 1,
            ],
            [
                'name' => 'Zaalouk',
                'description' => 'Eggplant and tomato salad',
                'price' => 30.00,
                'order' => 2,
            ],
        ]);

        $category2 = $business->menuCategories()->create([
            'name' => 'Main Courses',
            'order' => 2,
        ]);

        $category2->items()->createMany([
            [
                'name' => 'Chicken Tagine',
                'description' => 'Slow-cooked chicken with preserved lemons and olives',
                'price' => 85.00,
                'order' => 1,
            ],
            [
                'name' => 'Lamb Couscous',
                'description' => 'Traditional couscous with tender lamb and vegetables',
                'price' => 95.00,
                'order' => 2,
            ],
        ]);

        // Business 2: Café Moderne (owned by user2)
        $cafe = Business::create([
            'business_user_id' => $user2->id,
            'name' => 'Café Moderne',
            'address' => 'Boulevard Zerktouni, Marrakech, Morocco',
            'lat' => 31.6295,
            'lng' => -7.9811,
            'color' => '#8b5cf6',
            'seo_title' => 'Café Moderne - Coffee & Coworking Space in Marrakech',
            'seo_description' => 'Modern café and coworking space with specialty coffee and fresh pastries',
            'seo_keywords' => 'coffee shop, marrakech, coworking, specialty coffee',
            'total_views' => 856,
            'views_this_week' => 124,
            'growth_percentage' => 22.8,
        ]);

        $cafe->links()->createMany([
            [
                'type' => 'instagram',
                'label' => '@cafemoderne',
                'url' => 'https://instagram.com/cafemoderne',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'type' => 'facebook',
                'label' => 'Café Moderne',
                'url' => 'https://facebook.com/cafemoderne',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'type' => 'phone',
                'label' => 'Call Us',
                'url' => 'tel:+212524000001',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'type' => 'website',
                'label' => 'Book a Table',
                'url' => 'https://cafemoderne.ma',
                'is_active' => true,
                'order' => 4,
            ],
        ]);

        $drinks = $cafe->menuCategories()->create([
            'name' => 'Hot Drinks',
            'order' => 1,
        ]);

        $drinks->items()->createMany([
            [
                'name' => 'Espresso',
                'description' => 'Single shot of rich Italian espresso',
                'price' => 18.00,
                'order' => 1,
            ],
            [
                'name' => 'Cappuccino',
                'description' => 'Espresso with steamed milk and foam',
                'price' => 25.00,
                'order' => 2,
            ],
            [
                'name' => 'Moroccan Mint Tea',
                'description' => 'Traditional mint tea served in a pot',
                'price' => 15.00,
                'order' => 3,
            ],
        ]);

        $pastries = $cafe->menuCategories()->create([
            'name' => 'Pastries & Sweets',
            'order' => 2,
        ]);

        $pastries->items()->createMany([
            [
                'name' => 'Croissant',
                'description' => 'Fresh butter croissant',
                'price' => 12.00,
                'order' => 1,
            ],
            [
                'name' => 'Almond Briouate',
                'description' => 'Traditional Moroccan almond pastry',
                'price' => 20.00,
                'order' => 2,
            ],
        ]);

        // Business 3: Tech Store (owned by user1 - second business)
        $techStore = Business::create([
            'business_user_id' => $user1->id,
            'name' => 'ElectroTech Store',
            'address' => 'Rue de la Liberté, Rabat, Morocco',
            'lat' => 34.0209,
            'lng' => -6.8416,
            'color' => '#06b6d4',
            'seo_title' => 'ElectroTech Store - Electronics & Gadgets in Rabat',
            'seo_description' => 'Your one-stop shop for the latest electronics, smartphones, and tech accessories',
            'seo_keywords' => 'electronics, smartphones, gadgets, rabat, tech store',
            'total_views' => 2341,
            'views_this_week' => 187,
            'growth_percentage' => 8.3,
        ]);

        $techStore->links()->createMany([
            [
                'type' => 'whatsapp',
                'label' => 'Order on WhatsApp',
                'url' => 'https://wa.me/212537000002',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'type' => 'instagram',
                'label' => '@electrotech_rabat',
                'url' => 'https://instagram.com/electrotech',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'type' => 'tiktok',
                'label' => 'TikTok Shop',
                'url' => 'https://tiktok.com/@electrotech',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'type' => 'google_maps',
                'label' => 'Visit Our Store',
                'url' => 'https://maps.google.com',
                'is_active' => true,
                'order' => 4,
            ],
            [
                'type' => 'website',
                'label' => 'Online Shop',
                'url' => 'https://electrotech.ma',
                'is_active' => true,
                'order' => 5,
            ],
        ]);
    }
}
