<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['department', 'roles'])->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['role'] === 'Student' && empty($data['password'])) {
            $data['password'] = Str::password(32);
        }

        $user = User::create(collect($data)->except('role')->toArray());
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->syncRoles([$data['role']]);

        $auditLog->log($request->user(), 'user.created', $user, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user, AuditLogService $auditLog): RedirectResponse
    {
        $data = $this->validated($request, $user);
        $payload = collect($data)->except(['password', 'role'])->toArray();

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $before = $user->only(['name', 'email', 'employee_id', 'department_id', 'is_active']);
        $user->update($payload);
        $user->syncRoles([$data['role']]);

        $auditLog->log($request->user(), 'user.updated', $user, $before, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function auditLogs(): View
    {
        $logs = AuditLog::with(['user', 'auditable'])->latest()->paginate(20);

        return view('admin.audit-logs', compact('logs'));
    }

    protected function validated(Request $request, ?User $user = null): array
    {
        $isStudent = $request->input('role') === 'Student';

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)],
            'employee_id' => [
                $isStudent ? 'required' : 'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'employee_id')->ignore($user?->id),
            ],
            'department_id' => [$isStudent ? 'required' : 'nullable', 'exists:departments,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => [
                Rule::requiredIf(fn () => ! $user && ! $isStudent),
                'nullable',
                'string',
                'min:8',
            ],
            'role' => ['required', Rule::in(['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'])],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
