<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->admin()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);

        // Create sample categories
        $categories = collect([
            'Minuman',
            'Makanan Ringan',
            'Susu & Dairy',
            'Roti',
            'Bumbu Dapur',
            'Frozen Food',
            'Snack',
            'Minuman Bersoda',
            'Mie Instan',
            'Perawatan Tubuh',
        ])->map(fn (string $name) => Category::create(['name' => $name]));

        // Create sample products
        $products = [
            ['name' => 'Indomilk Coklat 250ml', 'barcode' => '8992802120012', 'category' => 'Susu & Dairy', 'shelf_life_days' => 180],
            ['name' => 'Yakult Original', 'barcode' => '8992388101016', 'category' => 'Susu & Dairy', 'shelf_life_days' => 40],
            ['name' => 'Roti Tawar Sari Roti', 'barcode' => '8996001302019', 'category' => 'Roti', 'shelf_life_days' => 7],
            ['name' => 'Teh Botol Sosro 450ml', 'barcode' => '8998009010019', 'category' => 'Minuman', 'shelf_life_days' => 365],
            ['name' => 'Pocari Sweat 500ml', 'barcode' => '4987035101512', 'category' => 'Minuman', 'shelf_life_days' => 365],
            ['name' => 'Chitato Sapi Panggang 68g', 'barcode' => '8886014100013', 'category' => 'Snack', 'shelf_life_days' => 180],
            ['name' => 'Indomie Goreng', 'barcode' => '8996001210017', 'category' => 'Mie Instan', 'shelf_life_days' => 240],
            ['name' => 'Coca Cola 330ml', 'barcode' => '5449000000996', 'category' => 'Minuman Bersoda', 'shelf_life_days' => 270],
            ['name' => 'Royco Ayam 100g', 'barcode' => '8999999035013', 'category' => 'Bumbu Dapur', 'shelf_life_days' => 365],
            ['name' => 'Walls Magnum Classic', 'barcode' => '8999999518011', 'category' => 'Frozen Food', 'shelf_life_days' => 545],
            ['name' => 'Good Day Cappuccino 250ml', 'barcode' => '8998866200016', 'category' => 'Minuman', 'shelf_life_days' => 365],
            ['name' => 'Silverqueen Cashew 65g', 'barcode' => '8991002101013', 'category' => 'Snack', 'shelf_life_days' => 365],
            ['name' => 'Lifebuoy Sabun Cair 100ml', 'barcode' => '8999999572013', 'category' => 'Perawatan Tubuh', 'shelf_life_days' => 730],
            ['name' => 'Oreo Original 137g', 'barcode' => '7622210100115', 'category' => 'Makanan Ringan', 'shelf_life_days' => 270],
            ['name' => 'Ultra Milk Full Cream 1L', 'barcode' => '8998009010118', 'category' => 'Susu & Dairy', 'shelf_life_days' => 270],
        ];

        foreach ($products as $productData) {
            $category = $categories->firstWhere('name', $productData['category']);

            Product::create([
                'category_id' => $category?->id,
                'name' => $productData['name'],
                'barcode' => $productData['barcode'],
                'shelf_life_days' => $productData['shelf_life_days'],
            ]);
        }
    }
}
