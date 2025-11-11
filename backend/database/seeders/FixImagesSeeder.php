<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FixImagesSeeder extends Seeder
{
    public function run(): void
    {
        // حذف جميع المنتجات الموجودة
        Product::truncate();
        
        // إنشاء منتجات جديدة مع صور مختلفة ومحددة
        $products = [
            [
                'restaurant_id' => 1, // استخدام أول مطعم موجود
                'name' => 'برجر دجاج طازج',
                'description' => 'برجر دجاج طازج مع الخضار والصلصة الخاصة',
                'price' => 75.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 15,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'بيتزا مارجريتا',
                'description' => 'بيتزا إيطالية أصيلة بالجبن والطماطم',
                'price' => 200.00,
                'category' => 'بيتزا',
                'preparation_time' => 20,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'سلطة خضراء طازجة',
                'description' => 'سلطة طازجة ومتنوعة مع الصلصة',
                'price' => 45.00,
                'category' => 'سلطات',
                'preparation_time' => 10,
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'كباب مشوي لذيذ',
                'description' => 'كباب مشوي طازج مع الأرز والسلطة',
                'price' => 85.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 25,
                'image' => 'https://images.unsplash.com/photo-1529042410759-befb11dac5a9?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'سوشي طازج',
                'description' => 'سوشي طازج مع الأرز والأسماك',
                'price' => 120.00,
                'category' => 'مأكولات بحرية',
                'preparation_time' => 30,
                'image' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'معكرونة كاربونارا',
                'description' => 'معكرونة إيطالية بالجبن والبيض',
                'price' => 90.00,
                'category' => 'معكرونة',
                'preparation_time' => 18,
                'image' => 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'تاكو مكسيكي',
                'description' => 'تاكو مكسيكي أصيل مع اللحم والخضار',
                'price' => 55.00,
                'category' => 'مكسيكي',
                'preparation_time' => 15,
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'شاورما دجاج',
                'description' => 'شاورما دجاج مع الخضار والصلصة',
                'price' => 65.00,
                'category' => 'وجبات رئيسية',
                'preparation_time' => 12,
                'image' => 'https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'تشيز كيك',
                'description' => 'تشيز كيك كريمي مع التوت الطازج',
                'price' => 40.00,
                'category' => 'حلويات',
                'preparation_time' => 5,
                'image' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
            [
                'restaurant_id' => 1,
                'name' => 'قهوة لاتيه',
                'description' => 'قهوة لاتيه كريمية مع الحليب الطازج',
                'price' => 25.00,
                'category' => 'مشروبات',
                'preparation_time' => 3,
                'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&h=300&fit=crop',
                'is_available' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        $this->command->info('All products recreated with unique images!');
        $this->command->info('Total products: ' . Product::count());
        $this->command->info('Each product now has its own unique image!');
    }
}
