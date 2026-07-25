<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Services\AiInsightService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AiInsightService $ai): View
    {
        $user = Auth::user();
        $inventory = Inventory::all();

        $stats = [
            'total_items' => $inventory->count(),
            'total_quantity' => $inventory->sum('quantity'),
            'available_stock' => $inventory->sum(fn (Inventory $i) => $i->availableQuantity()),
            'low_stock' => $inventory->filter(fn (Inventory $i) => $i->isLowStock())->count(),
            'out_of_stock' => $inventory->filter(fn (Inventory $i) => $i->isOutOfStock())->count(),
            'pending_requests' => SupplyRequest::whereIn('status', ['pending', 'accounting_review', 'admin_review'])->count(),
            'approved_requests' => SupplyRequest::where('status', 'approved')->count(),
            'released_requests' => SupplyRequest::where('status', 'released')->count(),
            'monthly_transactions' => Transaction::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        $chartLabels = [];
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartLabels[] = $month->format('M Y');
            $chartData[] = Transaction::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        $recentRequests = SupplyRequest::with('user')->latest()->limit(5)->get();
        $forecasts = $ai->inventoryForecasts(5);
        $aiSummary = $ai->monthlySummary();

        $studentPurchases = $user->hasRole('Student')
            ? PurchaseRequest::where('user_id', $user->id)->latest()->limit(5)->get()
            : collect();

        $announcements = Announcement::active()
            ->with('creator')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'chartLabels',
            'chartData',
            'recentRequests',
            'forecasts',
            'aiSummary',
            'studentPurchases',
            'announcements',
        ));
    }
}
