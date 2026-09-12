<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $adminDept = Department::where('code', 'ADMIN')->first()
            ?? Department::where('name', 'Administration')->first();
        $supplyDept = Department::where('code', 'SUPPLY')->first()
            ?? Department::where('name', 'Supply Office')->first();
        $ccsDept = Department::where('code', 'CCS')->first();
        $ccDept = Department::where('code', 'CC')->first();

        $users = [
            ['Administrator', 'PECIT Admin', null, 'admin@pecit.edu.ph', 'ADM-001', $adminDept?->id],
            ['Admission', 'PECIT Admission', null, 'admission@pecit.edu.ph', 'ADN-001', $adminDept?->id],
            ['Accounting', 'PECIT Accounting', null, 'accounting@pecit.edu.ph', 'ACC-001', $adminDept?->id],
            ['Supply Personnel', 'Supply Officer', null, 'supply@pecit.edu.ph', 'SUP-001', $supplyDept?->id],
            ['Faculty', 'Prof. Juan Dela Cruz', null, 'faculty@pecit.edu.ph', 'FAC-001', $ccsDept?->id],
            ['Student', 'Maria Santos', 'Santos', 'student@pecit.edu.ph', 'STU-001', $ccsDept?->id],
            ['Student', 'Carlos Mendoza', 'Mendoza', 'engineering.student@pecit.edu.ph', 'STU-CC-001', $ccDept?->id],
        ];

        foreach ($users as [$role, $name, $lastName, $email, $employeeId, $departmentId]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'last_name' => $lastName,
                    // Plain value: User model casts password as hashed.
                    'password' => 'password',
                    'employee_id' => $employeeId,
                    'department_id' => $departmentId,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->fill([
                'name' => $name,
                'last_name' => $lastName,
                'employee_id' => $employeeId,
                'department_id' => $departmentId,
                'is_active' => true,
            ])->save();

            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $user->syncRoles([$role]);
        }
    }
}
