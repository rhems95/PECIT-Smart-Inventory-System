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
        $q = $this->normalizeQuestion($question);
        $role = $user->getRoleNames()->first() ?? 'User';

        // Specific how-to / intent handlers FIRST (never let generic "how do i" steal these).
        if ($this->matches($q, [
            'how do i buy', 'how to buy', 'buy uniform', 'buying uniform',
            'uniform shop', 'checkout', 'payment slip', 'upload receipt',
        ])) {
            return $this->answerHowToBuy($user);
        }

        if ($this->matches($q, [
            'what uniform', 'which uniform', 'uniforms can i', 'can i buy',
            'available uniform', 'my uniform', 'department uniform',
        ])) {
            return $this->answerUniformsForStudent($user);
        }

        if ($this->matches($q, [
            'how do i request', 'how to request', 'submit request', 'request supplies', 'new request',
        ])) {
            return $this->answerHowToRequest($user);
        }

        if ($this->matches($q, [
            'my purchase', 'purchase status', 'what did i buy', 'my order', 'my orders',
        ])) {
            return $this->answerMyPurchases($user);
        }

        if ($this->matches($q, [
            'my request', 'request status', 'status of my request', 'status request', 'my pending request',
        ])) {
            return $this->answerMyRequests($user);
        }

        if ($this->matches($q, ['pending payment', 'verify payment', 'payments to verify', 'to verify'])) {
            return $this->answerPayments($user);
        }

        if ($this->matches($q, ['pending request', 'pending'])) {
            return $this->answerPending($user);
        }

        if ($this->matches($q, ['approve', 'admin review', 'for approval', 'waiting for approval'])) {
            return $this->answerApprovals($user);
        }

        if ($this->matches($q, ['release', 'ready for release'])) {
            return $this->answerReleases($user);
        }

        if ($this->matches($q, ['payment']) && $user->hasAnyRole(['Accounting', 'Administrator', 'Student'])) {
            return $this->answerPayments($user);
        }

        if ($this->matches($q, [
            'low stock', 'low-stock', 'low in stock', 'below minimum', 'running low', 'items are low',
        ])) {
            return $this->answerLowStock($user);
        }

        if ($this->matches($q, ['out of stock', 'out-of-stock', 'no stock', 'zero stock'])) {
            return $this->answerOutOfStock($user);
        }

        if ($this->matches($q, ['forecast', 'reorder', 'restock', 'run out', 'recommendation'])) {
            return $this->answerForecasts();
        }

        if ($this->matches($q, ['monthly', 'summary', 'this month'])) {
            return $this->monthlySummary();
        }

        // Category-aware stock query: "computer supplies" / "laboratory"
        if (preg_match('/\b(office|classroom|laboratory|computer|cleaning|pantry|maintenance|furniture|uniform)\b/', $q, $m)) {
            if ($m[1] === 'uniform') {
                return $this->answerUniformsForStudent($user);
            }

            return $this->answerCategoryStock($m[1], $user);
        }

        if ($this->matches($q, ['help', 'what can you', 'commands', 'frequent question'])) {
            return $this->helpForRole($user);
        }

        return "I'm your {$role} assistant. ".$this->helpForRole($user);
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function lowStockItems(): Collection
    {
        return Inventory::with(['category'])->get()->filter(fn (Inventory $i) => $i->isLowStock());
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
                'How do I buy uniforms?',
                'What uniforms can I buy?',
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
            $user->hasRole('Admission') => [
                'For approval',
                'Pending requests',
                'Monthly summary',
                'Low stock',
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

        Inventory::with(['category'])->orderBy('item_name')->get()->each(
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

    protected function normalizeQuestion(string $question): string
    {
        $q = strtolower(trim($question));
        $q = preg_replace('/[^\p{L}\p{N}\s\-?]/u', ' ', $q) ?? $q;
        $q = preg_replace('/\s+/', ' ', $q) ?? $q;

        return trim($q);
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
            return 'Try: "Status of my request", "My pending requests", "What items are low in stock?", "How do I request supplies?"';
        }
        if ($user->hasRole('Student')) {
            return 'Try: "My purchases", "Purchase status", "How do I buy uniforms?", "What uniforms can I buy?"';
        }
        if ($user->hasRole('Accounting')) {
            return 'Try: "Pending payments", "Pending requests", "Monthly summary", "Low stock".';
        }
        if ($user->hasRole('Supply Personnel')) {
            return 'Try: "Reorder recommendations", "Ready for release", "Low stock", "Computer supplies".';
        }
        if ($user->hasRole('Administrator')) {
            return 'Try: "For approval", "Monthly summary", "Reorder", "Pending requests".';
        }
        if ($user->hasRole('Admission')) {
            return 'Try: "For approval", "Pending requests", "Monthly summary", "Low stock".';
        }

        return 'Try: "low stock", "monthly summary", or "help".';
    }

    protected function answerHowToBuy(User $user): string
    {
        if (! $user->hasRole('Student') && ! $user->hasRole('Administrator')) {
            return 'Uniform purchases are for Student accounts. Students use Uniform Shop with Student ID + last name login.';
        }

        $dept = $user->department?->name ?? 'your department';

        return "To buy uniforms: open Uniform Shop → choose a size for each uniform → add your department items (and shared P.E., NSTP, or ID lanyard) → View Cart → Checkout "
            .'→ pay over the counter → upload your receipt → Accounting verifies → Supply releases. '
            ."You only see exclusive uniforms for {$dept}, plus shared items. Ask \"What uniforms can I buy?\" to list them.";
    }

    protected function answerHowToRequest(User $user): string
    {
        if ($user->hasRole('Student')) {
            return 'Students do not submit faculty supply requests. Use Uniform Shop to buy uniforms. Ask "How do I buy uniforms?" for steps.';
        }

        return 'Faculty: go to New Request, pick items and purpose, then submit. Flow: Accounting review → Admin approval → Supply release. '
            .'Track progress under My Requests.';
    }

    protected function answerUniformsForStudent(User $user): string
    {
        if (! $user->hasRole('Student') && ! $user->hasRole('Administrator')) {
            return 'Uniform Shop listings are for students. Supply/Admin manage uniforms under Inventory (student shop + exclusive department).';
        }

        if (! $user->department_id && $user->hasRole('Student')) {
            return 'Your account has no department assigned, so you can only buy shared items (P.E., NSTP, ID lanyard). Contact Supply to set your department for exclusive uniforms.';
        }

        $items = Inventory::with('department')
            ->forStudentShop($user)
            ->orderByRaw('department_id is null')
            ->orderBy('item_name')
            ->get();

        if ($items->isEmpty()) {
            return 'No uniforms are available for your department right now. Please contact Supply Personnel.';
        }

        $dept = $user->department?->name ?? 'your department';
        $list = $items->map(function (Inventory $i) {
            $tag = $i->isDepartmentExclusive()
                ? 'exclusive'
                : 'shared';
            $stock = $i->availableQuantity() > 0
                ? "{$i->availableQuantity()} {$i->unit} avail"
                : 'out of stock';

            return "{$i->item_name} ({$tag}, ₱".number_format((float) $i->unit_price, 2).", {$stock})";
        })->join('; ');

        return "Uniforms you can buy for {$dept}: {$list}. Open Uniform Shop to add items to your cart.";
    }

    protected function answerLowStock(?User $user = null): string
    {
        if ($user?->hasRole('Student')) {
            $items = Inventory::forStudentShop($user)
                ->get()
                ->filter(fn (Inventory $i) => $i->isLowStock() || $i->isOutOfStock());

            if ($items->isEmpty()) {
                return 'Your available uniforms currently have healthy stock. Ask "What uniforms can I buy?" to see the list.';
            }

            return 'Uniform stock attention: '.$items->map(
                fn (Inventory $i) => "{$i->item_name} ({$i->availableQuantity()} {$i->unit})"
            )->join('; ').'.';
        }

        $items = $this->lowStockItems();

        if ($items->isEmpty()) {
            return 'No items are currently below minimum stock levels.';
        }

        return 'Low stock items: '.$items->map(
            fn (Inventory $i) => "{$i->item_name} ({$i->availableQuantity()} {$i->unit}, min {$i->minimum_stock})"
        )->take(15)->join('; ').'.';
    }

    protected function answerOutOfStock(?User $user = null): string
    {
        if ($user?->hasRole('Student')) {
            $items = Inventory::forStudentShop($user)
                ->get()
                ->filter(fn (Inventory $i) => $i->isOutOfStock());

            return $items->isEmpty()
                ? 'None of your available uniforms are out of stock right now.'
                : 'Out of stock for you: '.$items->pluck('item_name')->join(', ').'.';
        }

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
            return "{$f['message']} Suggested reorder: {$f['recommended_reorder']} {$f['unit']}.";
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
            return 'You have not submitted any supply requests yet. Open New Request to submit one.';
        }

        return 'Your recent requests: '.$requests->map(
            fn (SupplyRequest $r) => "{$r->request_number} (".str_replace('_', ' ', $r->status).')'
        )->join('; ').'. Open My Requests for full details.';
    }

    protected function answerMyPurchases(User $user): string
    {
        if (! $user->hasAnyRole(['Student', 'Administrator'])) {
            return 'Purchase history is mainly for Student accounts.';
        }

        $purchases = PurchaseRequest::where('user_id', $user->id)->latest()->limit(5)->get();

        if ($purchases->isEmpty()) {
            return 'You have no purchases yet. Open Uniform Shop to buy department uniforms or shared P.E. / NSTP / lanyard items.';
        }

        return 'Your recent purchases: '.$purchases->map(
            fn (PurchaseRequest $p) => "{$p->purchase_number} — ₱".number_format((float) $p->total_amount, 2)
                .' ('.str_replace('_', ' ', $p->status).')'
        )->join('; ').'. Open My Purchases to view slips and upload receipts.';
    }

    protected function answerPending(User $user): string
    {
        if ($user->hasRole('Faculty')) {
            $count = SupplyRequest::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'accounting_review', 'admin_review', 'approved'])
                ->count();

            return "You have {$count} open/pending supply request(s). Ask \"Status of my request\" for details.";
        }

        if ($user->hasRole('Student')) {
            $count = PurchaseRequest::where('user_id', $user->id)
                ->whereNotIn('status', ['released', 'cancelled'])
                ->count();

            return "You have {$count} open purchase(s). Ask \"My purchases\" for details.";
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
        if (! $user->hasAnyRole(['Administrator', 'Admission'])) {
            return 'Admin approval queue is for Admission and Administrators.';
        }

        $count = SupplyRequest::where('status', 'admin_review')->count();

        return "There are {$count} faculty request(s) waiting for admin approval. Open Approve Requests.";
    }

    protected function answerCategoryStock(string $keyword, ?User $user = null): string
    {
        if ($user?->hasRole('Student')) {
            return $this->answerUniformsForStudent($user);
        }

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
