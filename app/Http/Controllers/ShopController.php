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
        $query = Inventory::with('category')->where('status', '!=', 'discontinued');

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

        $cart = session('cart', []);
        $cart[$data['inventory_id']] = ($cart[$data['inventory_id']] ?? 0) + $data['quantity'];
        session(['cart' => $cart]);

        return back()->with('success', 'Item added to cart.');
    }

    public function cart(): View
    {
        $cart = session('cart', []);
        $items = Inventory::whereIn('id', array_keys($cart))->get()->keyBy('id');

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
