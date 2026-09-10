<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
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
                ['name' => $name, 'is_active' => true],
            );
        }
    }
}
