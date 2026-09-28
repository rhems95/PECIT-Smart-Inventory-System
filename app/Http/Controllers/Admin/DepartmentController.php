<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\AuditLogService;
use App\Services\FacultyBudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(FacultyBudgetService $budget): View
    {
        $period = $budget->period();
        $departments = Department::withCount(['users', 'supplyRequests'])
            ->withSum([
                'supplyRequests as faculty_budget_used' => function ($query) use ($period) {
                    $query->where('type', 'faculty')
                        ->whereIn('status', FacultyBudgetService::COUNTING_STATUSES)
                        ->whereBetween('created_at', [$period['starts_at'], $period['ends_at']]);
                },
            ], 'total_amount')
            ->latest()
            ->paginate(15);

        return view('admin.departments.index', compact('departments', 'period'));
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
            'faculty_budget_limit' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $data['faculty_budget_limit'] = $data['faculty_budget_limit'] ?? FacultyBudgetService::DEFAULT_LIMIT;

        $department = Department::create($data);
        $auditLog->log($request->user(), 'department.created', $department, null, [
            'name' => $department->name,
            'code' => $department->code,
        ]);

        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code,'.$department->id],
            'description' => ['nullable', 'string'],
            'faculty_budget_limit' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $before = $department->only(['name', 'code', 'faculty_budget_limit']);
        $department->update($data);
        $auditLog->log($request->user(), 'department.updated', $department, $before, [
            'name' => $department->name,
            'code' => $department->code,
            'faculty_budget_limit' => $department->faculty_budget_limit,
        ]);

        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department, AuditLogService $auditLog): RedirectResponse
    {
        if ($department->users()->exists() || $department->supplyRequests()->exists() || $department->inventoryItems()->exists()) {
            return back()->with('error', 'Cannot delete: department is assigned to users, requests, or inventory. Reassign them first.');
        }

        $auditLog->log(auth()->user(), 'department.deleted', null, [
            'name' => $department->name,
            'code' => $department->code,
        ]);

        $department->delete();

        return back()->with('success', 'Department deleted.');
    }
}
