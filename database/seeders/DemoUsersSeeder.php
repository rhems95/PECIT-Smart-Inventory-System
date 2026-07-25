<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminDept = Department::where('name', 'Administration')->first();
        $supplyDept = Department::where('name', 'Supply Office')->first();
        $engDept = Department::where('name', 'College of Engineering')->first();
        $itDept = Department::where('name', 'College of Information Technology')->first();

        $users = [
            ['Administrator', 'PECIT Admin', 'admin@pecit.edu.ph', 'ADM-001', $adminDept?->id],
            ['Accounting', 'PECIT Accounting', 'accounting@pecit.edu.ph', 'ACC-001', $adminDept?->id],
            ['Supply Personnel', 'Supply Officer', 'supply@pecit.edu.ph', 'SUP-001', $supplyDept?->id],
            ['Faculty', 'Prof. Juan Dela Cruz', 'faculty@pecit.edu.ph', 'FAC-001', $engDept?->id],
            ['Student', 'Maria Santos', 'student@pecit.edu.ph', 'STU-001', $itDept?->id],
        ];

        foreach ($users as [$role, $name, $email, $employeeId, $departmentId]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    // Plain value: User model casts password as hashed.
                    'password' => 'password',
                    'employee_id' => $employeeId,
                    'department_id' => $departmentId,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$role]);
        }
    }
}
