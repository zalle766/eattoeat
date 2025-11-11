<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Product;
use App\Models\Courier;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@eattoeat.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'is_active' => true,
        ]);

        // Create Restaurant Owner
        $restaurantOwner = User::create([
            'name' => 'Ahmed Restaurant Owner',
            'email' => 'restaurant@eattoeat.com',
            'password' => Hash::make('password'),
            'role' => 'restaurant',
            'phone' => '+1234567891',
            'address' => '123 Main Street, Cairo, Egypt',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'is_active' => true,
        ]);

        // Create Restaurant
        $restaurant = Restaurant::create([
            'user_id' => $restaurantOwner->id,
            'name' => 'مطعم الأصالة',
            'description' => 'مطعم يقدم أشهى الأطباق العربية التقليدية',
            'address' => '123 Main Street, Cairo, Egypt',
            'latitude' => 30.0444,
            'longitude' => 31.2357,
            'phone' => '+1234567891',
            'email' => 'info@alasala.com',
            'opening_time' => '09:00',
            'closing_time' => '23:00',
            'delivery_fee' => 15.00,
            'delivery_radius' => 10,
            'is_active' => true,
            'is_open' => true,
        ]);

        // Create Products for Restaurant
        $products = [
            [
                'name' => 'كباب مشوي',
                'description' => 'كباب مشوي طازج مع الأرز والسلطة',
                'price' => 45.00,
                'category' => 'Grilled',
                'preparation_time' => 20,
            ],
            [
                'name' => 'شاورما دجاج',
                'description' => 'شاورما دجاج مع الخضار والصلصة',
                'price' => 35.00,
                'category' => 'Sandwiches',
                'preparation_time' => 15,
            ],
            [
                'name' => 'مشويات مشكلة',
                'description' => 'طبق مشويات متنوعة مع الأرز والسلطة',
                'price' => 80.00,
                'category' => 'Grilled',
                'preparation_time' => 25,
            ],
            [
                'name' => 'بيتزا مارغريتا',
                'description' => 'بيتزا بالجبن والطماطم الطازجة',
                'price' => 55.00,
                'category' => 'Pizza',
                'preparation_time' => 18,
            ],
            [
                'name' => 'برجر كلاسيك',
                'description' => 'برجر باللحم مع الخضار والصلصة',
                'price' => 40.00,
                'category' => 'Burgers',
                'preparation_time' => 12,
            ],
        ];

        foreach ($products as $productData) {
            Product::create(array_merge($productData, [
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
            ]));
        }

        // Create Courier
        $courierUser = User::create([
            'name' => 'Mohamed Courier',
            'email' => 'courier@eattoeat.com',
            'password' => Hash::make('password'),
            'role' => 'courier',
            'phone' => '+1234567892',
            'address' => '456 Delivery Street, Cairo, Egypt',
            'latitude' => 30.0544,
            'longitude' => 31.2457,
            'is_active' => true,
        ]);

        Courier::create([
            'user_id' => $courierUser->id,
            'vehicle_type' => 'motorcycle',
            'license_number' => 'MOT123456',
            'is_available' => true,
            'latitude' => 30.0544,
            'longitude' => 31.2457,
            'is_verified' => true,
        ]);

        // Create Customer
        $customer = User::create([
            'name' => 'Sara Customer',
            'email' => 'customer@eattoeat.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1234567893',
            'address' => '789 Customer Street, Cairo, Egypt',
            'latitude' => 30.0644,
            'longitude' => 31.2557,
            'is_active' => true,
        ]);

        // Create more restaurants
        $restaurants = [
            [
                'name' => 'مطعم البيتزا الإيطالية',
                'description' => 'أفضل البيتزا الإيطالية الأصيلة',
                'address' => '321 Pizza Street, Cairo, Egypt',
                'latitude' => 30.0344,
                'longitude' => 31.2257,
                'phone' => '+1234567894',
                'email' => 'info@italianpizza.com',
                'opening_time' => '10:00',
                'closing_time' => '24:00',
                'delivery_fee' => 20.00,
                'delivery_radius' => 8,
            ],
            [
                'name' => 'مطعم السوشي الياباني',
                'description' => 'سوشي طازج ومأكولات يابانية أصيلة',
                'address' => '654 Sushi Street, Cairo, Egypt',
                'latitude' => 30.0744,
                'longitude' => 31.2757,
                'phone' => '+1234567895',
                'email' => 'info@sushijapan.com',
                'opening_time' => '11:00',
                'closing_time' => '23:00',
                'delivery_fee' => 25.00,
                'delivery_radius' => 12,
            ],
        ];

        foreach ($restaurants as $restaurantData) {
            $owner = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'role' => 'restaurant',
                'phone' => fake()->phoneNumber(),
                'address' => $restaurantData['address'],
                'latitude' => $restaurantData['latitude'],
                'longitude' => $restaurantData['longitude'],
                'is_active' => true,
            ]);

            Restaurant::create(array_merge($restaurantData, [
                'user_id' => $owner->id,
                'is_active' => true,
                'is_open' => true,
            ]));
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@eattoeat.com / password');
        $this->command->info('Restaurant: restaurant@eattoeat.com / password');
        $this->command->info('Courier: courier@eattoeat.com / password');
        $this->command->info('Customer: customer@eattoeat.com / password');
    }
}