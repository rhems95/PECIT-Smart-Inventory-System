<?php

use App\Models\Department;
use App\Models\Inventory;
use App\Models\SupplyRequest;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $keep = [
            'CCS' => 'College of Computer Studies',
            'CC' => 'College of Criminology',
            'CTHM' => 'College of Tourism and Hospitality Management',
            'CTE' => 'College of Teacher Education',
            'CBA' => 'College of Business Administration',
            'SHS' => 'Senior High School',
            'ADMIN' => 'Administration',
            'SUPPLY' => 'Supply Office',
        ];

        foreach ($keep as $code => $name) {
            Department::query()->updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'is_active' => true],
            );
        }

        $ids = Department::query()->whereIn('code', array_keys($keep))->pluck('id', 'code');

        foreach (['CIT' => 'CCS', 'COE' => 'CC', 'COB' => 'CBA', 'COLLEG' => 'CCS'] as $fromCode => $toCode) {
            $from = Department::query()->where('code', $fromCode)->first();
            $toId = $ids[$toCode] ?? null;
            if (! $from || ! $toId || (int) $from->id === (int) $toId) {
                continue;
            }

            User::query()->where('department_id', $from->id)->update(['department_id' => $toId]);
            Inventory::query()->where('department_id', $from->id)->update(['department_id' => $toId]);
            SupplyRequest::query()->where('department_id', $from->id)->update(['department_id' => $toId]);
        }

        if (! User::query()->where('employee_id', 'STU-CC-001')->exists()) {
            User::query()->where('employee_id', 'STU-COE-001')->update(['employee_id' => 'STU-CC-001']);
        }

        $this->relabelExclusive('UNI-CCS', $ids['CCS'] ?? null, 'Computer Studies Uniform (Exclusive)', 'Exclusive to College of Computer Studies students only.');
        $this->retireDuplicate('UNI-CIT');

        $this->renameExclusive('UNI-COE', 'UNI-CC', $ids['CC'] ?? null, 'Criminology Uniform (Exclusive)', 'Exclusive to College of Criminology students only.');
        $this->renameExclusive('UNI-COB', 'UNI-CBA', $ids['CBA'] ?? null, 'Business Administration Uniform (Exclusive)', 'Exclusive to College of Business Administration students only.');

        Department::query()
            ->whereNotIn('code', array_keys($keep))
            ->get()
            ->each(function (Department $department) {
                if (
                    $department->users()->exists()
                    || $department->supplyRequests()->exists()
                    || $department->inventoryItems()->exists()
                ) {
                    $department->update(['is_active' => false]);

                    return;
                }

                $department->delete();
            });
    }

    public function down(): void
    {
        // Academic department list is forward-only.
    }

    protected function relabelExclusive(?string $code, ?int $departmentId, string $name, string $description): void
    {
        if (! $code || ! $departmentId) {
            return;
        }

        Inventory::query()->where('item_code', $code)->update([
            'item_name' => $name,
            'description' => $description,
            'department_id' => $departmentId,
        ]);
    }

    protected function retireDuplicate(string $itemCode): void
    {
        Inventory::query()->where('item_code', $itemCode)->update([
            'student_shop' => false,
            'status' => 'discontinued',
        ]);
    }

    protected function renameExclusive(string $fromCode, string $toCode, ?int $departmentId, string $name, string $description): void
    {
        $from = Inventory::query()->where('item_code', $fromCode)->first();
        if (! $from || ! $departmentId) {
            return;
        }

        $payload = [
            'item_name' => $name,
            'description' => $description,
            'department_id' => $departmentId,
            'student_shop' => true,
        ];

        if (! Inventory::query()->where('item_code', $toCode)->exists()) {
            $payload['item_code'] = $toCode;
        }

        $from->update($payload);
    }
};
