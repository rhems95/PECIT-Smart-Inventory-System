<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Inventory::with(['category', 'department', 'sizeStocks'])
            ->forStudentShop($user);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('item_name', 'like', "%{$search}%");
        }

        $cart = $this->normalizedCart(session('cart', []));
        session(['cart' => $cart]);

        return view('shop.index', [
            'items' => $query->orderBy('item_name')->paginate(12)->withQueryString(),
            'cart' => $cart,
            'sizes' => config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']),
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $sizes = config('psis.uniform_sizes', ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL']);

        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['nullable', 'string', Rule::in($sizes)],
        ]);

        $item = Inventory::with('sizeStocks')->findOrFail($data['inventory_id']);

        if (! $item->isAvailableInStudentShop($request->user())) {
            return back()->with('error', 'That item is not available for your department.');
        }

        $size = null;
        if ($item->requiresSize()) {
            if (empty($data['size'])) {
                return back()->with('error', 'Please choose a size before adding this uniform to your cart.');
            }
            $size = $data['size'];
        }

        $qty = (int) $data['quantity'];
        $available = $item->availableQuantity($size);
        if ($available < $qty) {
            $label = $size ? "size {$size}" : 'stock';
            return back()->with('error', "Only {$available} available for {$label}.");
        }

        $cart = $this->normalizedCart(session('cart', []));
        $lineKey = $this->cartLineKey((int) $data['inventory_id'], $size);

        $existingQty = $cart[$lineKey]['quantity'] ?? 0;
        if ($available < $existingQty + $qty) {
            $label = $size ? "size {$size}" : 'stock';
            return back()->with('error', "Only {$available} available for {$label} (including items already in your cart).");
        }
        $cart[$lineKey] = [
            'inventory_id' => (int) $data['inventory_id'],
            'size' => $size,
            'quantity' => $existingQty + (int) $data['quantity'],
        ];

        session(['cart' => $cart]);

        $sizeNote = $size ? " (size {$size})" : '';

        return back()->with('success', "Item{$sizeNote} added to cart.");
    }

    public function cart(): View
    {
        $user = request()->user();
        $cart = $this->normalizedCart(session('cart', []));
        $inventoryIds = collect($cart)->pluck('inventory_id')->unique()->values()->all();
        $items = Inventory::whereIn('id', $inventoryIds)->with('sizeStocks')->get()->keyBy('id');

        // Drop cart lines the student is no longer allowed to buy, or missing required size / stock.
        foreach ($cart as $lineKey => $line) {
            $item = $items->get($line['inventory_id']);
            if (! $item || ! $item->isAvailableInStudentShop($user)) {
                unset($cart[$lineKey]);
                continue;
            }
            if ($item->requiresSize() && empty($line['size'])) {
                unset($cart[$lineKey]);
                continue;
            }
            if ($item->availableQuantity($line['size'] ?? null) < (int) $line['quantity']) {
                // Keep the line but clamp isn't needed — checkout will reject; drop if zero available.
                if ($item->availableQuantity($line['size'] ?? null) <= 0) {
                    unset($cart[$lineKey]);
                }
            }
        }
        session(['cart' => $cart]);

        return view('shop.cart', compact('cart', 'items'));
    }

    public function removeFromCart(string $lineKey): RedirectResponse
    {
        $cart = $this->normalizedCart(session('cart', []));
        unset($cart[$lineKey]);
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * @param  array<string, mixed>  $cart
     * @return array<string, array{inventory_id: int, size: string|null, quantity: int}>
     */
    protected function normalizedCart(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $key => $value) {
            // Legacy cart: inventory_id => quantity (int) — drop so size can be required.
            if (! is_array($value)) {
                continue;
            }

            $inventoryId = (int) ($value['inventory_id'] ?? 0);
            $quantity = (int) ($value['quantity'] ?? 0);
            if ($inventoryId <= 0 || $quantity <= 0) {
                continue;
            }

            $size = $value['size'] ?? null;
            if (is_string($size)) {
                $size = strtoupper(trim($size));
                if ($size === '') {
                    $size = null;
                }
            } else {
                $size = null;
            }

            $lineKey = $this->cartLineKey($inventoryId, $size);
            if (isset($normalized[$lineKey])) {
                $normalized[$lineKey]['quantity'] += $quantity;
            } else {
                $normalized[$lineKey] = [
                    'inventory_id' => $inventoryId,
                    'size' => $size,
                    'quantity' => $quantity,
                ];
            }
        }

        return $normalized;
    }

    protected function cartLineKey(int $inventoryId, ?string $size): string
    {
        return $inventoryId.'_'.($size ?: 'NONE');
    }
}
