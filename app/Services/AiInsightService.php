<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Payment;
use App\Models\PurchaseRequest;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AiInsightService
{
    protected int $lookbackDays = 90;

    /**
     * @return array<int, array{
     *     inventory_id: int,
     *     item: string,
     *     item_code: string,
     *     category: string|null,
     *     supplier: string|null,
     *     unit: string,
     *     on_hand: int,
     *     reserved: int,
     *     available: int,
     *     minimum_stock: int,
     *     daily_rate: float,
     *     days_until_depletion: int|null,
     *     recommended_reorder: int,
     *     urgency: string,
     *     message: string
     * }>
     */
    public function inventoryForecasts(int $limit = 10, ?int $maxDays = 14): array
    {
        return array_slice($this->buildForecastRows($maxDays), 0, $limit);
    }

    /**
     * Full restock recommendation list for Supply Personnel.
     *
     * @return array<int, array<string, mixed>>
     */
    public function restockRecommendations(int $limit = 50): array
    {
        return array_slice($this->buildForecastRows(30), 0, $limit);
    }

    /**
     * Items likely to run out within N days (for alerts).
     *
     * @return array<int, array<string, mixed>>
     */
    public function urgentStockAlerts(int $withinDays = 7): array
    {
        return array_values(array_filter(
            $this->buildForecastRows($withinDays),
            fn (array $row) => in_array($row['urgency'], ['critical', 'high'], true)
                || ($row['days_until_depletion'] !== null && $row['days_until_depletion'] <= $withinDays)
                || $row['available'] <= 0
        ));
    }

    public function monthlySummary(): string
    {
        $start = Carbon::now()->startOfMonth();

        $topItem = RequestItem::query()
            ->whereHas('supplyRequest', fn ($q) => $q->where('created_at', '>=', $start))
            ->selectRaw('inventory_id, SUM(quantity_requested) as total')
            ->groupBy('inventory_id')
            ->orderByDesc('total')
            ->with('inventory')
            ->first();

        $requestCount = SupplyRequest::where('created_at', '>=', $start)->count();
        $purchaseCount = PurchaseRequest::where('created_at', '>=', $start)->count();
        $lowStock = Inventory::query()->get()->filter(fn (Inventory $i) => $i->isLowStock())->count();
        $outOfStock = Inventory::query()->get()->filter(fn (Inventory $i) => $i->isOutOfStock())->count();
        $topName = $topItem?->inventory?->item_name ?? 'N/A';

        return "This month: {$requestCount} faculty request(s), {$purchaseCount} student purchase(s), "
            ."{$lowStock} low-stock and {$outOfStock} out-of-stock item(s). Most requested: {$topName}.";
    }

    public function chatResponse(User $user, string $question): string
    {
        $q = strtolower(trim($question));
        $role = $user->getRoleNames()->first() ?? 'User';

        if ($this->matches($q, ['help', 'what can you', 'how do i'])) {
            return $this->helpForRole($user);
        }

        if ($this->matches($q, ['how do i request', 'how to request', 'submit request'])) {
            return 'Faculty: go to New Request, pick items and purpose, then submit. Flow: Accounting review → Admin approval → Supply release.';
        }

        if ($this->matches($q, ['how do i buy', 'how to buy', 'payment', 'checkout'])) {
            return 'Students: open Shop → add to cart → Checkout → pay over the counter → upload receipt → Accounting verifies → Supply releases items.';
        }

        if ($this->matches($q, ['low stock', 'low-stock'])) {
            return $this->answerLowStock();
        }

        if ($this->matches($q, ['out of stock', 'out-of-stock', 'no stock'])) {
            return $this->answerOutOfStock();
        }

        if ($this->matches($q, ['forecast', 'reorder', 'restock', 'run out'])) {
            return $this->answerForecasts();
        }

        if ($this->matches($q, ['monthly', 'summary', 'this month'])) {
            return $this->monthlySummary();
        }

        if ($this->matches($q, ['pending'])) {
            return $this->answerPending($user);
        }

        if ($this->matches($q, ['my request', 'request status', 'status of my request', 'status request'])) {
            return $this->answerMyRequests($user);
        }

        if ($this->matches($q, ['my purchase', 'purchase status', 'what did i buy', 'my order'])) {
            return $this->answerMyPurchases($user);
        }

        if ($this->matches($q, ['release', 'ready for release'])) {
            return $this->answerReleases($user);
        }

        if ($this->matches($q, ['payment', 'verify', 'to verify'])) {
            return $this->answerPayments($user);
        }

        if ($this->matches($q, ['approve', 'admin review', 'for approval'])) {
            return $this->answerApprovals($user);
        }

        // Category-aware stock query: "computer supplies low" / "items in laboratory"
        if (preg_match('/\b(office|classroom|laboratory|computer|cleaning|pantry|maintenance|furniture)\b/', $q, $m)) {
            return $this->answerCategoryStock($m[1]);
        }

        return "I'm your {$role} assistant. ".$this->helpForRole($user);
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function lowStockItems(): Collection
    {
        return Inventory::with(['category', 'supplier'])->get()->filter(fn (Inventory $i) => $i->isLowStock());
    }

    /**
     * Frequent questions shown in AI chat / floating widget.
     *
     * @return array<int, string>
     */
    public function chatSuggestions(User $user): array
    {
        return match (true) {
            $user->hasRole('Faculty') => [
                'Status of my request',
                'My pending requests',
                'What items are low in stock?',
                'How do I request supplies?',
            ],
            $user->hasRole('Student') => [
                'My purchases',
                'Purchase status',
                'Out of stock',
                'How do I buy?',
            ],
            $user->hasRole('Accounting') => [
                'Pending payments',
                'Pending requests',
                'Monthly summary',
                'Low stock',
            ],
            $user->hasRole('Supply Personnel') => [
                'Reorder recommendations',
                'Ready for release',
                'Low stock',
                'Computer supplies',
            ],
            $user->hasRole('Administrator') => [
                'For approval',
                'Monthly summary',
                'Reorder',
                'Pending requests',
            ],
            default => ['Low stock', 'Monthly summary', 'Help'],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildForecastRows(?int $maxDays = 30): array
    {
        $since = Carbon::now()->subDays($this->lookbackDays);

        $usageByInventory = Transaction::query()
            ->where('created_at', '>=', $since)
            ->whereIn('type', ['release', 'stock_out'])
            ->selectRaw('inventory_id, SUM(quantity) as total_used')
            ->groupBy('inventory_id')
            ->pluck('total_used', 'inventory_id');

        $forecasts = [];

        Inventory::with(['category', 'supplier'])->orderBy('item_name')->get()->each(
            function (Inventory $item) use ($usageByInventory, $maxDays, &$forecasts) {
                $used = (int) ($usageByInventory[$item->id] ?? 0);
                $dailyRate = $used > 0 ? round($used / $this->lookbackDays, 3) : 0.0;
                $available = $item->availableQuantity();
                $onHand = (int) $item->quantity;

                $daysLeft = $dailyRate > 0 ? (int) floor($available / $dailyRate) : null;

                $include = $item->isOutOfStock()
                    || $item->isLowStock()
                    || ($daysLeft !== null && ($maxDays === null || $daysLeft <= $maxDays));

                if (! $include) {
                    return;
                }

                // Cover ~30 days of usage, at least 2x minimum, never below minimum gap.
                $coverUsage = (int) ceil(max($dailyRate, 0.1) * 30);
                $minGap = max(0, ($item->minimum_stock * 2) - $available);
                $recommended = max($item->minimum_stock, $coverUsage, $minGap, 5);

                $urgency = $this->urgency($available, $item->minimum_stock, $daysLeft);

                $message = match (true) {
                    $available <= 0 => "{$item->item_name} is out of stock.",
                    $daysLeft !== null => "{$item->item_name} may run out within {$daysLeft} day(s) (avg {$dailyRate}/day over {$this->lookbackDays} days).",
                    default => "{$item->item_name} is below minimum stock ({$available} available / min {$item->minimum_stock}).",
                };

                $forecasts[] = [
                    'inventory_id' => $item->id,
                    'item' => $item->item_name,
                    'item_code' => $item->item_code,
                    'category' => $item->category?->name,
                    'supplier' => $item->supplier?->name,
                    'unit' => $item->unit,
                    'on_hand' => $onHand,
                    'reserved' => (int) $item->reserved_quantity,
                    'available' => $available,
                    'minimum_stock' => (int) $item->minimum_stock,
                    'daily_rate' => $dailyRate,
                    'days_until_depletion' => $daysLeft,
                    'recommended_reorder' => $recommended,
                    'urgency' => $urgency,
                    'message' => $message,
                ];
            }
        );

        usort($forecasts, function (array $a, array $b) {
            $order = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];

            return ($order[$a['urgency']] ?? 9) <=> ($order[$b['urgency']] ?? 9)
                ?: ($a['days_until_depletion'] ?? 999) <=> ($b['days_until_depletion'] ?? 999);
        });

        return $forecasts;
    }

    protected function urgency(int $available, int $minimum, ?int $daysLeft): string
    {
        if ($available <= 0) {
            return 'critical';
        }
        if ($daysLeft !== null && $daysLeft <= 3) {
            return 'critical';
        }
        if ($daysLeft !== null && $daysLeft <= 7) {
            return 'high';
        }
        if ($available <= $minimum || ($daysLeft !== null && $daysLeft <= 14)) {
            return 'medium';
        }

        return 'low';
    }

    protected function matches(string $q, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($q, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function helpForRole(User $user): string
    {
        if ($user->hasRole('Faculty')) {
            return 'Try: "status of my request", "my pending requests", "low stock", "how do I request supplies?"';
        }
        if ($user->hasRole('Student')) {
            return 'Try: "my purchases", "purchase status", "how do I buy?", "out of stock".';
        }
        if ($user->hasRole('Accounting')) {
            return 'Try: "pending payments", "pending requests", "monthly summary", "low stock".';
        }
        if ($user->hasRole('Supply Personnel')) {
            return 'Try: "reorder", "ready for release", "low stock", "forecast", "computer supplies".';
        }
        if ($user->hasRole('Administrator')) {
            return 'Try: "for approval", "monthly summary", "reorder", "low stock", "pending requests".';
        }

        return 'Try: "low stock", "monthly summary", or "help".';
    }

    protected function answerLowStock(): string
    {
        $items = $this->lowStockItems();

        if ($items->isEmpty()) {
            return 'No items are currently below minimum stock levels.';
        }

        return 'Low stock items: '.$items->map(
            fn (Inventory $i) => "{$i->item_name} ({$i->availableQuantity()} {$i->unit}, min {$i->minimum_stock})"
        )->take(15)->join('; ').'.';
    }

    protected function answerOutOfStock(): string
    {
        $items = Inventory::all()->filter(fn (Inventory $i) => $i->isOutOfStock());

        return $items->isEmpty()
            ? 'All tracked items have available stock.'
            : 'Out of stock: '.$items->pluck('item_name')->join(', ').'.';
    }

    protected function answerForecasts(): string
    {
        $forecasts = $this->inventoryForecasts(8, 14);

        if (empty($forecasts)) {
            return 'No urgent restocking forecasts at this time.';
        }

        return collect($forecasts)->map(function (array $f) {
            return "{$f['message']} Suggested reorder: {$f['recommended_reorder']} {$f['unit']}"
                .($f['supplier'] ? " (supplier: {$f['supplier']})" : '').'.';
        })->join(' ');
    }

    protected function answerMyRequests(User $user): string
    {
        if (! $user->hasAnyRole(['Faculty', 'Administrator'])) {
            if ($user->hasRole('Student')) {
                return $this->answerMyPurchases($user);
            }

            return 'Supply request status is mainly for Faculty accounts.';
        }

        $requests = SupplyRequest::where('user_id', $user->id)->latest()->limit(5)->get();

        if ($requests->isEmpty()) {
            return 'You have not submitted any supply requests yet.';
        }

        return 'Your recent requests: '.$requests->map(
            fn (SupplyRequest $r) => "{$r->request_number} (".str_replace('_', ' ', $r->status).')'
        )->join('; ').'.';
    }

    protected function answerMyPurchases(User $user): string
    {
        if (! $user->hasAnyRole(['Student', 'Administrator'])) {
            return 'Purchase history is mainly for Student accounts.';
        }

        $purchases = PurchaseRequest::where('user_id', $user->id)->latest()->limit(5)->get();

        if ($purchases->isEmpty()) {
            return 'You have no purchases yet. Open Shop to buy available items.';
        }

        return 'Your recent purchases: '.$purchases->map(
            fn (PurchaseRequest $p) => "{$p->purchase_number} — ₱".number_format($p->total_amount, 2)
                .' ('.str_replace('_', ' ', $p->status).')'
        )->join('; ').'.';
    }

    protected function answerPending(User $user): string
    {
        if ($user->hasRole('Faculty')) {
            $count = SupplyRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'accounting_review', 'admin_review', 'approved'])
                ->count();

            return "You have {$count} open/pending supply request(s). Ask \"status of my request\" for details.";
        }

        if ($user->hasRole('Student')) {
            $count = PurchaseRequest::where('user_id', $user->id)
                ->whereNotIn('status', ['released', 'cancelled'])
                ->count();

            return "You have {$count} open purchase(s). Ask \"my purchases\" for details.";
        }

        if ($user->hasAnyRole(['Accounting', 'Administrator'])) {
            $req = SupplyRequest::whereIn('status', ['pending', 'accounting_review'])->count();
            $pay = PurchaseRequest::where('status', 'payment_submitted')->count();

            return "Pending for Accounting: {$req} faculty request(s) to review, {$pay} student payment(s) to verify.";
        }

        if ($user->hasRole('Supply Personnel')) {
            return $this->answerReleases($user);
        }

        return $this->monthlySummary();
    }

    protected function answerReleases(User $user): string
    {
        if (! $user->hasAnyRole(['Supply Personnel', 'Administrator'])) {
            return 'Release queues are for Supply Personnel.';
        }

        $faculty = SupplyRequest::where('status', 'approved')->count();
        $students = PurchaseRequest::where('status', 'payment_verified')->count();

        return "Ready for release: {$faculty} faculty request(s) and {$students} student purchase(s). "
            .'Open Release Items or Student Purchases in the sidebar.';
    }

    protected function answerPayments(User $user): string
    {
        if (! $user->hasAnyRole(['Accounting', 'Administrator'])) {
            if ($user->hasRole('Student')) {
                return $this->answerMyPurchases($user);
            }

            return 'Payment verification is handled by Accounting.';
        }

        $count = PurchaseRequest::where('status', 'payment_submitted')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();

        return "There are {$count} purchase(s) awaiting verification ({$pendingPayments} pending payment record(s)). Open Verify Payments.";
    }

    protected function answerApprovals(User $user): string
    {
        if (! $user->hasRole('Administrator')) {
            return 'Admin approval queue is for Administrators.';
        }

        $count = SupplyRequest::where('status', 'admin_review')->count();

        return "There are {$count} faculty request(s) waiting for admin approval. Open Approve Requests.";
    }

    protected function answerCategoryStock(string $keyword): string
    {
        $map = [
            'office' => 'Office Supplies',
            'classroom' => 'Classroom Supplies',
            'laboratory' => 'Laboratory Supplies',
            'computer' => 'Computer Supplies',
            'cleaning' => 'Cleaning Supplies',
            'pantry' => 'Pantry Supplies',
            'maintenance' => 'Maintenance Supplies',
            'furniture' => 'Furniture',
        ];

        $categoryName = $map[$keyword] ?? null;
        if (! $categoryName) {
            return 'I could not match that category.';
        }

        $items = Inventory::with('category')
            ->whereHas('category', fn ($q) => $q->where('name', $categoryName))
            ->orderBy('item_name')
            ->get();

        if ($items->isEmpty()) {
            return "No inventory items found under {$categoryName}.";
        }

        $low = $items->filter(fn (Inventory $i) => $i->isLowStock() || $i->isOutOfStock());

        $list = $items->take(10)->map(
            fn (Inventory $i) => "{$i->item_name} (avail {$i->availableQuantity()})"
        )->join('; ');

        $extra = $low->isEmpty()
            ? 'None are low/out of stock.'
            : 'Attention: '.$low->pluck('item_name')->join(', ').'.';

        return "{$categoryName}: {$list}. {$extra}";
    }
}
