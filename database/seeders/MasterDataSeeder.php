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

        $departments = [
            ['College of Engineering', 'COE'],
            ['College of Information Technology', 'CIT'],
            ['College of Business', 'COB'],
            ['Senior High School', 'SHS'],
            ['Administration', 'ADMIN'],
            ['Supply Office', 'SUPPLY'],
        ];

        foreach ($departments as [$name, $code]) {
            Department::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        Supplier::firstOrCreate(['name' => 'National Book Store'], [
            'contact_person' => 'Procurement Desk',
            'email' => 'procurement@nbs.example',
            'phone' => '02-8000-0000',
            'address' => 'Metro Manila',
        ]);

        Supplier::firstOrCreate(['name' => 'Office Warehouse Inc.'], [
            'contact_person' => 'Sales Team',
            'email' => 'sales@owi.example',
            'phone' => '02-7000-0000',
            'address' => 'Quezon City',
        ]);
    }
}
