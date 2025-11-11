<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class RealImageSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء مطعم مع صور حقيقية من الإنترنت
        $restaurantOwner = User::create([
            'name' => 'مطعم الصور الحقيقية',
            'email' => 'realimages@eattoeat.com',
            'password' => Hash::make('password'),
            'role' => 'restaurant',
            'phone' => '+1234567898',
            'address' => 'شارع الصور الحقيقية، الرباط، المغرب',
            'latitude' => 34.0209,
            'longitude' => -6.8416,
            'is_active' => true,
        ]);

        $restaurant = Restaurant::create([
            'user_id' => $restaurantOwner->id,
            'name' => 'مطعم الصور الحقيقية',
            'description' => 'مطعم يقدم أطباق لذيذة مع صور حقيقية',
            'address' => 'شارع الصور الحقيقية، الرباط، المغرب',
            'latitude' => 34.0209,
            'longitude' => -6.8416,
            'phone' => '+1234567898',
            'email' => 'info@realimages.com',
            'opening_time' => '09:00',
            'closing_time' => '23:00',
            'delivery_fee' => 15.00,
            'delivery_radius' => 10,
            'is_active' => true,
            'is_open' => true,
        ]);

        // إنشاء منتجات مع صور حقيقية من الإنترنت
        $products = [
            [
                'name' => 'برجر دجاج',
                'description' => 'برجر دجاج طازج مع الخضار والصلصة الخاصة',
                'price' => 75.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 15,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'بيتزا مارجريتا',
                'description' => 'بيتزا إيطالية أصيلة بالجبن والطماطم',
                'price' => 200.00,
                'category' => 'بيتزا',
                'preparation_time' => 20,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'سلطة خضراء',
                'description' => 'سلطة طازجة ومتنوعة مع الصلصة',
                'price' => 45.00,
                'category' => 'سلطات',
                'preparation_time' => 10,
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'كباب مشوي',
                'description' => 'كباب مشوي طازج مع الأرز والسلطة',
                'price' => 85.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 25,
                'image' => 'https://images.unsplash.com/photo-1529042410759-befb11dac5a9?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'شاورما دجاج',
                'description' => 'شاورما دجاج مع الخضار والصلصة',
                'price' => 65.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 12,
                'image' => 'https://images.unsplash.com/photo-1529042410759-befb11dac5a9?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'سوشي طازج',
                'description' => 'سوشي طازج مع الأرز والأسماك',
                'price' => 120.00,
                'category' => 'مأكولات بحرية',
                'preparation_time' => 30,
                'image' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'معكرونة كاربونارا',
                'description' => 'معكرونة إيطالية بالجبن والبيض',
                'price' => 90.00,
                'category' => 'معكرونة',
                'preparation_time' => 18,
                'image' => 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'تاكو مكسيكي',
                'description' => 'تاكو مكسيكي أصيل مع اللحم والخضار',
                'price' => 55.00,
                'category' => 'مكسيكي',
                'preparation_time' => 15,
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop',
            ],
        ];

        foreach ($products as $productData) {
            Product::create(array_merge($productData, [
                'restaurant_id' => $restaurant->id,
                'is_available' => true,
            ]));
        }

        $this->command->info('Real images seeded successfully!');
        $this->command->info('Restaurant with real images: realimages@eattoeat.com / password');
    }
}
