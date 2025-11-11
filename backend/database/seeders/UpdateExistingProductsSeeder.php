<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateExistingProductsSeeder extends Seeder
{
    public function run(): void
    {
        // تحديث المنتجات الموجودة بصور حقيقية
        $products = [
            [
                'name' => 'bittza',
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'tazin',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'كباب مشوي',
                'image' => 'https://images.unsplash.com/photo-1529042410759-befb11dac5a9?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'شاورما دجاج',
                'image' => 'https://images.unsplash.com/photo-1529042410759-befb11dac5a9?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'بيتزا مارغريتا',
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
            ],
            [
                'name' => 'برجر كلاسيك',
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
            ],
        ];

        foreach ($products as $productData) {
            Product::where('name', $productData['name'])
                ->update(['image' => $productData['image']]);
        }

        // تحديث جميع المنتجات التي لا تحتوي على صور
        Product::whereNull('image')
            ->orWhere('image', '')
            ->update(['image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop']);

        $this->command->info('Existing products updated with real images!');
    }
}
