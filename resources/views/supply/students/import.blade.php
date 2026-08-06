@extends('layouts.psis')

@section('title', 'Import Students — PSIS')
@section('page-title', 'Import Students')

@section('content')
<div class="grid lg:grid-cols-2 gap-6">
    <form method="POST" action="{{ route('supply.students.import.store') }}" enctype="multipart/form-data" class="psis-card p-6 space-y-4">
        @csrf

        <div>
            <label class="psis-label">CSV file</label>
            <input type="file" name="file" accept=".csv,text/csv" class="psis-input" required>
            <p class="text-xs text-slate-500 mt-1">Max 2 MB. Use the template for the correct columns.</p>
            @error('file')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="psis-btn-primary">Upload &amp; Import</button>
            <a href="{{ route('supply.students.template') }}" class="psis-btn-outline">Download Template</a>
            <a href="{{ route('supply.students.index') }}" class="psis-btn-outline">Back</a>
        </div>
    </form>

    <div class="psis-card p-6 space-y-3 text-sm">
        <h3 class="font-semibold text-base">CSV format</h3>
        <p>Required columns (header row):</p>
        <code class="block text-xs bg-slate-50 dark:bg-slate-900/50 p-3 rounded overflow-x-auto">student_id,last_name,name,email,department_code,phone</code>
        <ul class="list-disc pl-5 space-y-1 text-slate-600 dark:text-slate-300">
            <li><strong>student_id</strong> — unique ID used for login</li>
            <li><strong>last_name</strong> — login credential with student ID</li>
            <li><strong>name</strong> — full display name</li>
            <li><strong>email</strong> — for notifications only</li>
            <li><strong>department_code</strong> — e.g. CIT, COE, COB, SHS</li>
            <li><strong>phone</strong> — optional</li>
        </ul>
        <p class="text-slate-500">Existing student IDs or emails are skipped. Department codes currently available:</p>
        <p class="text-xs">{{ $departments->map(fn ($d) => $d->code)->join(', ') ?: '—' }}</p>
    </div>
</div>
@endsection
