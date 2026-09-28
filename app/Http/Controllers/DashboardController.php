<?php

namespace App\Http\Controllers;

use App\Enums\ReceivingInspectionStatus;
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
        $isStudent = $user->hasRole('Student');

        $announcements = Announcement::active()
            ->with('creator')
            ->latest('published_at')
            ->limit(5)
            ->get();

        if ($isStudent) {
            $studentPurchases = PurchaseRequest::with('payments')->where('user_id', $user->id)->latest()->limit(5)->get();
            $shopCount = Inventory::forStudentShop($user)->count();
            $aiSummary = 'Buy uniforms for your department in the Uniform Shop. Shared items (P.E., NSTP, and ID lanyard) are available to all students.';

            return view('dashboard.index', [
                'isStudent' => true,
                'stats' => [],
                'chartLabels' => [],
                'chartData' => [],
                'recentRequests' => collect(),
                'forecasts' => [],
                'aiSummary' => $aiSummary,
                'studentPurchases' => $studentPurchases,
                'shopCount' => $shopCount,
                'announcements' => $announcements,
                'releasedRequests' => collect(),
                'recentStudentPurchases' => collect(),
                'recentVerifiedPayments' => collect(),
                'availableStockItems' => collect(),
            ]);
        }

        $inventory = Inventory::query()
            ->with('sizeStocks')
            ->orderBy('item_name')
            ->get();

        $stats = [
            'total_items' => $inventory->count(),
            'total_quantity' => $inventory->sum('quantity'),
            'available_stock' => $inventory->sum(fn (Inventory $i) => $i->availableQuantity()),
            'reserved_stock' => (int) $inventory->sum('reserved_quantity'),
            'low_stock' => $inventory->filter(fn (Inventory $i) => $i->isLowStock())->count(),
            'out_of_stock' => $inventory->filter(fn (Inventory $i) => $i->isOutOfStock())->count(),
            'pending_requests' => SupplyRequest::whereIn('status', ['pending', 'accounting_review', 'admin_review'])->count(),
            'approved_requests' => SupplyRequest::where('status', 'approved')->count(),
            'released_requests' => SupplyRequest::where('status', 'released')->count(),
            'monthly_transactions' => Transaction::where('created_at', '>=', now()->startOfMonth())->count(),
            'today_movements' => Transaction::query()
                ->whereDate('created_at', now()->toDateString())
                ->whereNotIn('type', ['reserve', 'restore'])
                ->count(),
            'ready_faculty' => 0,
            'ready_students' => 0,
            'inspect_today' => 0,
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
        $releasedRequests = SupplyRequest::with(['user', 'items.inventory'])
            ->where('status', 'released')
            ->latest('released_at')
            ->limit(10)
            ->get();
        $recentStudentPurchases = collect();
        if ($user->hasAnyRole(['Supply Personnel', 'Administrator'])) {
            $recentStudentPurchases = PurchaseRequest::with(['user.department'])
                ->latest()
                ->limit(10)
                ->get();
        }

        $recentVerifiedPayments = collect();
        if ($user->hasRole('Accounting')) {
            $recentVerifiedPayments = PurchaseRequest::query()->recentlyVerified(10)->get();
        }

        $availableStockItems = $inventory->map(fn (Inventory $item) => [
            'name' => $item->item_name,
            'available' => $item->availableQuantity(),
        ])->values();

        $forecasts = $ai->inventoryForecasts(5);
        $aiSummary = $ai->monthlySummary();
        $demandMemory = $ai->monthlyDemandMemory();

        $showSupplyWorkQueue = $user->hasAnyRole(['Supply Personnel', 'Administrator']);
        $readyFacultyReleases = collect();
        $readyStudentReleases = collect();
        $pendingInspections = collect();
        $actionableLowStock = collect();

        if ($showSupplyWorkQueue) {
            $readyFacultyReleases = SupplyRequest::query()
                ->with(['user.department', 'department'])
                ->whereIn('status', ['approved', 'reserved'])
                ->orderBy('id')
                ->limit(8)
                ->get();
            $readyStudentReleases = PurchaseRequest::query()
                ->with(['user.department'])
                ->where('status', 'payment_verified')
                ->orderBy('id')
                ->limit(8)
                ->get();
            $pendingInspections = Transaction::query()
                ->with(['inventory', 'supplier'])
                ->purchaseHistory()
                ->where(function ($query) {
                    $query->where('inspection_status', ReceivingInspectionStatus::Pending->value)
                        ->orWhereNull('inspection_status');
                })
                ->latest('id')
                ->limit(8)
                ->get();
            $actionableLowStock = $inventory
                ->filter(fn (Inventory $i) => $i->isLowStock() || $i->isOutOfStock())
                ->sortBy(fn (Inventory $i) => $i->availableQuantity())
                ->take(5)
                ->values();

            $stats['ready_faculty'] = SupplyRequest::query()->whereIn('status', ['approved', 'reserved'])->count();
            $stats['ready_students'] = PurchaseRequest::query()->where('status', 'payment_verified')->count();
            $stats['inspect_today'] = Transaction::query()
                ->purchaseHistory()
                ->where(function ($query) {
                    $query->where('inspection_status', ReceivingInspectionStatus::Pending->value)
                        ->orWhereNull('inspection_status');
                })
                ->count();
        }

        return view('dashboard.index', [
            'isStudent' => false,
            'stats' => $stats,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'recentRequests' => $recentRequests,
            'releasedRequests' => $releasedRequests,
            'forecasts' => $forecasts,
            'demandMemory' => $demandMemory,
            'aiSummary' => $aiSummary,
            'studentPurchases' => collect(),
            'shopCount' => 0,
            'announcements' => $announcements,
            'recentStudentPurchases' => $recentStudentPurchases,
            'recentVerifiedPayments' => $recentVerifiedPayments,
            'availableStockItems' => $availableStockItems,
            'showSupplyWorkQueue' => $showSupplyWorkQueue,
            'readyFacultyReleases' => $readyFacultyReleases,
            'readyStudentReleases' => $readyStudentReleases,
            'pendingInspections' => $pendingInspections,
            'actionableLowStock' => $actionableLowStock,
        ]);
    }
}
