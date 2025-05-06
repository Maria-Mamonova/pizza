<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Пепперони',
            'type' => 'pizza',
            'price' => 500,
        ]);

        Product::create([
            'name' => 'Маргарита',
            'type' => 'pizza',
            'price' => 450,
        ]);

        Product::create([
            'name' => 'Кола',
            'type' => 'drink',
            'price' => 150,
        ]);

        Product::create([
            'name' => 'Сок апельсиновый',
            'type' => 'drink',
            'price' => 180,
        ]);
    }
}
