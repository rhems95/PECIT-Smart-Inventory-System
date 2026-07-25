<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DemoInventorySeeder extends Seeder
{
    public function run(): void
    {
        $supplier = Supplier::first();
        $items = [
            ['Bond Paper A4', 'Office Supplies', 'ream', 285, 120, 30, 'Supply Room A'],
            ['Board Marker (Black)', 'Classroom Supplies', 'piece', 45, 85, 25, 'Supply Room A'],
            ['Whiteboard Eraser', 'Classroom Supplies', 'piece', 35, 40, 15, 'Supply Room A'],
            ['Bottled Water 500ml', 'Pantry Supplies', 'case', 250, 60, 20, 'Pantry'],
            ['Ethernet Cable Cat6', 'Computer Supplies', 'piece', 120, 35, 10, 'IT Stock Room'],
            ['Disinfectant Spray', 'Cleaning Supplies', 'bottle', 95, 28, 12, 'Janitorial'],
            ['Laboratory Gloves', 'Laboratory Supplies', 'box', 180, 22, 8, 'Lab Store'],
            ['Office Chair', 'Furniture', 'unit', 3500, 8, 2, 'Warehouse'],
        ];

        foreach ($items as [$name, $categoryName, $unit, $price, $qty, $min, $location]) {
            $category = Category::where('name', $categoryName)->first();
            $code = 'PECIT-'.strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 8));

            Inventory::firstOrCreate(
                ['item_code' => $code],
                [
                    'item_name' => $name,
                    'description' => "PECIT standard {$name}",
                    'category_id' => $category->id,
                    'unit' => $unit,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'reserved_quantity' => 0,
                    'minimum_stock' => $min,
                    'supplier_id' => $supplier?->id,
                    'location' => $location,
                    'status' => $qty <= 0 ? 'out_of_stock' : ($qty <= $min ? 'low_stock' : 'available'),
                ],
            );
        }
    }
}
