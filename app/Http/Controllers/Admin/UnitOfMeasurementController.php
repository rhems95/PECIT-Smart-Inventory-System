<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitOfMeasurement;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitOfMeasurementController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Administrator', 'Supply Personnel']), 403);

        return view('admin.units.index', [
            'units' => UnitOfMeasurement::orderBy('name')->paginate(20),
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        abort_unless(auth()->user()?->hasAnyRole(['Administrator', 'Supply Personnel']), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:20', 'unique:units_of_measurement,symbol'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $unit = UnitOfMeasurement::create($data);
        $auditLog->log($request->user(), 'unit.created', $unit, null, ['symbol' => $unit->symbol]);

        return back()->with('success', 'Unit of measurement created.');
    }

    public function update(Request $request, UnitOfMeasurement $unit, AuditLogService $auditLog): RedirectResponse
    {
        abort_unless(auth()->user()?->hasAnyRole(['Administrator', 'Supply Personnel']), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:20', 'unique:units_of_measurement,symbol,'.$unit->id],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $unit->only(['name', 'symbol']);
        $unit->update($data);
        $auditLog->log($request->user(), 'unit.updated', $unit, $before, ['symbol' => $unit->symbol]);

        return back()->with('success', 'Unit of measurement updated.');
    }
}
