<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\UnitOfMeasurement;
use Illuminate\Database\Seeder;

class DemoInventorySeeder extends Seeder
{
    public function run(): void
    {
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
            $uom = $this->unit($unit);

            Inventory::firstOrCreate(
                ['item_code' => $code],
                [
                    'item_name' => $name,
                    'description' => "PECIT standard {$name}",
                    'category_id' => $category->id,
                    'unit' => $unit,
                    'unit_of_measurement_id' => $uom?->id,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'reserved_quantity' => 0,
                    'minimum_stock' => $min,
                    'location' => $location,
                    'student_shop' => false,
                    'department_id' => null,
                    'status' => $qty <= 0 ? 'out_of_stock' : ($qty <= $min ? 'low_stock' : 'available'),
                ],
            );
        }

        $this->seedUniforms();
    }

    protected function seedUniforms(): void
    {
        $uniforms = Category::where('slug', 'uniforms')->first();
        if (! $uniforms) {
            return;
        }

        // Shared: every student may buy these (no department lock).
        $shared = [
            ['Uniform P.E.', 'UNI-PE', 650, 80, 15, 'Physical Education uniform — available to all students.'],
            ['Uniform NSTP', 'UNI-NSTP', 550, 60, 15, 'NSTP uniform — available to all students.'],
            ['Lanyard for ID', 'UNI-LANYARD', 80, 200, 30, 'ID lanyard — available to all students.'],
        ];

        foreach ($shared as [$name, $code, $price, $qty, $min, $description]) {
            $item = Inventory::updateOrCreate(
                ['item_code' => $code],
                [
                    'item_name' => $name,
                    'description' => $description,
                    'category_id' => $uniforms->id,
                    'unit' => 'piece',
                    'unit_of_measurement_id' => $this->unit('piece')?->id,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'reserved_quantity' => 0,
                    'minimum_stock' => $min,
                    'location' => 'Uniform Store',
                    'student_shop' => true,
                    'department_id' => null,
                    'status' => 'available',
                ],
            );

            $this->seedSizeStocks($item, $qty);
        }

        // Exclusive: ONLY students of that department can buy.
        $deptUniforms = [
            'CCS' => ['Computer Studies Uniform (Exclusive)', 'UNI-CCS', 'Exclusive to College of Computer Studies students only.'],
            'CC' => ['Criminology Uniform (Exclusive)', 'UNI-CC', 'Exclusive to College of Criminology students only.'],
            'CTHM' => ['Tourism and Hospitality Uniform (Exclusive)', 'UNI-CTHM', 'Exclusive to College of Tourism and Hospitality Management students only.'],
            'CTE' => ['Teacher Education Uniform (Exclusive)', 'UNI-CTE', 'Exclusive to College of Teacher Education students only.'],
            'CBA' => ['Business Administration Uniform (Exclusive)', 'UNI-CBA', 'Exclusive to College of Business Administration students only.'],
            'SHS' => ['SHS Uniform (Exclusive)', 'UNI-SHS', 'Exclusive to Senior High School students only.'],
        ];

        foreach ($deptUniforms as $code => [$name, $itemCode, $description]) {
            $department = Department::where('code', $code)->first();
            if (! $department) {
                continue;
            }

            Inventory::updateOrCreate(
                ['item_code' => $itemCode],
                [
                    'item_name' => $name,
                    'description' => $description,
                    'category_id' => $uniforms->id,
                    'unit' => 'set',
                    'unit_of_measurement_id' => $this->unit('set')?->id,
                    'unit_price' => 1200,
                    'quantity' => 40,
                    'reserved_quantity' => 0,
                    'minimum_stock' => 10,
                    'location' => 'Uniform Store',
                    'student_shop' => true,
                    'department_id' => $department->id,
                    'status' => 'available',
                ],
            );

            $item = Inventory::where('item_code', $itemCode)->first();
            if ($item) {
                $this->seedSizeStocks($item, 40);
            }
        }
    }

    /**
     * Spread demo on-hand stock across uniform sizes (skip accessories like lanyard).
     */
    protected function seedSizeStocks(Inventory $item, int $totalQty): void
    {
        if (! $item->requiresSize()) {
            $item->sizeStocks()->delete();

            return;
        }

        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);
        $item->sizeStocks()->delete();

        $base = intdiv($totalQty, count($sizes));
        $remainder = $totalQty % count($sizes);

        foreach ($sizes as $index => $size) {
            $qty = $base + ($index < $remainder ? 1 : 0);
            if ($qty <= 0) {
                continue;
            }

            $item->sizeStocks()->create([
                'size' => $size,
                'quantity' => $qty,
                'reserved_quantity' => 0,
            ]);
        }

        $item->syncAggregatesFromSizeStocks();
        $item->updateStatus();
    }

    protected function unit(string $symbol): ?UnitOfMeasurement
    {
        $lookup = strtolower($symbol) === 'piece' ? 'pcs' : $symbol;

        return UnitOfMeasurement::query()
            ->where(function ($q) use ($lookup, $symbol) {
                $q->whereRaw('LOWER(symbol) = ?', [strtolower($lookup)])
                    ->orWhereRaw('LOWER(symbol) = ?', [strtolower($symbol)]);
            })
            ->first();
    }
}
