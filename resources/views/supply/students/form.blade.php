@extends('layouts.psis')

@section('title', ($student->exists ? 'Edit Student' : 'Add Student').' — PSIS')
@section('page-title', $student->exists ? 'Edit Student' : 'Add Student')

@section('content')
<form
    method="POST"
    action="{{ $student->exists ? route('supply.students.update', $student) : route('supply.students.store') }}"
    class="psis-card p-6 max-w-xl space-y-3"
>
    @csrf
    @if ($student->exists)
        @method('PUT')
    @endif

    <div>
        <label class="psis-label">Student ID</label>
        <input name="employee_id" value="{{ old('employee_id', $student->employee_id) }}" class="psis-input" placeholder="e.g. 2024-00001" required>
        <p class="text-xs text-slate-500 mt-1">Used with last name for student login.</p>
    </div>

    <div>
        <label class="psis-label">Full Name</label>
        <input name="name" value="{{ old('name', $student->name) }}" class="psis-input" placeholder="Maria Santos" required>
    </div>

    <div>
        <label class="psis-label">Last Name</label>
        <input name="last_name" value="{{ old('last_name', $student->last_name) }}" class="psis-input" placeholder="Santos" required>
        <p class="text-xs text-slate-500 mt-1">Login credential with Student ID.</p>
    </div>

    <div>
        <label class="psis-label">Email</label>
        <input name="email" type="email" value="{{ old('email', $student->email) }}" class="psis-input" placeholder="student@pecit.edu.ph" required>
        <p class="text-xs text-slate-500 mt-1">For notifications only — not used for login.</p>
    </div>

    <div>
        <label class="psis-label">Department</label>
        <select name="department_id" class="psis-input" required>
            <option value="">Select department</option>
            @foreach ($departments as $dept)
                <option value="{{ $dept->id }}" @selected(old('department_id', $student->department_id) == $dept->id)>
                    {{ $dept->name }} ({{ $dept->code }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="psis-label">Phone</label>
        <input name="phone" value="{{ old('phone', $student->phone) }}" class="psis-input" placeholder="Optional">
    </div>

    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $student->is_active ?? true))>
        Active
    </label>

    <div class="pt-2">
        <button type="submit" class="psis-btn-primary">{{ $student->exists ? 'Save Changes' : 'Create Student' }}</button>
        <a href="{{ route('supply.students.index') }}" class="psis-btn-outline ml-2">Cancel</a>
    </div>
</form>
@endsection
