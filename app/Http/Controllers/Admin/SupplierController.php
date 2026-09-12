<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Supplier::class);

        return view('admin.suppliers.index', [
            'suppliers' => Supplier::latest()->paginate(15),
        ]);
    }

    public function store(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $data = $this->validated($request);
        $supplier = Supplier::create($data);
        $auditLog->log($request->user(), 'supplier.created', $supplier, null, [
            'name' => $supplier->name,
            'supplier_code' => $supplier->supplier_code,
        ]);

        return back()->with('success', 'Supplier created.');
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view', $supplier);

        $supplier->load(['transactions' => fn ($q) => $q->with('inventory')->latest('id')->limit(20)]);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $data = $this->validated($request, $supplier);
        $before = $supplier->only(['name', 'is_active', 'supplier_code']);
        $supplier->update($data);
        $auditLog->log($request->user(), 'supplier.updated', $supplier, $before, [
            'name' => $supplier->name,
        ]);

        return back()->with('success', 'Supplier updated.');
    }

    public function toggle(Request $request, Supplier $supplier, AuditLogService $auditLog): RedirectResponse
    {
        $this->authorize('update', $supplier);

        $supplier->is_active = ! $supplier->is_active;
        $supplier->save();
        $auditLog->log($request->user(), 'supplier.toggled', $supplier, null, [
            'name' => $supplier->name,
            'is_active' => $supplier->is_active,
        ]);

        return back()->with('success', $supplier->is_active ? 'Supplier activated.' : 'Supplier deactivated.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Supplier $supplier = null): array
    {
        $data = $request->validate([
            'supplier_code' => ['nullable', 'string', 'max:30', 'unique:suppliers,supplier_code'.($supplier ? ','.$supplier->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (empty($data['supplier_code'])) {
            unset($data['supplier_code']);
        }

        if (! $supplier) {
            $data['is_active'] = $request->boolean('is_active', true);
        } elseif ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        return $data;
    }
}
