@extends('layouts.psis')

@section('page-title', $user->exists ? 'Edit User' : 'New User')

@section('content')
<form
    method="POST"
    action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
    class="psis-card p-6 max-w-xl space-y-3"
    x-data="{ role: '{{ old('role', $user->getRoleNames()->first() ?? 'Administrator') }}' }"
>
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    <div>
        <label class="psis-label">Name</label>
        <input name="name" value="{{ old('name', $user->name) }}" class="psis-input" placeholder="Full name" required>
    </div>

    <div x-show="role === 'Student'" x-cloak>
        <label class="psis-label">Last Name</label>
        <input name="last_name" value="{{ old('last_name', $user->last_name) }}" class="psis-input" placeholder="Used for student login" x-bind:required="role === 'Student'">
        <p class="text-xs text-slate-500 mt-1">Students sign in with Student ID + last name.</p>
    </div>

    <div>
        <label class="psis-label">Email</label>
        <input name="email" type="email" value="{{ old('email', $user->email) }}" class="psis-input" placeholder="Email (notifications)" required>
        <p class="text-xs text-slate-500 mt-1" x-show="role === 'Student'" x-cloak>Kept for notifications; students do not use email to log in.</p>
    </div>

    <div>
        <label class="psis-label" x-text="role === 'Student' ? 'Student ID' : 'Employee ID'"></label>
        <input name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" class="psis-input" placeholder="ID number" x-bind:required="role === 'Student'">
    </div>

    <div>
        <label class="psis-label">Phone</label>
        <input name="phone" value="{{ old('phone', $user->phone) }}" class="psis-input" placeholder="Phone">
    </div>

    <div>
        <label class="psis-label">Department</label>
        <select name="department_id" class="psis-input" x-bind:required="role === 'Student'">
            <option value="">No department</option>
            @foreach (\App\Models\Department::orderBy('name')->get() as $dept)
                <option value="{{ $dept->id }}" @selected(old('department_id', $user->department_id) == $dept->id)>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="psis-label">Role</label>
        <select name="role" class="psis-input" required x-model="role">
            @foreach (['Administrator', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'] as $role)
                <option value="{{ $role }}" @selected(old('role', $user->getRoleNames()->first()) == $role)>
                    {{ $role }}
                </option>
            @endforeach
        </select>
    </div>

    <div x-show="role !== 'Student'" x-cloak>
        <label class="psis-label">Password</label>
        <input
            name="password"
            type="password"
            class="psis-input"
            placeholder="{{ $user->exists ? 'New password (optional)' : 'Password' }}"
            x-bind:required="role !== 'Student' && {{ $user->exists ? 'false' : 'true' }}"
        >
    </div>

    <p class="text-xs text-slate-500" x-show="role === 'Student'" x-cloak>
        Students log in with Student ID and last name only. A system password is generated automatically if blank.
    </p>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
        Active
    </label>

    <button type="submit" class="psis-btn-primary">Save User</button>
    <a href="{{ route('admin.users.index') }}" class="psis-btn-outline ml-2">Cancel</a>
</form>
@endsection
