@extends('layouts.psis')

@section('page-title', $user->exists ? 'Edit User' : 'New User')

@section('content')
<form
    method="POST"
    action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
    class="psis-card p-6 max-w-xl space-y-3"
>
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    <div>
        <label class="psis-label">Name</label>
        <input name="name" value="{{ old('name', $user->name) }}" class="psis-input" placeholder="Full name" required>
    </div>

    <div>
        <label class="psis-label">Email</label>
        <input name="email" type="email" value="{{ old('email', $user->email) }}" class="psis-input" placeholder="Email" required>
    </div>

    <div>
        <label class="psis-label">Employee ID</label>
        <input name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" class="psis-input" placeholder="Employee ID">
    </div>

    <div>
        <label class="psis-label">Phone</label>
        <input name="phone" value="{{ old('phone', $user->phone) }}" class="psis-input" placeholder="Phone">
    </div>

    <div>
        <label class="psis-label">Department</label>
        <select name="department_id" class="psis-input">
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
        <select name="role" class="psis-input" required>
            @foreach (['Administrator', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'] as $role)
                <option value="{{ $role }}" @selected(old('role', $user->getRoleNames()->first()) == $role)>
                    {{ $role }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="psis-label">Password</label>
        <input
            name="password"
            type="password"
            class="psis-input"
            placeholder="{{ $user->exists ? 'New password (optional)' : 'Password' }}"
            @if (! $user->exists) required @endif
        >
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
        Active
    </label>

    <button type="submit" class="psis-btn-primary">Save User</button>
    <a href="{{ route('admin.users.index') }}" class="psis-btn-outline ml-2">Cancel</a>
</form>
@endsection
