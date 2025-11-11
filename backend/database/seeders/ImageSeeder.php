<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class ImageSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء مطعم مع صور
        $restaurantOwner = User::create([
            'name' => 'مطعم الصور التجريبي',
            'email' => 'images@eattoeat.com',
            'password' => Hash::make('password'),
            'role' => 'restaurant',
            'phone' => '+1234567899',
            'address' => 'شارع الصور، الرباط، المغرب',
            'latitude' => 34.0209,
            'longitude' => -6.8416,
            'is_active' => true,
        ]);

        $restaurant = Restaurant::create([
            'user_id' => $restaurantOwner->id,
            'name' => 'مطعم الصور التجريبي',
            'description' => 'مطعم تجريبي لاختبار الصور',
            'address' => 'شارع الصور، الرباط، المغرب',
            'latitude' => 34.0209,
            'longitude' => -6.8416,
            'phone' => '+1234567899',
            'email' => 'info@images.com',
            'opening_time' => '09:00',
            'closing_time' => '23:00',
            'delivery_fee' => 15.00,
            'delivery_radius' => 10,
            'is_active' => true,
            'is_open' => true,
        ]);

        // إنشاء منتجات مع صور
        $products = [
            [
                'name' => 'برجر دجاج',
                'description' => 'برجر دجاج طازج مع الخضار والصلصة الخاصة',
                'price' => 75.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 15,
                'image' => 'products/burger.jpg',
            ],
            [
                'name' => 'بيتزا مارجريتا',
                'description' => 'بيتزا إيطالية أصيلة بالجبن والطماطم',
                'price' => 200.00,
                'category' => 'بيتزا',
                'preparation_time' => 20,
                'image' => 'products/pizza.jpg',
            ],
            [
                'name' => 'سلطة خضراء',
                'description' => 'سلطة طازجة ومتنوعة مع الصلصة',
                'price' => 45.00,
                'category' => 'سلطات',
                'preparation_time' => 10,
                'image' => 'products/salad.jpg',
            ],
            [
                'name' => 'كباب مشوي',
                'description' => 'كباب مشوي طازج مع الأرز والسلطة',
                'price' => 85.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 25,
                'image' => 'products/kebab.jpg',
            ],
            [
                'name' => 'شاورما دجاج',
                'description' => 'شاورما دجاج مع الخضار والصلصة',
                'price' => 65.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 12,
                'image' => 'products/shawarma.jpg',
            ],
        ];

        foreach ($products as $productData) {
            Product::create(array_merge($productData, [
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
            ]));
        }

        $this->command->info('Images seeded successfully!');
        $this->command->info('Restaurant with images: images@eattoeat.com / password');
    }
}
