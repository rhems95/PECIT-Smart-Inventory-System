<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Office Supplies',
            'Classroom Supplies',
            'Laboratory Supplies',
            'Computer Supplies',
            'Cleaning Supplies',
            'Pantry Supplies',
            'Maintenance Supplies',
            'Furniture',
            'Others',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => str()->slug($name)],
                ['name' => $name, 'description' => "{$name} for PECIT campuses."],
            );
        }

        Category::firstOrCreate(
            ['slug' => 'uniforms'],
            ['name' => 'Uniforms', 'description' => 'Student uniforms and related items for the shop.'],
        );

        $departments = [
            ['College of Computer Studies', 'CCS'],
            ['College of Criminology', 'CC'],
            ['College of Tourism and Hospitality Management', 'CTHM'],
            ['College of Teacher Education', 'CTE'],
            ['College of Business Administration', 'CBA'],
            ['Senior High School', 'SHS'],
            ['Administration', 'ADMIN'],
            ['Supply Office', 'SUPPLY'],
        ];

        foreach ($departments as [$name, $code]) {
            Department::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'is_active' => true, 'faculty_budget_limit' => 10000],
            );
        }

        $suppliers = [
            [
                'supplier_code' => 'SUP-0001',
                'name' => 'Cebu Paper & Office Supply',
                'contact_person' => 'Maria Reyes',
                'phone' => '032-255-1001',
                'email' => 'sales@cpos-demo.pecit.local',
                'address' => 'Colon St., Cebu City',
            ],
            [
                'supplier_code' => 'SUP-0002',
                'name' => 'Visayas Uniform House',
                'contact_person' => 'Jose Tan',
                'phone' => '032-255-1002',
                'email' => 'orders@vuh-demo.pecit.local',
                'address' => 'Mandaue City, Cebu',
            ],
            [
                'supplier_code' => 'SUP-0003',
                'name' => 'Island Tech Computer Trading',
                'contact_person' => 'Ana Cruz',
                'phone' => '032-255-1003',
                'email' => 'support@itct-demo.pecit.local',
                'address' => 'IT Park, Lahug, Cebu City',
            ],
            [
                'supplier_code' => 'SUP-0004',
                'name' => 'Campus Care Janitorial Supply',
                'contact_person' => 'Pedro Santos',
                'phone' => '032-255-1004',
                'email' => 'hello@ccjs-demo.pecit.local',
                'address' => 'Talisay City, Cebu',
            ],
        ];

        foreach ($suppliers as $row) {
            Supplier::updateOrCreate(
                ['supplier_code' => $row['supplier_code']],
                $row + ['is_active' => true],
            );
        }
    }
}
