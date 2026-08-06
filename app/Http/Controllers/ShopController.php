<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Inventory::with(['category', 'department'])
            ->forStudentShop($user);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where('item_name', 'like', "%{$search}%");
        }

        return view('shop.index', [
            'items' => $query->orderBy('item_name')->paginate(12)->withQueryString(),
            'cart' => session('cart', []),
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'inventory_id' => ['required', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = Inventory::findOrFail($data['inventory_id']);

        if (! $item->isAvailableInStudentShop($request->user())) {
            return back()->with('error', 'That item is not available for your department.');
        }

        $cart = session('cart', []);
        $cart[$data['inventory_id']] = ($cart[$data['inventory_id']] ?? 0) + $data['quantity'];
        session(['cart' => $cart]);

        return back()->with('success', 'Item added to cart.');
    }

    public function cart(): View
    {
        $user = request()->user();
        $cart = session('cart', []);
        $items = Inventory::whereIn('id', array_keys($cart))->get()->keyBy('id');

        // Drop cart lines the student is no longer allowed to buy.
        foreach (array_keys($cart) as $inventoryId) {
            $item = $items->get($inventoryId);
            if (! $item || ! $item->isAvailableInStudentShop($user)) {
                unset($cart[$inventoryId]);
                $items->forget($inventoryId);
            }
        }
        session(['cart' => $cart]);

        return view('shop.cart', compact('cart', 'items'));
    }

    public function removeFromCart(int $inventoryId): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$inventoryId]);
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed from cart.');
    }
}
