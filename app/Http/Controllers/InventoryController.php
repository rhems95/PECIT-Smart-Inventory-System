<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Inventory::with(['category', 'supplier']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $items = $query->latest()->paginate(15)->withQueryString();

        return view('inventory.index', [
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Inventory::class);

        return view('inventory.create', [
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Inventory::class);

        $data = $request->validate([
            'item_code' => ['required', 'string', 'max:50', 'unique:inventory,item_code'],
            'item_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $item = Inventory::create($data + ['reserved_quantity' => 0]);
        $item->updateStatus();

        return redirect()->route('inventory.index')->with('success', 'Inventory item created.');
    }

    public function show(Inventory $inventory): View
    {
        $inventory->load(['category', 'supplier', 'transactions' => fn ($q) => $q->latest()->limit(10)]);

        $qrSvg = QrCode::size(120)->generate($inventory->item_code);

        return view('inventory.show', compact('inventory', 'qrSvg'));
    }

    public function edit(Inventory $inventory): View
    {
        $this->authorize('update', $inventory);

        return view('inventory.edit', [
            'inventory' => $inventory,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        $this->authorize('update', $inventory);

        $data = $request->validate([
            'item_code' => ['required', 'string', 'max:50', 'unique:inventory,item_code,'.$inventory->id],
            'item_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:available,low_stock,out_of_stock,discontinued'],
        ]);

        $inventory->update($data);
        if (! isset($data['status']) || $data['status'] !== 'discontinued') {
            $inventory->updateStatus();
        }

        return redirect()->route('inventory.show', $inventory)->with('success', 'Inventory item updated.');
    }
}
