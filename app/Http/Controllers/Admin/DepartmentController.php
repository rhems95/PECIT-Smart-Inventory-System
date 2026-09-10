<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('admin.departments.index', [
            'departments' => Department::withCount(['users', 'supplyRequests'])->latest()->paginate(15),
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
        ]);

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
        ]);

        $before = $department->only(['name', 'code']);
        $department->update($data);
        $auditLog->log($request->user(), 'department.updated', $department, $before, [
            'name' => $department->name,
            'code' => $department->code,
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
