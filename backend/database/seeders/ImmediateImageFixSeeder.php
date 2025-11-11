<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ImmediateImageFixSeeder extends Seeder
{
    public function run(): void
    {
        // تحديث جميع المنتجات الموجودة بصور فورية
        $imageUrls = [
            'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop', // برجر
            'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop', // بيتزا
            'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop', // سلطة
            'https://images.unsplash.com/photo-1529042410759-befb11dac5a9?w=400&h=300&fit=crop', // كباب
            'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400&h=300&fit=crop', // سوشي
            'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400&h=300&fit=crop', // معكرونة
            'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop', // تاكو
            'https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=400&h=300&fit=crop', // شاورما
        ];

        // تحديث جميع المنتجات الموجودة
        $products = Product::all();
        foreach ($products as $index => $product) {
            $imageUrl = $imageUrls[$index % count($imageUrls)];
            $product->update(['image' => $imageUrl]);
        }

        // إضافة منتجات جديدة مع صور مؤكدة
        $restaurants = DB::table('restaurants')->get();
        if ($restaurants->count() > 0) {
            $restaurantId = $restaurants->first()->id;
            
            $newProducts = [
                [
                    'restaurant_id' => $restaurantId,
                    'name' => 'برجر دجاج طازج',
                    'description' => 'برجر دجاج طازج مع الخضار والصلصة الخاصة',
                    'price' => 75.00,
                    'category' => 'وجبات رئيسية',
                    'preparation_time' => 15,
                    'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
                    'is_available' => true,
                ],
                [
                    'restaurant_id' => $restaurantId,
                    'name' => 'بيتزا مارجريتا',
                    'description' => 'بيتزا إيطالية أصيلة بالجبن والطماطم',
                    'price' => 200.00,
                    'category' => 'بيتزا',
                    'preparation_time' => 20,
                    'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
                    'is_available' => true,
                ],
                [
                    'restaurant_id' => $restaurantId,
                    'name' => 'سلطة خضراء طازجة',
                    'description' => 'سلطة طازجة ومتنوعة مع الصلصة',
                    'price' => 45.00,
                    'category' => 'سلطات',
                    'preparation_time' => 10,
                    'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
                    'is_available' => true,
                ],
                [
                    'restaurant_id' => $restaurantId,
                    'name' => 'كباب مشوي لذيذ',
                    'description' => 'كباب مشوي طازج مع الأرز والسلطة',
                    'price' => 85.00,
                    'category' => 'وجبات رئيسية',
                    'preparation_time' => 25,
                    'image' => 'https://images.unsplash.com/photo-1529042410759-befb11dac5a9?w=400&h=300&fit=crop',
                    'is_available' => true,
                ],
                [
                    'restaurant_id' => $restaurantId,
                    'name' => 'شاورما دجاج',
                    'description' => 'شاورما دجاج مع الخضار والصلصة',
                    'price' => 65.00,
                    'category' => 'وجبات رئيسية',
                    'preparation_time' => 12,
                    'image' => 'https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=400&h=300&fit=crop',
                    'is_available' => true,
                ],
            ];

            foreach ($newProducts as $productData) {
                Product::create($productData);
            }
        }

        $this->command->info('All products updated with working images!');
        $this->command->info('Total products: ' . Product::count());
        $this->command->info('Products with images: ' . Product::whereNotNull('image')->count());
    }
}
