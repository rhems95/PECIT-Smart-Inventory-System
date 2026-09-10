<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StudentAccountService
{
    public function __construct(
        protected AuditLogService $auditLog,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     last_name: string,
     *     email: string,
     *     employee_id: string,
     *     department_id: int,
     *     phone?: string|null,
     *     is_active?: bool
     * }  $data
     */
    public function create(array $data, ?User $actor = null): User
    {
        return DB::transaction(function () use ($data, $actor) {
            $user = User::create([
                'name' => $data['name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'employee_id' => $data['employee_id'],
                'department_id' => $data['department_id'],
                'phone' => $data['phone'] ?? null,
                'password' => Str::password(32),
                'is_active' => $data['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            $user->syncRoles(['Student']);

            if ($actor) {
                $this->auditLog->log($actor, 'student.created', $user, null, [
                    'employee_id' => $user->employee_id,
                    'email' => $user->email,
                ]);
            }

            return $user;
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     last_name: string,
     *     email: string,
     *     employee_id: string,
     *     department_id: int,
     *     phone?: string|null,
     *     is_active?: bool
     * }  $data
     */
    public function update(User $student, array $data, ?User $actor = null): User
    {
        if (! $student->hasRole('Student')) {
            throw new RuntimeException('Only student accounts can be updated here.');
        }

        $student->update([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'employee_id' => $data['employee_id'],
            'department_id' => $data['department_id'],
            'phone' => $data['phone'] ?? null,
            'is_active' => $data['is_active'] ?? $student->is_active,
        ]);

        $student->syncRoles(['Student']);

        if ($actor) {
            $this->auditLog->log($actor, 'student.updated', $student);
        }

        return $student->fresh(['department', 'roles']);
    }

    /**
     * Import students from a CSV file.
     *
     * Expected headers: student_id, last_name, name, email, department_code, phone
     *
     * @return array{created: int, skipped: int, errors: array<int, string>}
     */
    public function importFromCsv(UploadedFile $file, ?User $actor = null): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            throw ValidationException::withMessages(['file' => 'Could not read the uploaded file.']);
        }

        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV file is empty.']);
        }

        $headers = array_map(fn ($h) => Str::of((string) $h)->lower()->trim()->replace(' ', '_')->toString(), $headerRow);
        $required = ['student_id', 'last_name', 'name', 'email', 'department_code'];

        foreach ($required as $column) {
            if (! in_array($column, $headers, true)) {
                fclose($handle);
                throw ValidationException::withMessages([
                    'file' => "Missing required CSV column: {$column}. Download the template for the correct format.",
                ]);
            }
        }

        $departments = Department::query()->active()->get()->keyBy(fn (Department $d) => Str::upper($d->code));
        $created = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $data = [];
            foreach ($headers as $index => $header) {
                $data[$header] = isset($row[$index]) ? trim((string) $row[$index]) : '';
            }

            $studentId = $data['student_id'] ?? '';
            $lastName = $data['last_name'] ?? '';
            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $deptCode = Str::upper($data['department_code'] ?? '');
            $phone = ($data['phone'] ?? '') !== '' ? $data['phone'] : null;

            if ($studentId === '' || $lastName === '' || $name === '' || $email === '' || $deptCode === '') {
                $errors[] = "Row {$rowNumber}: missing required values.";
                $skipped++;

                continue;
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowNumber}: invalid email ({$email}).";
                $skipped++;

                continue;
            }

            $department = $departments->get($deptCode);
            if (! $department) {
                $errors[] = "Row {$rowNumber}: unknown department_code ({$deptCode}).";
                $skipped++;

                continue;
            }

            if (User::where(function ($q) use ($studentId, $email) {
                $q->where('employee_id', $studentId)->orWhere('email', $email);
            })->exists()) {
                $errors[] = "Row {$rowNumber}: student ID or email already exists ({$studentId} / {$email}).";
                $skipped++;

                continue;
            }

            try {
                $this->create([
                    'name' => $name,
                    'last_name' => $lastName,
                    'email' => $email,
                    'employee_id' => $studentId,
                    'department_id' => $department->id,
                    'phone' => $phone,
                    'is_active' => true,
                ], $actor);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNumber}: ".$e->getMessage();
                $skipped++;
            }
        }

        fclose($handle);

        return compact('created', 'skipped', 'errors');
    }

    public function templateCsv(): string
    {
        $lines = [
            'student_id,last_name,name,email,department_code,phone',
            '2024-00001,Santos,Maria Santos,maria.santos@pecit.edu.ph,CCS,09171234567',
            '2024-00002,Reyes,Juan Reyes,juan.reyes@pecit.edu.ph,CC,',
        ];

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<int, mixed>  $row
     */
    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
