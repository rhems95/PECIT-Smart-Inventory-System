<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\RequestItem;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AiInsightService
{
    protected int $lookbackDays = 90;

    /** @var 'en'|'fil'|'ceb' */
    protected string $chatLang = 'en';

    public function __construct(
        protected OllamaChatService $ollama,
        protected FacultyBudgetService $facultyBudget,
    ) {}

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

    /**
     * Ranked demand this calendar month (faculty requests + student purchases).
     * Cancelled and rejected orders do not count.
     *
     * @param  'all'|'faculty'|'student'  $channel
     * @return array<int, array{inventory_id: int, item: string, item_code: string, faculty_qty: int, student_qty: int, total: int}>
     */
    public function mostRequestedItemsThisMonth(int $limit = 8, ?Carbon $month = null, string $channel = 'all'): array
    {
        $month = ($month ?? Carbon::now())->copy()->startOfMonth();

        return $this->mostRequestedItemsInRange(
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth(),
            $limit,
            $channel
        );
    }

    /**
     * @param  'all'|'faculty'|'student'  $channel
     * @return array<int, array{inventory_id: int, item: string, item_code: string, faculty_qty: int, student_qty: int, total: int}>
     */
    public function mostRequestedItemsInRange(Carbon $start, Carbon $end, int $limit = 8, string $channel = 'all'): array
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->endOfDay();
        $skip = ['cancelled', 'rejected'];

        $faculty = RequestItem::query()
            ->whereHas('supplyRequest', fn ($q) => $q
                ->whereBetween('created_at', [$start, $end])
                ->whereNotIn('status', $skip))
            ->selectRaw('inventory_id, SUM(quantity_requested) as qty')
            ->groupBy('inventory_id')
            ->pluck('qty', 'inventory_id');

        $student = PurchaseRequestItem::query()
            ->whereHas('purchaseRequest', fn ($q) => $q
                ->whereBetween('created_at', [$start, $end])
                ->whereNotIn('status', $skip))
            ->selectRaw('inventory_id, SUM(quantity) as qty')
            ->groupBy('inventory_id')
            ->pluck('qty', 'inventory_id');

        $ids = $faculty->keys()->merge($student->keys())->unique()->filter();
        $items = Inventory::query()->whereIn('id', $ids)->get()->keyBy('id');

        $rows = [];
        foreach ($ids as $id) {
            $inventory = $items->get($id);
            if (! $inventory) {
                continue;
            }

            $facultyQty = (int) ($faculty[$id] ?? 0);
            $studentQty = (int) ($student[$id] ?? 0);

            $rows[] = [
                'inventory_id' => (int) $id,
                'item' => $inventory->item_name,
                'item_code' => $inventory->item_code,
                'faculty_qty' => $facultyQty,
                'student_qty' => $studentQty,
                'total' => $facultyQty + $studentQty,
            ];
        }

        if ($channel === 'faculty') {
            $rows = array_values(array_filter($rows, fn (array $row) => $row['faculty_qty'] > 0));
            usort($rows, fn (array $a, array $b) => $b['faculty_qty'] <=> $a['faculty_qty']);
        } elseif ($channel === 'student') {
            $rows = array_values(array_filter($rows, fn (array $row) => $row['student_qty'] > 0));
            usort($rows, fn (array $a, array $b) => $b['student_qty'] <=> $a['student_qty']);
        } else {
            usort($rows, fn (array $a, array $b) => $b['total'] <=> $a['total']);
        }

        return array_slice($rows, 0, $limit);
    }

    /**
     * Item-by-month demand for a semester or year: names on the left, months along the range.
     *
     * @return array{
     *     label: string,
     *     months: list<array{key: string, label: string}>,
     *     items: list<array{inventory_id: int, item: string, months: list<int>, total: int, predicted: int, prior_total: int}>,
     *     chart: array{labels: list<string>, datasets: list<array{label: string, data: list<int>, backgroundColor: string}>},
     *     prediction: array{
     *         summary: string,
     *         direction: 'up'|'down'|'steady'|'none',
     *         current_total: int,
     *         prior_total: int,
     *         prior_label: string,
     *         projected_total: int,
     *         remaining_months: int,
     *         elapsed_months: int,
     *         total_months: int,
     *         complete: bool
     *     }
     * }
     */
    public function itemDemandTrend(Carbon $from, Carbon $to, string $periodLabel, int $itemLimit = 8): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        $lastMonth = $to->copy()->startOfMonth();
        while ($cursor->lte($lastMonth)) {
            $months[] = [
                'key' => $cursor->format('Y-m'),
                'label' => $cursor->format('M Y'),
            ];
            $cursor->addMonth();
        }

        $currentMap = $this->demandByItemAndMonth($from, $to);
        $priorFrom = $from->copy()->subYear();
        $priorTo = $to->copy()->subYear();
        $priorMap = $this->demandByItemAndMonth($priorFrom, $priorTo);

        $totals = [];
        foreach ($currentMap as $id => $byMonth) {
            $totals[$id] = (int) array_sum($byMonth);
        }
        arsort($totals);
        $topIds = array_slice(array_keys($totals), 0, max(1, $itemLimit), true);
        $names = $topIds === []
            ? collect()
            : Inventory::query()->whereIn('id', $topIds)->get()->keyBy('id');

        $now = Carbon::now();
        $monthKeys = array_column($months, 'key');
        $elapsedKeys = array_values(array_filter($monthKeys, function (string $ym) use ($now) {
            return Carbon::createFromFormat('Y-m', $ym)->startOfMonth()->lte($now->copy()->endOfMonth());
        }));
        $remainingKeys = array_values(array_diff($monthKeys, $elapsedKeys));
        $complete = $remainingKeys === [];
        $elapsedCount = count($elapsedKeys);
        $totalMonths = max(1, count($monthKeys));

        $items = [];
        foreach ($topIds as $id) {
            $inventory = $names->get($id);
            if (! $inventory) {
                continue;
            }
            $monthValues = [];
            foreach ($months as $month) {
                $monthValues[] = (int) ($currentMap[$id][$month['key']] ?? 0);
            }
            $total = (int) array_sum($monthValues);
            $soFar = 0;
            foreach ($elapsedKeys as $key) {
                $soFar += (int) ($currentMap[$id][$key] ?? 0);
            }
            $priorTotal = (int) array_sum($priorMap[$id] ?? []);
            $priorRemaining = 0;
            foreach ($remainingKeys as $key) {
                $priorKey = Carbon::createFromFormat('Y-m', $key)->subYear()->format('Y-m');
                $priorRemaining += (int) ($priorMap[$id][$priorKey] ?? 0);
            }
            if ($complete) {
                $predicted = $total;
            } elseif ($priorTotal >= 50) {
                $predicted = $soFar + $priorRemaining;
            } elseif ($elapsedCount > 0) {
                $predicted = (int) round(($soFar / $elapsedCount) * $totalMonths);
            } else {
                $predicted = $total;
            }

            $items[] = [
                'inventory_id' => (int) $id,
                'item' => $inventory->item_name,
                'months' => $monthValues,
                'total' => $total,
                'predicted' => $predicted,
                'prior_total' => $priorTotal,
            ];
        }

        $colors = $this->trendMonthColors(count($months));
        $datasets = [];
        foreach ($months as $index => $month) {
            $datasets[] = [
                'label' => $month['label'],
                'data' => array_map(fn (array $row) => $row['months'][$index] ?? 0, $items),
                'backgroundColor' => $colors[$index],
            ];
        }

        $currentTotal = (int) array_sum(array_column($items, 'total'));
        $projectedTotal = (int) array_sum(array_column($items, 'predicted'));
        $priorTotal = 0;
        foreach ($topIds as $id) {
            $priorTotal += (int) array_sum($priorMap[$id] ?? []);
        }
        $compare = $complete ? $currentTotal : $projectedTotal;
        $prediction = $this->trendPredictionCopy(
            $periodLabel,
            $currentTotal,
            $priorTotal,
            $compare,
            $elapsedCount,
            count($remainingKeys),
            $totalMonths,
            $complete,
            $priorFrom->year.'–'.$priorTo->year,
        );

        return [
            'label' => $periodLabel,
            'months' => $months,
            'items' => $items,
            'chart' => [
                'labels' => array_column($items, 'item'),
                'datasets' => $datasets,
            ],
            'prediction' => $prediction + [
                'current_total' => $currentTotal,
                'prior_total' => $priorTotal,
                'prior_label' => 'same period last year',
                'projected_total' => $projectedTotal,
                'remaining_months' => count($remainingKeys),
                'elapsed_months' => $elapsedCount,
                'total_months' => $totalMonths,
                'complete' => $complete,
            ],
        ];
    }

    /**
     * Items likely to trend in the remaining months of a semester, with restock vs available.
     *
     * @return array{
     *     label: string,
     *     remaining_labels: list<string>,
     *     complete: bool,
     *     summary: string,
     *     items: list<array<string, mixed>>
     * }
     */
    public function semesterTrendForecast(Carbon $from, Carbon $to, string $periodLabel, int $itemLimit = 8): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        $lastMonth = $to->copy()->startOfMonth();
        while ($cursor->lte($lastMonth)) {
            $months[] = [
                'key' => $cursor->format('Y-m'),
                'label' => $cursor->format('M Y'),
            ];
            $cursor->addMonth();
        }

        $now = Carbon::now();
        $monthKeys = array_column($months, 'key');
        $elapsedKeys = array_values(array_filter($monthKeys, function (string $ym) use ($now) {
            return Carbon::createFromFormat('Y-m', $ym)->startOfMonth()->lte($now->copy()->endOfMonth());
        }));
        $remainingKeys = array_values(array_diff($monthKeys, $elapsedKeys));
        $remainingLabels = [];
        foreach ($months as $month) {
            if (in_array($month['key'], $remainingKeys, true)) {
                $remainingLabels[] = $month['label'];
            }
        }

        if ($remainingKeys === []) {
            return [
                'label' => $periodLabel,
                'remaining_labels' => [],
                'complete' => true,
                'summary' => "{$periodLabel} has no upcoming months left to forecast.",
                'items' => [],
            ];
        }

        $currentMap = $this->demandByItemAndMonth($from, $to);
        $priorMap = $this->demandByItemAndMonth($from->copy()->subYear(), $to->copy()->subYear());
        $ids = array_values(array_unique(array_merge(array_keys($currentMap), array_keys($priorMap))));
        $inventories = $ids === []
            ? collect()
            : Inventory::query()->whereIn('id', $ids)->get()->keyBy('id');

        $elapsedCount = max(1, count($elapsedKeys));
        $remainingCount = count($remainingKeys);
        $rows = [];

        foreach ($ids as $id) {
            $inventory = $inventories->get($id);
            if (! $inventory) {
                continue;
            }

            $soFar = 0;
            foreach ($elapsedKeys as $key) {
                $soFar += (int) ($currentMap[$id][$key] ?? 0);
            }

            $priorRemaining = 0;
            $priorByMonth = [];
            foreach ($remainingKeys as $key) {
                $priorKey = Carbon::createFromFormat('Y-m', $key)->subYear()->format('Y-m');
                $qty = (int) ($priorMap[$id][$priorKey] ?? 0);
                $priorRemaining += $qty;
                $priorByMonth[] = Carbon::createFromFormat('Y-m', $key)->format('M').' last year '.$qty;
            }

            if ($priorRemaining > 0) {
                $predictedRemaining = $priorRemaining;
                $basis = 'last year same months';
            } else {
                $predictedRemaining = (int) round(($soFar / $elapsedCount) * $remainingCount);
                $basis = 'current semester pace';
            }

            if ($predictedRemaining <= 0 && $soFar <= 0) {
                continue;
            }

            $available = $inventory->availableQuantity();
            $shortfall = max(0, $predictedRemaining - $available);
            $recommended = $shortfall > 0
                ? max($shortfall, max(0, ((int) $inventory->minimum_stock * 2) - $available), 5)
                : 0;

            $rows[] = [
                'inventory_id' => (int) $id,
                'item' => $inventory->item_name,
                'item_code' => $inventory->item_code,
                'unit' => $inventory->unitLabel() ?: $inventory->unit,
                'available' => $available,
                'so_far' => $soFar,
                'prior_remaining' => $priorRemaining,
                'predicted_remaining' => $predictedRemaining,
                'recommended_reorder' => $recommended,
                'basis' => $basis,
                'prior_months' => implode('; ', $priorByMonth),
            ];
        }

        usort($rows, fn (array $a, array $b) => $b['predicted_remaining'] <=> $a['predicted_remaining']
            ?: $b['recommended_reorder'] <=> $a['recommended_reorder']
            ?: $b['so_far'] <=> $a['so_far']);

        $items = array_slice($rows, 0, max(1, $itemLimit));
        $upcoming = implode(', ', $remainingLabels);
        $top = $items[0]['item'] ?? null;
        $summary = $top
            ? "{$periodLabel} still has {$upcoming}. Live demand plus last year the same months points to {$top} as a likely trend item. Restock when available stock cannot cover the predicted remaining months."
            : "{$periodLabel} still has {$upcoming}, but there is not enough last-year or current demand yet to name a trend item.";

        return [
            'label' => $periodLabel,
            'remaining_labels' => $remainingLabels,
            'complete' => false,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    public function currentSemesterTrendForecast(int $itemLimit = 8): array
    {
        $period = $this->facultyBudget->period();

        return $this->semesterTrendForecast(
            $period['starts_at']->copy(),
            $period['ends_at']->copy(),
            $period['period_label'],
            $itemLimit
        );
    }

    /**
     * Faculty vs student demand totals for the last N calendar months (oldest first).
     *
     * @return array<int, array{month: string, label: string, faculty: int, student: int}>
     */
    public function monthlyDemandTrend(int $months = 12): array
    {
        $months = max(2, min(18, $months));
        $rows = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $items = $this->mostRequestedItemsThisMonth(100, $month);
            $rows[] = [
                'month' => $month->format('Y-m'),
                'label' => $month->format('M Y'),
                'faculty' => (int) array_sum(array_column($items, 'faculty_qty')),
                'student' => (int) array_sum(array_column($items, 'student_qty')),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{inventory_id: int, item: string, item_code: string, faculty_qty: int, student_qty: int, total: int}>
     */
    public function mostRequestedByFacultyThisMonth(int $limit = 8, ?Carbon $month = null): array
    {
        return $this->mostRequestedItemsThisMonth($limit, $month, 'faculty');
    }

    /**
     * @return array<int, array{inventory_id: int, item: string, item_code: string, faculty_qty: int, student_qty: int, total: int}>
     */
    public function mostPurchasedByStudentsThisMonth(int $limit = 8, ?Carbon $month = null): array
    {
        return $this->mostRequestedItemsThisMonth($limit, $month, 'student');
    }

    /**
     * Live monthly demand the AI remembers (faculty requests, student purchases, restock needs).
     * Snapshot is stored for the rest of the calendar month and refreshed on each call.
     *
     * @return array{
     *     month: string,
     *     month_label: string,
     *     faculty: array<int, array<string, mixed>>,
     *     student: array<int, array<string, mixed>>,
     *     restock: array<int, array<string, mixed>>,
     *     remembered_at: string
     * }
     */
    public function monthlyDemandMemory(?Carbon $month = null): array
    {
        $month = ($month ?? Carbon::now())->copy()->startOfMonth();
        $snapshot = [
            'month' => $month->format('Y-m'),
            'month_label' => $month->format('F Y'),
            'faculty' => $this->mostRequestedByFacultyThisMonth(5, $month),
            'student' => $this->mostPurchasedByStudentsThisMonth(5, $month),
            'restock' => $this->demandRestockPredictions(8, $month),
            'remembered_at' => now()->toIso8601String(),
        ];

        Cache::put($this->demandMemoryCacheKey($month), $snapshot, $month->copy()->endOfMonth());

        return $snapshot;
    }

    /**
     * Items from this month's faculty/student demand that should be restocked.
     *
     * @return array<int, array<string, mixed>>
     */
    public function demandRestockPredictions(int $limit = 8, ?Carbon $month = null): array
    {
        $month = ($month ?? Carbon::now())->copy()->startOfMonth();
        $rows = $this->mostRequestedItemsThisMonth(20, $month);
        $ids = collect($rows)->pluck('inventory_id')->all();
        $items = Inventory::query()->whereIn('id', $ids)->get()->keyBy('id');
        $predictions = [];

        foreach ($rows as $row) {
            $item = $items->get($row['inventory_id']);
            if (! $item) {
                continue;
            }

            $available = $item->availableQuantity();
            $demand = (int) $row['total'];
            $shortfall = max(0, $demand - $available);
            $needsRestock = $shortfall > 0 || $item->isLowStock() || $item->isOutOfStock();

            if (! $needsRestock) {
                continue;
            }

            $recommended = max($shortfall, max(0, ((int) $item->minimum_stock * 2) - $available), 5);
            $daysLeft = null;
            $urgency = $this->urgency($available, (int) $item->minimum_stock, $daysLeft);
            $sources = [];
            if ($row['faculty_qty'] > 0) {
                $sources[] = 'faculty '.$row['faculty_qty'];
            }
            if ($row['student_qty'] > 0) {
                $sources[] = 'student '.$row['student_qty'];
            }
            $sourceLabel = $sources === [] ? 'this month' : implode(', ', $sources);

            $message = match (true) {
                $available <= 0 => "{$item->item_name} is this month's demanded item and is out of stock. Restock {$recommended} {$item->unit}.",
                $shortfall > 0 => "{$item->item_name} demand this month is {$demand} ({$sourceLabel}) but only {$available} available. Suggest restock {$recommended} {$item->unit}.",
                default => "{$item->item_name} is in demand this month ({$sourceLabel}) and is below minimum ({$available} available / min {$item->minimum_stock}). Suggest restock {$recommended} {$item->unit}.",
            };

            $predictions[] = [
                'inventory_id' => (int) $item->id,
                'item' => $item->item_name,
                'item_code' => $item->item_code,
                'unit' => $item->unit,
                'available' => $available,
                'minimum_stock' => (int) $item->minimum_stock,
                'faculty_qty' => (int) $row['faculty_qty'],
                'student_qty' => (int) $row['student_qty'],
                'demand' => $demand,
                'shortfall' => $shortfall,
                'recommended_reorder' => $recommended,
                'urgency' => $urgency,
                'message' => $message,
            ];
        }

        usort($predictions, function (array $a, array $b) {
            $order = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];

            return ($order[$a['urgency']] ?? 9) <=> ($order[$b['urgency']] ?? 9)
                ?: $b['shortfall'] <=> $a['shortfall']
                ?: $b['demand'] <=> $a['demand'];
        });

        return array_slice($predictions, 0, $limit);
    }

    protected function demandMemoryCacheKey(Carbon $month): string
    {
        return 'psis:demand-memory:'.$month->format('Y-m');
    }

    /**
     * Organized monthly summary used on Reports and export files.
     *
     * @return array{
     *     month: string,
     *     month_label: string,
     *     is_current: bool,
     *     counts: array{faculty_requests: int, student_purchases: int, low_stock: int, out_of_stock: int},
     *     top_overall: array<string, mixed>|null,
     *     faculty: array<int, array<string, mixed>>,
     *     student: array<int, array<string, mixed>>,
     *     restock: array<int, array<string, mixed>>,
     *     most_requested: array<int, array<string, mixed>>,
     *     low_stock_items: array<int, array{item: string, available: int, minimum: int}>
     * }
     */
    public function monthlySummaryReport(?Carbon $month = null): array
    {
        $month = ($month ?? Carbon::now())->copy()->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $memory = $this->monthlyDemandMemory($month);

        return [
            'month' => $month->format('Y-m'),
            'month_label' => $month->format('F Y'),
            'is_current' => $month->isSameMonth(Carbon::now()),
            'counts' => [
                'faculty_requests' => SupplyRequest::whereBetween('created_at', [$start, $end])->count(),
                'student_purchases' => PurchaseRequest::whereBetween('created_at', [$start, $end])->count(),
                'low_stock' => Inventory::query()->get()->filter(fn (Inventory $i) => $i->isLowStock())->count(),
                'out_of_stock' => Inventory::query()->get()->filter(fn (Inventory $i) => $i->isOutOfStock())->count(),
            ],
            'top_overall' => $this->mostRequestedItemsThisMonth(1, $month)[0] ?? null,
            'faculty' => $memory['faculty'],
            'student' => $memory['student'],
            'restock' => $memory['restock'],
            'most_requested' => $this->mostRequestedItemsThisMonth(8, $month),
            'low_stock_items' => $this->lowStockItems()->take(10)->map(fn (Inventory $i) => [
                'item' => $i->item_name,
                'available' => $i->availableQuantity(),
                'minimum' => (int) $i->minimum_stock,
            ])->values()->all(),
        ];
    }

    public function monthlySummary(?Carbon $month = null): string
    {
        $report = $this->monthlySummaryReport($month);
        $top = $report['top_overall'];
        $facultyTop = $report['faculty'][0] ?? null;
        $studentTop = $report['student'][0] ?? null;
        $topLabel = $top ? "{$top['item']} ({$top['total']})" : 'N/A';
        $facultyLabel = $facultyTop ? "{$facultyTop['item']} ({$facultyTop['faculty_qty']})" : 'N/A';
        $studentLabel = $studentTop ? "{$studentTop['item']} ({$studentTop['student_qty']})" : 'N/A';
        $period = $report['is_current'] ? 'This month' : $report['month_label'];
        $c = $report['counts'];

        return "{$period}: {$c['faculty_requests']} faculty request(s), {$c['student_purchases']} student purchase(s), "
            ."{$c['low_stock']} low-stock and {$c['out_of_stock']} out-of-stock item(s). Most requested: {$topLabel}. "
            ."Faculty most requested: {$facultyLabel}. Student most purchased: {$studentLabel}.";
    }

    /**
     * @param  array<int, array{role?: string, text?: string}>  $history
     */
    public function chatResponse(User $user, string $question, array $history = []): string
    {
        try {
            $this->chatLang = $this->detectChatLanguage($question);
            $question = $this->resolveFollowUpQuestion($user, $question, $history);

            if ($this->isAllowedChatQuestion($user, $question)) {
                return $this->ruleBasedChatResponse($user, $question);
            }

            $rule = $this->ruleBasedChatResponse($user, $question);
            if (! $this->isGenericHelpReply($rule, $user)) {
                return $rule;
            }

            $llm = $this->ollama->reply($user, $question, $this->groundingContext($user, $question));
            if (is_string($llm) && trim($llm) !== '') {
                return trim($llm);
            }

            if ($this->ollama->isEnabled()) {
                return 'The local AI (Ollama) did not reply. '.$this->helpForRole($user);
            }

            return $rule;
        } catch (\Throwable $e) {
            report($e);

            return $this->inLang(
                'I could not finish that answer. Try "How many items?", an item name, or pick a listed question.',
                'Hindi ko natapos ang sagot. Subukan ang "How many items?", pangalan ng item, o pumili sa listahan.',
                'Wala nako natapos ang tubag. Sulayi ang "How many items?", ngalan sa item, o pagpili sa lista.'
            );
        }
    }

    /**
     * Compact live facts injected into the local Ollama prompt.
     */
    public function groundingContext(User $user, string $question = ''): string
    {
        $lines = [];
        $lines[] = 'User: '.$user->name;
        $lines[] = 'Role: '.($user->getRoleNames()->implode(', ') ?: 'none');
        $dept = $user->department;
        $lines[] = 'Department: '.($dept?->name ?? 'none').($dept?->code ? ' ('.$dept->code.')' : '');
        foreach ($this->liveDirectoryFacts($user) as $fact) {
            $lines[] = $fact;
        }
        $lines[] = $this->monthlySummary();
        $lines[] = $this->inventoryCountFact($user);
        $lines[] = $this->namedItemFact($user, $question);
        $memory = $this->monthlyDemandMemory();
        $topRequested = $this->mostRequestedItemsThisMonth(5);
        $lines[] = $topRequested === []
            ? 'Most requested this month: none'
            : "Most requested this month:\n".$this->bulletList(collect($topRequested)
                ->map(fn (array $row) => "{$row['item']} {$row['total']} (faculty {$row['faculty_qty']}, student {$row['student_qty']})"));
        $lines[] = ($memory['faculty'] ?? []) === []
            ? 'Remembered faculty most requested this month: none'
            : 'Remembered faculty most requested this month: '.collect($memory['faculty'])
                ->map(fn (array $row) => "{$row['item']} {$row['faculty_qty']}")
                ->implode('; ');
        $lines[] = ($memory['student'] ?? []) === []
            ? 'Remembered student most purchased this month: none'
            : 'Remembered student most purchased this month: '.collect($memory['student'])
                ->map(fn (array $row) => "{$row['item']} {$row['student_qty']}")
                ->implode('; ');
        $lines[] = ($memory['restock'] ?? []) === []
            ? 'Remembered restock this month: none'
            : 'Remembered restock this month: '.collect($memory['restock'])
                ->map(fn (array $row) => "{$row['item']} demand {$row['demand']} available {$row['available']} reorder {$row['recommended_reorder']}")
                ->implode('; ');

        $low = $this->lowStockItems()->take(8);
        $lines[] = $low->isEmpty()
            ? 'Low stock: none'
            : 'Low stock: '.$low->map(
                fn (Inventory $i) => $i->item_name.' available '.$i->availableQuantity().' min '.$i->minimum_stock
            )->implode('; ');

        $out = Inventory::query()->get()->filter(fn (Inventory $i) => $i->isOutOfStock())->take(8);
        $lines[] = $out->isEmpty()
            ? 'Out of stock: none'
            : 'Out of stock: '.$out->map(fn (Inventory $i) => $i->item_name)->implode(', ');

        foreach ($this->inventoryForecasts(5, 14) as $forecast) {
            $days = $forecast['days_until_depletion'] ?? 'n/a';
            $lines[] = "Forecast {$forecast['item']}: available {$forecast['available']}, days {$days}, reorder {$forecast['recommended_reorder']}";
        }

        if ($user->hasRole('Student')) {
            $latest = PurchaseRequest::query()->where('user_id', $user->id)->latest('id')->first();
            $lines[] = $latest
                ? "Latest purchase {$latest->purchase_number} status {$latest->status}"
                : 'No student purchases yet.';
            $lines[] = 'Shop rule: only this department exclusive uniforms plus shared P.E., NSTP, ID lanyard.';
        }

        if ($user->hasRole('Faculty') && $dept) {
            $snapshot = $this->facultyBudget->snapshot($dept);
            $lines[] = sprintf(
                'Faculty budget %s %s: used %.2f of %.2f remaining %.2f',
                $snapshot['period_label'],
                $dept->name,
                $snapshot['used'],
                $snapshot['limit'],
                $snapshot['remaining']
            );
            $latest = SupplyRequest::query()->where('user_id', $user->id)->latest('id')->first();
            $lines[] = $latest
                ? "Latest request {$latest->request_number} status {$latest->status} amount {$latest->total_amount}"
                : 'No faculty requests yet.';
        }

        if ($user->hasAnyRole(['Accounting', 'Administrator', 'Admission', 'Supply Personnel'])) {
            $forecast = $this->currentSemesterTrendForecast(5);
            $lines[] = $forecast['items'] === []
                ? 'Semester trend forecast: '.$forecast['summary']
                : 'Semester trend forecast: '.collect($forecast['items'])
                    ->map(fn (array $row) => "{$row['item']} predicted {$row['predicted_remaining']} restock {$row['recommended_reorder']}")
                    ->implode('; ');
            $lines[] = 'Pending faculty requests: '.SupplyRequest::query()->where('status', 'pending')->count();
            $lines[] = 'Accounting review: '.SupplyRequest::query()->where('status', 'accounting_review')->count();
            $lines[] = 'Admin review: '.SupplyRequest::query()->where('status', 'admin_review')->count();
            $lines[] = 'Ready to release: '.SupplyRequest::query()->whereIn('status', ['approved', 'reserved'])->count();
            $lines[] = 'Payments waiting verify: '.PurchaseRequest::query()->where('status', 'payment_submitted')->count();
        }

        $nav = collect($this->navigationCatalog())
            ->filter(fn (array $page) => $user->hasAnyRole($page['roles']))
            ->unique('route')
            ->map(fn (array $page) => $page['label'].' '.$this->pageUrl($page['route']))
            ->implode('; ');
        $lines[] = $nav === ''
            ? 'Pages this role can open: none'
            : 'Pages this role can open (include the URL when they ask where to go): '.$nav;
        $lines[] = 'Users may ask live counts (departments, categories, units of measurement, users, suppliers), one item by name or item code, a REQ- or PUR- number, what to do next, compare this month vs last month, what will trend this semester, or semester restock from last year same months.';
        $lines[] = 'Never invent quantities, department counts, category counts, unit-of-measurement counts, user counts, or names that are not listed above.';
        $lines[] = 'If Named items in this question is none, do not answer with an item from low stock, most requested, forecast, or any other list just because a word looks similar.';

        return implode("\n", $lines);
    }

    public function ruleBasedChatResponse(User $user, string $question): string
    {
        $q = $this->normalizeQuestion($question);
        $role = $user->getRoleNames()->first() ?? 'User';

        $this->chatLang = $this->detectChatLanguage($question);

        $document = $this->extractDocumentNumber($question);
        if ($document !== null) {
            return $this->answerDocumentLookup($user, $document);
        }

        if ($this->matches($q, $this->intentNeedles('next_action'))) {
            return $this->answerNextAction($user);
        }

        if ($this->matches($q, $this->intentNeedles('compare_months'))) {
            return $this->answerCompareMonths();
        }

        if ($this->matches($q, $this->intentNeedles('semester_restock'))) {
            return $this->answerSemesterRestock();
        }

        if ($this->matches($q, $this->intentNeedles('budget'))) {
            return $this->answerBudgetCheck($user, $question);
        }

        if ($this->isNavigationQuestion($q)) {
            return $this->answerNavigation($user, $q);
        }

        if ($this->matches($q, $this->intentNeedles('departments'))) {
            return $this->answerDepartments();
        }

        if ($this->matches($q, $this->intentNeedles('categories'))) {
            return $this->answerCategories();
        }

        if ($this->isUnitOfMeasurementQuestion($q)) {
            return $this->answerUnits();
        }

        if ($this->matches($q, $this->intentNeedles('suppliers'))) {
            return $this->answerSuppliers($user);
        }

        if ($this->matches($q, $this->intentNeedles('users_count'))) {
            return $this->answerUserCounts($user);
        }

        // How-to steps after navigation so “where is / go to” returns the page URL.
        if ($this->matches($q, $this->intentNeedles('buy_uniform'))) {
            return $this->answerHowToBuy($user);
        }

        if ($this->matches($q, $this->intentNeedles('list_uniforms'))) {
            return $this->answerUniformsForStudent($user);
        }

        if ($this->matches($q, $this->intentNeedles('how_to_request'))) {
            return $this->answerHowToRequest($user);
        }

        if ($this->matches($q, $this->intentNeedles('my_purchases'))) {
            return $this->answerMyPurchases($user);
        }

        if ($this->matches($q, $this->intentNeedles('my_requests'))) {
            return $this->answerMyRequests($user);
        }

        if ($this->matches($q, $this->intentNeedles('verify_payment'))) {
            return $this->answerPayments($user);
        }

        if ($this->matches($q, $this->intentNeedles('pending'))) {
            return $this->answerPending($user);
        }

        if ($this->matches($q, $this->intentNeedles('approvals'))) {
            return $this->answerApprovals($user);
        }

        if ($this->matches($q, $this->intentNeedles('releases'))) {
            return $this->answerReleases($user);
        }

        if ($this->matches($q, $this->intentNeedles('payment')) && $user->hasAnyRole(['Accounting', 'Administrator', 'Student'])) {
            return $this->answerPayments($user);
        }

        if ($this->matches($q, $this->intentNeedles('low_stock'))) {
            return $this->answerLowStock($user);
        }

        if ($this->matches($q, $this->intentNeedles('out_of_stock'))) {
            return $this->answerOutOfStock($user);
        }

        if ($this->isInventoryCountQuestion($q)) {
            return $this->answerInventoryCount($user);
        }

        if ($this->isInventoryListQuestion($q)) {
            return $this->answerInventoryList($user);
        }

        if ($this->matches($q, $this->intentNeedles('most_faculty'))) {
            return $this->answerMostRequested('faculty');
        }

        if ($this->matches($q, $this->intentNeedles('most_student'))) {
            return $this->answerMostRequested('student');
        }

        if ($this->matches($q, $this->intentNeedles('restock_month'))) {
            return $this->answerRestockThisMonth();
        }

        if ($this->matches($q, $this->intentNeedles('forecast'))) {
            return $this->answerForecasts();
        }

        if ($this->matches($q, $this->intentNeedles('most_requested'))) {
            return $this->answerMostRequested();
        }

        if ($this->matches($q, $this->intentNeedles('monthly'))) {
            return $this->monthlySummary();
        }

        $matchedItems = $this->findInventoryMatches($user, $question);
        if ($matchedItems->isNotEmpty()) {
            return $this->answerMatchedItems(
                $user,
                $matchedItems,
                $this->matches($q, $this->intentNeedles('why_low'))
            );
        }

        if ($this->matches($q, $this->intentNeedles('why_low'))) {
            return $this->answerWhyLowStock($user);
        }

        $categoryKeyword = $this->matchCategoryStockKeyword($q);
        if ($categoryKeyword !== null) {
            if ($categoryKeyword === 'uniform') {
                return $this->answerUniformsForStudent($user);
            }

            return $this->answerCategoryStock($categoryKeyword, $user);
        }

        if ($this->matches($q, $this->intentNeedles('help'))) {
            return $this->helpForRole($user);
        }

        return $this->inLang(
            "I'm your {$role} assistant. ",
            "Ako ang {$role} assistant mo. ",
            "Ako ang imong {$role} assistant. "
        ).$this->helpForRole($user);
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
                'What should I do next?',
                'Status of my request',
                'My pending requests',
                'How do I request supplies?',
                'Where do I submit a request?',
                'Can I still request supplies?',
                'How many items?',
                'List all items',
                'What items are low in stock?',
                'Out of stock',
                'Monthly summary',
            ],
            $user->hasRole('Student') => [
                'What should I do next?',
                'What uniforms can I buy?',
                'How do I buy uniforms?',
                'Where is Uniform Shop?',
                'List all items',
                'My purchases',
                'Purchase status',
            ],
            $user->hasRole('Accounting') => [
                'What should I do next?',
                'Where do I verify payments?',
                'Pending payments',
                'Pending requests',
                'How many items?',
                'List all items',
                'Most requested this month',
                'Most requested by faculty',
                'Most purchased by students',
                'Compare this month to last month',
                'Monthly summary',
                'Low stock',
                'Out of stock',
            ],
            $user->hasRole('Supply Personnel') => [
                'What should I do next?',
                'Where do I view reports?',
                'How many items?',
                'How many units of measurement?',
                'List all items',
                'Most requested this month',
                'Most requested by faculty',
                'Most purchased by students',
                'What needs restock this month',
                'What will trend this semester?',
                'What should we restock this semester?',
                'Compare this month to last month',
                'Reorder recommendations',
                'Ready for release',
                'Low stock',
                'Out of stock',
                'Computer supplies',
                'Office supplies',
                'Laboratory supplies',
                'Monthly summary',
            ],
            $user->hasRole('Administrator') => [
                'What should I do next?',
                'Where do I view reports?',
                'How many items?',
                'How many units of measurement?',
                'List all items',
                'Most requested this month',
                'Most requested by faculty',
                'Most purchased by students',
                'What needs restock this month',
                'What will trend this semester?',
                'What should we restock this semester?',
                'Compare this month to last month',
                'For approval',
                'Pending requests',
                'Ready for release',
                'Reorder',
                'Low stock',
                'Monthly summary',
            ],
            $user->hasRole('Admission') => [
                'What should I do next?',
                'How many items?',
                'List all items',
                'Most requested this month',
                'Most requested by faculty',
                'Most purchased by students',
                'Compare this month to last month',
                'For approval',
                'Pending requests',
                'Monthly summary',
                'Low stock',
            ],
            default => ['How many items?', 'List all items', 'Low stock', 'Monthly summary'],
        };
    }

    public function isAllowedChatQuestion(User $user, string $question): bool
    {
        $needle = mb_strtolower(trim($question));

        foreach ($this->chatSuggestions($user) as $allowed) {
            if (mb_strtolower($allowed) === $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildForecastRows(?int $maxDays = 30): array
    {
        $since = Carbon::now()->subDays($this->lookbackDays);

        $usageByInventory = Transaction::query()
            ->where('created_at', '>=', $since)
            ->whereIn('type', ['release', 'stock_out', 'damage', 'bad_order', 'return_to_supplier', 'adjustment_out'])
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

    protected function isInventoryCountQuestion(string $q): bool
    {
        if ($this->matches($q, $this->intentNeedles('inventory_count'))) {
            return true;
        }

        $asksHowMany = $this->matches($q, [
            'how many', 'how much', 'ilan ang', 'ilang ', 'pila ka', 'pila ang',
            'gaano karami', 'unsa kadaghan',
        ]);
        $aboutStock = $this->matches($q, [
            'item', 'stock', 'inventory', 'available', 'on hand', 'on-hand',
            'type', 'klase', 'sku',
        ]);

        return $asksHowMany && $aboutStock;
    }

    protected function isInventoryListQuestion(string $q): bool
    {
        if ($this->isInventoryCountQuestion($q) && ! $this->matches($q, [
            'list all', 'list the', 'show all', 'lahat ng', 'tanang item', 'tanan nga', 'listahan',
        ])) {
            return false;
        }

        return $this->matches($q, $this->intentNeedles('inventory_list'));
    }

    /**
     * @return 'en'|'fil'|'ceb'
     */
    protected function detectChatLanguage(string $question): string
    {
        $q = $this->normalizeQuestion($question);

        if ($this->matches($q, [
            'pila ka', 'pila ang', 'unsaon', 'unsa ', 'nahurot', 'walay stock',
            'hapit mahurot', 'pangayo', 'palit uniporme', 'pagpalit', 'karong bulan',
            'akong request', 'akong palit', 'naghulat', 'tabang',
        ])) {
            return 'ceb';
        }

        if ($this->matches($q, [
            'ilan ang', 'ilang ', 'gaano karami', 'kakaunti', 'malapit na maubos',
            'ubos na', 'walang stock', 'wala nang', 'bumili', 'paano ', 'pano ',
            'kahilingan', 'nakabinbin', 'ngayong buwan', 'tulong',
        ])) {
            return 'fil';
        }

        return 'en';
    }

    /**
     * @return array<int, string>
     */
    protected function intentNeedles(string $intent): array
    {
        return match ($intent) {
            'buy_uniform' => [
                'how do i buy', 'how to buy', 'buy uniform', 'buying uniform',
                'uniform shop', 'checkout', 'payment slip', 'upload receipt',
                'paano bumili', 'pano bumili', 'bumili ng uniporme', 'bili uniporme',
                'unsaon pagpalit', 'unsaon pag palit', 'palit uniporme', 'asa palit',
            ],
            'list_uniforms' => [
                'what uniform', 'which uniform', 'uniforms can i', 'can i buy',
                'available uniform', 'my uniform', 'department uniform',
                'anong uniporme', 'anong uniform', 'unsa nga uniporme', 'unsa ang uniporme',
                'pwede nako paliton', 'pwede kong bilhin',
            ],
            'how_to_request' => [
                'how do i request', 'how to request', 'submit request', 'request supplies', 'new request',
                'paano mag request', 'paano mag-request', 'paano humingi',
                'unsaon pag request', 'unsaon pag-request', 'pangayo og supply',
            ],
            'departments' => [
                'how many department', 'how many departments', 'list department', 'list departments',
                'all department', 'all departments', 'departments in', 'department in total',
                'total department', 'total departments',
                'ilan ang department', 'ilang department', 'mga department',
                'pila ka department', 'pila ang department',
            ],
            'categories' => [
                'how many categor', 'list categor', 'all categor', 'item categor',
                'ilan ang categor', 'ilang categor', 'mga categor',
                'pila ka categor', 'pila ang categor',
            ],
            'units' => [
                'unit of measurement', 'units of measurement', 'unit of measure', 'units of measure',
                'measurement unit', 'measurement units', 'how many uom', 'list uom', 'all uom',
                'list unit of', 'list units of', 'list the unit', 'list the units',
                'ilan ang uom', 'ilang uom', 'ilan ang unit of', 'ilang unit of',
                'pila ka uom', 'pila ang uom', 'pila ka unit of', 'pila ang unit of',
                'mga unit of measurement', 'mga yunit',
            ],
            'suppliers' => [
                'how many supplier', 'list supplier', 'all supplier',
                'ilan ang supplier', 'ilang supplier', 'mga supplier',
                'pila ka supplier', 'pila ang supplier',
            ],
            'users_count' => [
                'how many user', 'how many users', 'how many faculty', 'how many student',
                'how many admin', 'list users', 'user count',
                'ilan ang user', 'ilang user', 'ilan ang faculty', 'ilan ang student',
                'pila ka user', 'pila ang user', 'pila ka faculty', 'pila ka student',
            ],
            'my_purchases' => [
                'my purchase', 'purchase status', 'what did i buy', 'my order', 'my orders',
                'mga nabili ko', 'binili ko', 'akong palit', 'akong order',
            ],
            'my_requests' => [
                'my request', 'request status', 'status of my request', 'status request', 'my pending request',
                'akong request', 'status ng request', 'status sa request', 'asa na ang request',
            ],
            'verify_payment' => [
                'pending payment', 'verify payment', 'payments to verify', 'to verify',
                'i-verify ang bayad', 'i verify ang bayad', 'bayad na i-verify',
            ],
            'pending' => [
                'pending request', 'pending', 'nakabinbin', 'naghulat nga request',
            ],
            'approvals' => [
                'approve', 'admin review', 'for approval', 'waiting for approval',
                'para maaprubahan', 'para ma-approve', 'hulat og approval',
            ],
            'releases' => [
                'release', 'ready for release', 'pwede na i-release', 'pwede na i release',
                'andam na i-release',
            ],
            'payment' => [
                'payment', 'bayad', 'resibo',
            ],
            'low_stock' => [
                'low stock', 'low-stock', 'low in stock', 'below minimum', 'running low', 'items are low',
                'kakaunti na', 'kulang na', 'gamay na ang stock', 'malapit na maubos', 'hapit mahurot',
                'hapit na mahurot', 'ubos na ang stock',
            ],
            'out_of_stock' => [
                'out of stock', 'out-of-stock', 'no stock', 'zero stock',
                'ubos na', 'wala nang stock', 'walang stock', 'nahurot', 'walay stock', 'wala na stock',
            ],
            'inventory_list' => [
                'list all item', 'list all items', 'list the item', 'list the items',
                'show all item', 'show all items', 'all items', 'all item',
                'item list', 'inventory list',
                'lahat ng item', 'lahat ng mga item', 'listahan', 'ano ang mga item',
                'anong mga item', 'tanang item', 'tanan nga item', 'lista sa item',
            ],
            'inventory_count' => [
                'how many item', 'how many items', 'how many available', 'how many type',
                'how many types', 'item by type', 'items by type', 'item type',
                'total item', 'total items',
                'items in inventory', 'item in inventory', 'inventory count',
                'number of item', 'number of items', 'how much stock',
                'total stock', 'available stock', 'on hand stock', 'on-hand stock',
                'available item', 'available items',
                'available by type', 'available by category',
                'stock by type', 'items by category',
                'ilan ang item', 'ilan ang mga item', 'ilang item', 'ilang klase',
                'gaano karami', 'pila ka item', 'pila ang item', 'pila ka stock',
                'pila sa inventory', 'unsa kadaghan',
            ],
            'forecast' => [
                'forecast', 'reorder', 'restock', 'run out', 'recommendation',
                'rekomenda', 'rekomendar', 'palit og balik',
            ],
            'restock_month' => [
                'what needs restock this month', 'need restock this month', 'needs restock this month',
                'restock this month', 'restock of the month', 'predict restock',
                'kailangan i-restock', 'anong kailangan i-restock', 'kinahanglan i-restock',
                'unsa ang kinahanglan i-restock',
            ],
            'most_faculty' => [
                'most requested by faculty', 'faculty most requested', 'faculty requested',
                'top faculty request', 'pinaka-hinihingi ng faculty', 'hinihingi ng faculty',
                'gipangayo sa faculty', 'labing gipangayo sa faculty',
            ],
            'most_student' => [
                'most purchased by students', 'most purchased', 'student most purchased',
                'top student purchase', 'pinakamaraming bili', 'pinakamaraming binili',
                'gipalit sa estudyante', 'labing gipalit',
            ],
            'most_requested' => [
                'most requested', 'top requested', 'top item', 'pinaka requested',
                'pinaka-hinihingi', 'pinaka hinihingi', 'pinakamaraming request',
                'labing gipangayo', 'pinakadaghang request', 'unsa ang pinaka',
            ],
            'monthly' => [
                'monthly', 'summary', 'this month', 'ngayong buwan', 'karong bulan', 'buod',
            ],
            'next_action' => [
                'what should i do next', 'what should i do', 'next action', 'next step',
                'ano ang susunod', 'ano dapat kong gawin', 'unsa akong buhaton',
                'unsa ang sunod', 'unsaon nako sunod',
            ],
            'compare_months' => [
                'compare this month', 'compare to last month', 'versus last month',
                'vs last month', 'last month', 'miaging bulan', 'noong isang buwan',
                'kaysa last month', 'kaysa ning miaging',
            ],
            'semester_restock' => [
                'restock this semester', 'this semester', 'before classes',
                'enrollment', 'same month last year', 'last year this month',
                'what will trend', 'will trend this', 'trend this semester',
                'forecast this semester', 'predict this semester',
                'karong semester', 'ngayong semester',
            ],
            'budget' => [
                'can i still request', 'can i request', 'kaya pa ba', 'budget',
                'pwede pa ko mag request', 'pwede pa bang mag-request',
                'remaining budget', 'faculty budget',
            ],
            'why_low' => [
                'why is', 'why is it', 'why low', 'explain', 'ngano', 'bakit kulang',
                'bakit mababa', 'ngano gamay', 'ngano kulang',
            ],
            'help' => [
                'help', 'what can you', 'commands', 'frequent question',
                'tulong', 'tabang', 'ano ang kaya mo', 'unsa imong mahimo',
            ],
            default => [],
        };
    }

    protected function inLang(string $en, string $fil, string $ceb): string
    {
        return match ($this->chatLang) {
            'fil' => $fil,
            'ceb' => $ceb,
            default => $en,
        };
    }

    protected function helpForRole(User $user): string
    {
        $options = collect($this->chatSuggestions($user))->map(fn (string $q) => '"'.$q.'"')->implode(', ');

        if ($options === '') {
            return $this->inLang(
                'No questions are available for your role.',
                'Walang tanong para sa iyong role.',
                'Walay pangutana para sa imong role.'
            );
        }

        return $this->inLang(
            'You can type a question or choose one of these: '.$options.'.',
            'Pwede kang mag-type o pumili: '.$options.'.',
            'Pwede ka mo-type o mopili: '.$options.'.'
        );
    }

    protected function isNavigationQuestion(string $q): bool
    {
        return $this->matches($q, [
            'where do i', 'where can i', 'where to view', 'where to open', 'where is the',
            'where is uniform', 'where is report', 'where is restock', 'where is inventory',
            'which page', 'what page', 'navigate', 'go to the', 'go to report', 'go to inventory',
            'open the page', 'open report', 'open inventory', 'open restock', 'open shop',
            'view the page', 'view report', 'link to', 'give me the link', 'send the link',
            'how do i open', 'how to open', 'paano pumunta', 'paano mag-open', 'paano mag open',
            'saan ako', 'saan ang', 'saan pwedeng', 'asa ko', 'asa ang', 'adto sa', 'pumunta sa',
            'unsa nga page', 'asa nako tan-awon',
        ]);
    }

    protected function answerNavigation(User $user, string $q): string
    {
        $page = $this->matchNavigationPage($q);

        if ($page) {
            if (! $user->hasAnyRole($page['roles'])) {
                return $this->inLang(
                    "{$page['label']} is not in your menu. ".$this->navigationMenuList($user),
                    "Ang {$page['label']} wala sa menu mo. ".$this->navigationMenuList($user),
                    "Ang {$page['label']} wala sa imong menu. ".$this->navigationMenuList($user)
                );
            }

            $url = $this->pageUrl($page['route']);

            return $this->inLang(
                "Open {$page['label']} from the sidebar. Link: {$url}",
                "Buksan ang {$page['label']} sa sidebar. Link: {$url}",
                "Ablihi ang {$page['label']} sa sidebar. Link: {$url}"
            );
        }

        return $this->inLang(
            $this->navigationMenuList($user),
            $this->navigationMenuList($user),
            $this->navigationMenuList($user)
        );
    }

    /**
     * @return array{label: string, route: string, roles: list<string>, needles: list<string>}|null
     */
    protected function matchNavigationPage(string $q): ?array
    {
        $best = null;
        $bestLen = 0;

        foreach ($this->navigationCatalog() as $page) {
            foreach ($page['needles'] as $needle) {
                if (str_contains($q, $needle) && strlen($needle) > $bestLen) {
                    $best = $page;
                    $bestLen = strlen($needle);
                }
            }
        }

        return $best;
    }

    protected function navigationMenuList(User $user): string
    {
        $lines = collect($this->navigationCatalog())
            ->filter(fn (array $page) => $user->hasAnyRole($page['roles']))
            ->unique('route')
            ->map(fn (array $page) => $page['label'].': '.$this->pageUrl($page['route']))
            ->values();

        if ($lines->isEmpty()) {
            return $this->inLang(
                'I could not match a page. Use the sidebar menu for your role.',
                'Walang tumugmang page. Gamitin ang sidebar.',
                'Walay nahiuyon nga page. Gamita ang sidebar.'
            );
        }

        return $this->inLang(
            'Pages you can open: '.$lines->implode(' | '),
            'Mga page na pwede mong buksan: '.$lines->implode(' | '),
            'Mga page nga pwede nimo ablihan: '.$lines->implode(' | ')
        );
    }

    protected function pageUrl(string $route): string
    {
        try {
            return route($route, [], false);
        } catch (\Throwable) {
            return '/';
        }
    }

    /**
     * @return list<array{label: string, route: string, roles: list<string>, needles: list<string>}>
     */
    protected function navigationCatalog(): array
    {
        $staffView = ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty'];
        $supplyAdmin = ['Supply Personnel', 'Administrator'];
        $reports = ['Administrator', 'Accounting', 'Supply Personnel'];

        return [
            ['label' => 'Supplies issuance log', 'route' => 'reports.supplies-issuance', 'roles' => $reports, 'needles' => ['issuance log', 'issuance', 'supplies issuance']],
            ['label' => 'Restock Tips', 'route' => 'ai.restock', 'roles' => $supplyAdmin, 'needles' => ['restock tip', 'restock page', 'reorder recommendation']],
            ['label' => 'Reports', 'route' => 'reports.index', 'roles' => $reports, 'needles' => ['report', 'demand trend', 'most requested', 'monthly summary']],
            ['label' => 'Stock Operations', 'route' => 'supply.stock.index', 'roles' => $supplyAdmin, 'needles' => ['stock operation', 'stock in', 'stock out', 'stock page']],
            ['label' => 'Release Items', 'route' => 'supply.releases', 'roles' => $supplyAdmin, 'needles' => ['release item', 'release page', 'ready for release']],
            ['label' => 'Purchase History', 'route' => 'supply.purchase-history', 'roles' => $supplyAdmin, 'needles' => ['purchase history']],
            ['label' => 'Student Purchases', 'route' => 'supply.purchases', 'roles' => $supplyAdmin, 'needles' => ['student purchase']],
            ['label' => 'Students', 'route' => 'supply.students.index', 'roles' => $supplyAdmin, 'needles' => ['student account', 'student list']],
            ['label' => 'Verify Payments', 'route' => 'accounting.payments', 'roles' => ['Accounting'], 'needles' => ['verify payment', 'payment page', 'pending payment']],
            ['label' => 'Review Requests', 'route' => 'accounting.requests', 'roles' => ['Accounting'], 'needles' => ['review request']],
            ['label' => 'Approve Requests', 'route' => 'admin.requests', 'roles' => ['Administrator', 'Admission'], 'needles' => ['approve request', 'for approval']],
            ['label' => 'New Request', 'route' => 'requests.create', 'roles' => ['Faculty'], 'needles' => ['new request', 'submit a request', 'submit request', 'create request']],
            ['label' => 'My Requests', 'route' => 'requests.index', 'roles' => ['Faculty'], 'needles' => ['my request', 'request list']],
            ['label' => 'Uniform Shop', 'route' => 'shop.index', 'roles' => ['Student'], 'needles' => ['uniform shop', 'shop', 'buy uniform']],
            ['label' => 'My Purchases', 'route' => 'purchases.index', 'roles' => ['Student'], 'needles' => ['my purchase', 'purchase list']],
            ['label' => 'Inventory', 'route' => 'inventory.index', 'roles' => $staffView, 'needles' => ['inventory', 'item list page', 'stock list']],
            ['label' => 'Users', 'route' => 'admin.users.index', 'roles' => $supplyAdmin, 'needles' => ['user page', 'users']],
            ['label' => 'Categories', 'route' => 'admin.categories.index', 'roles' => $supplyAdmin, 'needles' => ['categor']],
            ['label' => 'Units', 'route' => 'admin.units.index', 'roles' => $supplyAdmin, 'needles' => ['unit of measurement', 'units of measurement', 'uom', 'units page']],
            ['label' => 'Departments', 'route' => 'admin.departments.index', 'roles' => $supplyAdmin, 'needles' => ['department']],
            ['label' => 'Suppliers', 'route' => 'admin.suppliers.index', 'roles' => $supplyAdmin, 'needles' => ['supplier']],
            ['label' => 'Announcements', 'route' => 'admin.announcements.index', 'roles' => $supplyAdmin, 'needles' => ['announcement']],
            ['label' => 'Audit Logs', 'route' => 'admin.audit-logs', 'roles' => $supplyAdmin, 'needles' => ['audit log', 'audit']],
            ['label' => 'AI Assistant', 'route' => 'ai.chat', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'], 'needles' => ['ai assistant', 'ai page', 'chat page']],
            ['label' => 'Dashboard', 'route' => 'dashboard', 'roles' => ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'], 'needles' => ['dashboard', 'home']],
        ];
    }

    protected function isGenericHelpReply(string $reply, User $user): bool
    {
        $role = $user->getRoleNames()->first() ?? 'User';

        return str_contains($reply, $this->helpForRole($user))
            || str_contains($reply, "I'm your {$role} assistant.")
            || str_contains($reply, "Ako ang {$role} assistant")
            || str_contains($reply, "Ako ang imong {$role} assistant");
    }

    protected function answerHowToBuy(User $user): string
    {
        if (! $user->hasRole('Student') && ! $user->hasRole('Administrator')) {
            return $this->inLang(
                'Uniform purchases are for Student accounts. Students use Uniform Shop with Student ID + last name login.',
                'Ang pagbili ng uniporme ay para sa Student. Gamitin ang Uniform Shop (Student ID + last name).',
                'Ang pagpalit og uniporme para sa Student. Gamita ang Uniform Shop (Student ID + last name).'
            );
        }

        $dept = $user->department?->name ?? $this->inLang('your department', 'iyong department', 'imong department');

        return $this->inLang(
            "To buy uniforms: open Uniform Shop {$this->pageUrl('shop.index')} → choose a size for each uniform → add your department items (and shared P.E., NSTP, or ID lanyard) → View Cart → Checkout → pay over the counter → upload your receipt → Accounting verifies → Supply releases. You only see exclusive uniforms for {$dept}, plus shared items. Ask \"What uniforms can I buy?\" to list them.",
            "Para bumili ng uniporme: buksan ang Uniform Shop {$this->pageUrl('shop.index')} → pumili ng size → i-add ang items ng {$dept} (at shared P.E., NSTP, o ID lanyard) → View Cart → Checkout → magbayad sa Accounting → i-upload ang resibo → i-verify → i-release ng Supply.",
            "Para mopalit og uniporme: ablihi ang Uniform Shop {$this->pageUrl('shop.index')} → pili og size → i-add ang items sa {$dept} (ug shared P.E., NSTP, o ID lanyard) → View Cart → Checkout → bayad sa Accounting → i-upload ang resibo → i-verify → i-release sa Supply."
        );
    }

    protected function answerHowToRequest(User $user): string
    {
        if ($user->hasRole('Student')) {
            return $this->inLang(
                'Students do not submit faculty supply requests. Use Uniform Shop to buy uniforms. Ask "How do I buy uniforms?" for steps.',
                'Ang Student ay hindi nagre-request ng faculty supplies. Gamitin ang Uniform Shop para bumili ng uniporme.',
                'Ang Student dili mag-request og faculty supplies. Gamita ang Uniform Shop para mopalit og uniporme.'
            );
        }

        return $this->inLang(
            'Faculty: go to New Request '.$this->pageUrl('requests.create').' — pick items and purpose, then submit. Flow: Accounting review → Admin approval → Supply release. Track progress under My Requests '.$this->pageUrl('requests.index'),
            'Faculty: pumunta sa New Request, piliin ang items at purpose, tapos i-submit. Flow: Accounting review → Admin approval → Supply release. Tingnan ang My Requests.',
            'Faculty: adto sa New Request, pili og items ug purpose, dayon i-submit. Flow: Accounting review → Admin approval → Supply release. Tan-awa ang My Requests.'
        );
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

        return $this->linedAnswer(
            "Uniforms you can buy for {$dept}:",
            $items->map(function (Inventory $i) {
                $tag = $i->isDepartmentExclusive() ? 'exclusive' : 'shared';
                $stock = $i->availableQuantity() > 0
                    ? "{$i->availableQuantity()} {$i->unit} avail"
                    : 'out of stock';

                return "{$i->item_name} ({$tag}, ₱".number_format((float) $i->unit_price, 2).", {$stock})";
            }),
            'Open Uniform Shop to add items to your cart.'
        );
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

            return $this->linedAnswer(
                'Uniform stock attention:',
                $items->map(fn (Inventory $i) => "{$i->item_name} ({$i->availableQuantity()} {$i->unit})")
            );
        }

        $items = $this->lowStockItems();

        if ($items->isEmpty()) {
            return 'No items are currently below minimum stock levels.';
        }

        return $this->linedAnswer(
            'Low stock items:',
            $items->take(15)->map(
                fn (Inventory $i) => "{$i->item_name} ({$i->availableQuantity()} {$i->unit}, min {$i->minimum_stock})"
            )
        );
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

    protected function inventoryCountFact(User $user): string
    {
        return $this->answerInventoryCount($user);
    }

    protected function namedItemFact(User $user, string $question): string
    {
        if (trim($question) === '') {
            return 'Named items in this question: none. Do not mention a specific inventory item unless the user used that exact item name or item code.';
        }

        $items = $this->findInventoryMatches($user, $question);
        if ($items->isEmpty()) {
            return 'Named items in this question: none. Do not mention a specific inventory item from low stock, most requested, forecast, or any other list just because a word looks similar. Ask for the exact item name or item code.';
        }

        return "Items clearly named in this question:\n".$this->formatInventoryAvailability($items->take(15));
    }

    protected function answerInventoryCount(User $user): string
    {
        if ($user->hasRole('Student')) {
            $items = Inventory::forStudentShop($user)->with('sizeStocks')->get();
            $skus = $items->count();
            $available = (int) $items->sum(fn (Inventory $i) => $i->availableQuantity());

            if ($skus === 0) {
                return $this->inLang(
                    'You have no Uniform Shop items available for your account.',
                    'Walang Uniform Shop item para sa account mo.',
                    'Walay Uniform Shop item para sa imong account.'
                );
            }

            return $this->inLang(
                "You can see {$skus} Uniform Shop item(s) (shared plus your department). Combined available quantity: {$available}.",
                "Makikita mo ang {$skus} item sa Uniform Shop (shared plus department mo). Available lahat: {$available}.",
                "Makita nimo ang {$skus} item sa Uniform Shop (shared plus imong department). Available tanan: {$available}."
            );
        }

        $inventory = Inventory::query()->with(['sizeStocks', 'category'])->get();
        $skus = $inventory->count();
        $onHand = (int) $inventory->sum('quantity');
        $reserved = (int) $inventory->sum('reserved_quantity');
        $available = (int) $inventory->sum(fn (Inventory $i) => $i->availableQuantity());
        $byType = $inventory
            ->groupBy(fn (Inventory $item) => $item->category?->name ?: 'Uncategorized')
            ->map(function ($rows, $name) {
                $types = $rows->count();
                $qty = (int) $rows->sum(fn (Inventory $item) => $item->availableQuantity());

                return "{$name}: {$types} type(s), available {$qty}";
            })
            ->values()
            ->implode('; ');

        $base = $this->inLang(
            "Inventory has {$skus} item type(s). On hand {$onHand}, reserved {$reserved}, available {$available}.",
            "May {$skus} klase ng item sa inventory. On hand {$onHand}, reserved {$reserved}, available {$available}.",
            "Naay {$skus} klase sa item sa inventory. On hand {$onHand}, reserved {$reserved}, available {$available}."
        );

        return $byType === ''
            ? $base
            : $base.' '.$this->inLang(
                "By category: {$byType}.",
                "Ayon sa kategorya: {$byType}.",
                "Pinaagi sa kategorya: {$byType}."
            );
    }

    /**
     * @return Collection<int, Inventory>
     */
    protected function inventoryCatalog(User $user): Collection
    {
        if ($user->hasRole('Student')) {
            return Inventory::forStudentShop($user)->with('sizeStocks')->orderBy('item_name')->get();
        }

        return Inventory::query()->with('sizeStocks')->orderBy('item_name')->get();
    }

    /**
     * @param  Collection<int, Inventory>  $items
     */
    protected function formatInventoryAvailability(Collection $items): string
    {
        return $this->bulletList($items->map(function (Inventory $item) {
            $unit = trim($item->unitLabel());
            $available = $item->availableQuantity();

            return $unit !== ''
                ? "{$item->item_name} available {$available} {$unit}"
                : "{$item->item_name} available {$available}";
        }));
    }

    /**
     * @param  iterable<int, mixed>  $lines
     */
    protected function bulletList(iterable $lines): string
    {
        return collect($lines)
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(fn (string $line) => str_starts_with($line, '• ') ? $line : '• '.$line)
            ->implode("\n");
    }

    /**
     * @param  iterable<int, mixed>  $lines
     */
    protected function linedAnswer(string $intro, iterable $lines = [], string $footer = ''): string
    {
        $parts = [rtrim($intro)];
        $body = $this->bulletList($lines);
        if ($body !== '') {
            $parts[] = $body;
        }
        $footer = trim($footer);
        if ($footer !== '') {
            $parts[] = $footer;
        }

        return implode("\n", $parts);
    }

    protected function departmentFact(): string
    {
        $rows = Department::query()->orderBy('name')->get(['name', 'code', 'is_active']);
        if ($rows->isEmpty()) {
            return 'Departments in PSIS: none';
        }

        $active = $rows->where('is_active', true);
        $list = $active->map(fn (Department $d) => ($d->code ? $d->code.' ' : '').$d->name)->implode('; ');
        $inactive = $rows->where('is_active', false)->count();

        return 'Departments in PSIS: '.$active->count().' active'
            .($inactive > 0 ? ", {$inactive} inactive" : '')
            .'. '.$list
            .'. Do not say there are only 5; include SHS, Administration, and Supply Office when they exist.';
    }

    protected function answerDepartments(): string
    {
        $rows = Department::query()->orderBy('name')->get();
        if ($rows->isEmpty()) {
            return $this->inLang(
                'No departments are recorded.',
                'Walang department sa record.',
                'Walay department sa record.'
            );
        }

        $active = $rows->where('is_active', true)->values();
        $colleges = ['CCS', 'CC', 'CTHM', 'CTE', 'CBA'];
        $collegeCount = $active->filter(fn (Department $d) => in_array((string) $d->code, $colleges, true))->count();

        return $this->linedAnswer(
            $this->inLang(
                "There are {$active->count()} active department(s) in PSIS (not only the {$collegeCount} colleges).",
                "May {$active->count()} active department sa PSIS (hindi lang ang {$collegeCount} college).",
                "Naa'y {$active->count()} active department sa PSIS (dili lang ang {$collegeCount} college)."
            ),
            $active->map(function (Department $d) {
                $tag = $d->code ? "{$d->code} — {$d->name}" : $d->name;

                return $d->is_active ? $tag : $tag.' (inactive)';
            }),
            $this->inLang(
                'SHS, Administration, and Supply Office count as departments too when they are in the list.',
                'Kasama rin ang SHS, Administration, at Supply Office kung nandiyan sila sa listahan.',
                'Apil usab ang SHS, Administration, ug Supply Office kung naa sila sa lista.'
            )
        );
    }

    /**
     * @return list<string>
     */
    protected function liveDirectoryFacts(User $user): array
    {
        $lines = [$this->departmentFact()];

        if ($user->hasRole('Student')) {
            return $lines;
        }

        $lines[] = $this->categoryFact();
        $lines[] = $this->unitFact();
        $lines[] = $this->inventorySplitFact();

        if ($user->hasAnyRole(['Administrator', 'Supply Personnel', 'Accounting', 'Admission'])) {
            $lines[] = $this->userRoleFact();
        }

        if ($user->hasAnyRole(['Administrator', 'Supply Personnel'])) {
            $lines[] = $this->supplierFact();
        }

        return $lines;
    }

    protected function categoryFact(): string
    {
        $rows = Category::query()->orderBy('name')->withCount('inventoryItems')->get();
        if ($rows->isEmpty()) {
            return 'Categories in PSIS: none';
        }

        $list = $rows->map(fn (Category $c) => $c->name.' '.$c->inventory_items_count.' item(s)')->implode('; ');

        return 'Categories in PSIS: '.$rows->count().'. '.$list.'.';
    }

    protected function unitFact(): string
    {
        $rows = UnitOfMeasurement::query()->orderBy('name')->withCount('inventoryItems')->get();
        if ($rows->isEmpty()) {
            return 'Units of measurement in PSIS: none';
        }

        $list = $rows->map(fn (UnitOfMeasurement $u) => $u->label().' '.$u->inventory_items_count.' item(s)')->implode('; ');

        return 'Units of measurement in PSIS: '.$rows->count().'. '.$list.'.';
    }

    protected function inventorySplitFact(): string
    {
        $shop = Inventory::query()->where('student_shop', true)->count();
        $office = Inventory::query()->where('student_shop', false)->count();

        return "Inventory split: Uniform Shop {$shop} SKU(s); office/supply items {$office} SKU(s).";
    }

    protected function userRoleFact(): string
    {
        $roles = ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'];
        $parts = [];
        foreach ($roles as $role) {
            $all = User::role($role)->count();
            $active = User::role($role)->where('is_active', true)->count();
            $parts[] = "{$role} {$active} active of {$all}";
        }

        return 'Users by role: '.implode('; ', $parts).'.';
    }

    protected function supplierFact(): string
    {
        $rows = Supplier::query()->orderBy('name')->get(['name', 'is_active']);
        if ($rows->isEmpty()) {
            return 'Suppliers in PSIS: none';
        }

        $active = $rows->where('is_active', true);
        $list = $active->map(fn (Supplier $s) => $s->name)->implode('; ');

        return 'Suppliers in PSIS: '.$active->count().' active of '.$rows->count().'. '.$list.'.';
    }

    protected function answerCategories(): string
    {
        $rows = Category::query()->orderBy('name')->withCount('inventoryItems')->get();
        if ($rows->isEmpty()) {
            return $this->inLang(
                'No categories are recorded.',
                'Walang category sa record.',
                'Walay category sa record.'
            );
        }

        return $this->linedAnswer(
            $this->inLang(
                "There are {$rows->count()} categories in PSIS:",
                "May {$rows->count()} category sa PSIS:",
                "Naa'y {$rows->count()} category sa PSIS:"
            ),
            $rows->map(fn (Category $c) => "{$c->name} — {$c->inventory_items_count} item(s)")
        );
    }

    protected function isUnitOfMeasurementQuestion(string $q): bool
    {
        if ($this->matches($q, $this->intentNeedles('units'))) {
            return true;
        }

        $asksHowManyUnits = $this->matches($q, [
            'how many unit', 'how many units',
            'ilan ang unit', 'ilang unit', 'ilan ang mga unit',
            'pila ka unit', 'pila ang unit',
        ]);

        if (! $asksHowManyUnits) {
            return false;
        }

        // "how many units of bond paper" is stock, not the UoM master list.
        return ! preg_match('/\bunits?\s+of\s+(?!measurement\b|measure\b)/', $q);
    }

    protected function answerUnits(): string
    {
        $rows = UnitOfMeasurement::query()->orderBy('name')->withCount('inventoryItems')->get();
        if ($rows->isEmpty()) {
            return $this->inLang(
                'No units of measurement are recorded.',
                'Walang unit of measurement sa record.',
                'Walay unit of measurement sa record.'
            );
        }

        return $this->linedAnswer(
            $this->inLang(
                "There are {$rows->count()} unit(s) of measurement in PSIS:",
                "May {$rows->count()} unit of measurement sa PSIS:",
                "Naa'y {$rows->count()} unit of measurement sa PSIS:"
            ),
            $rows->map(fn (UnitOfMeasurement $u) => "{$u->label()} — {$u->inventory_items_count} item(s)")
        );
    }

    protected function answerSuppliers(User $user): string
    {
        if (! $user->hasAnyRole(['Administrator', 'Supply Personnel'])) {
            return $this->inLang(
                'Supplier records are for Supply and Admin. Ask them, or open the sidebar if it is in your menu.',
                'Ang supplier ay para sa Supply at Admin.',
                'Ang supplier para sa Supply ug Admin.'
            );
        }

        $rows = Supplier::query()->orderBy('name')->get();
        if ($rows->isEmpty()) {
            return $this->inLang(
                'No suppliers are recorded.',
                'Walang supplier sa record.',
                'Walay supplier sa record.'
            );
        }

        return $this->linedAnswer(
            $this->inLang(
                "There are {$rows->count()} supplier(s) in PSIS:",
                "May {$rows->count()} supplier sa PSIS:",
                "Naa'y {$rows->count()} supplier sa PSIS:"
            ),
            $rows->map(fn (Supplier $s) => $s->name.($s->is_active ? '' : ' (inactive)'))
        );
    }

    protected function answerUserCounts(User $user): string
    {
        if (! $user->hasAnyRole(['Administrator', 'Supply Personnel', 'Accounting', 'Admission'])) {
            return $this->inLang(
                'User counts are for staff accounts (Admin, Supply, Accounting, Admission).',
                'Ang bilang ng users ay para sa staff.',
                'Ang ihap sa users para sa staff.'
            );
        }

        $roles = ['Administrator', 'Admission', 'Accounting', 'Supply Personnel', 'Faculty', 'Student'];
        $lines = [];
        $activeTotal = 0;
        $allTotal = 0;
        foreach ($roles as $role) {
            $all = User::role($role)->count();
            $active = User::role($role)->where('is_active', true)->count();
            $activeTotal += $active;
            $allTotal += $all;
            $lines[] = "{$role} — {$active} active of {$all}";
        }

        return $this->linedAnswer(
            $this->inLang(
                "Live user counts: {$activeTotal} active of {$allTotal} accounts.",
                "Live na bilang ng users: {$activeTotal} active sa {$allTotal} accounts.",
                "Live nga ihap sa users: {$activeTotal} active sa {$allTotal} accounts."
            ),
            $lines
        );
    }

    protected function answerInventoryList(User $user): string
    {
        $items = $this->inventoryCatalog($user);

        if ($items->isEmpty()) {
            return $this->inLang(
                'No inventory items to list.',
                'Walang item na mailista sa inventory.',
                'Walay item nga mailista sa inventory.'
            );
        }

        $count = $items->count();
        $limit = 30;
        $shown = $items->take($limit);
        $more = $count > $limit
            ? $this->inLang(
                " Showing {$limit} of {$count}. Type an item name for its available count, or ask How many items? for totals by type.",
                " Pinapakita ang {$limit} sa {$count}. I-type ang pangalan ng item para sa available count, o itanong ang How many items? para sa kabuuan.",
                " Gipakita ang {$limit} sa {$count}. I-type ang ngalan sa item para sa available count, o pangutana How many items? para sa kinatibuk-an."
            )
            : '';

        if ($user->hasRole('Student')) {
            return $this->linedAnswer(
                $this->inLang(
                    "Uniform Shop items you can see ({$count}):",
                    "Mga Uniform Shop item na makikita mo ({$count}):",
                    "Mga Uniform Shop item nga makita nimo ({$count}):"
                ),
                $shown->map(function (Inventory $item) {
                    $unit = trim($item->unitLabel());
                    $available = $item->availableQuantity();

                    return $unit !== ''
                        ? "{$item->item_name} available {$available} {$unit}"
                        : "{$item->item_name} available {$available}";
                }),
                ltrim($more)
            );
        }

        return $this->linedAnswer(
            $this->inLang(
                "All inventory items and available count ({$count}):",
                "Lahat ng item at available count ({$count}):",
                "Tanan nga item ug available count ({$count}):"
            ),
            $shown->map(function (Inventory $item) {
                $unit = trim($item->unitLabel());
                $available = $item->availableQuantity();

                return $unit !== ''
                    ? "{$item->item_name} available {$available} {$unit}"
                    : "{$item->item_name} available {$available}";
            }),
            ltrim($more)
        );
    }

    protected function answerForecasts(): string
    {
        $memory = $this->monthlyDemandMemory();
        $month = $memory['month_label'];
        $faculty = $memory['faculty'][0] ?? null;
        $student = $memory['student'][0] ?? null;
        $restock = $memory['restock'];
        $forecasts = $this->inventoryForecasts(8, 14);

        $sections = [
            $this->linedAnswer(
                $this->inLang("I am tracking {$month}.", "Sine-track ko ang {$month}.", "Gitrack nako ang {$month}."),
                [
                    $this->inLang(
                        'Faculty most requested: '.($faculty ? "{$faculty['item']} ({$faculty['faculty_qty']})" : 'none'),
                        'Pinaka-hinihingi ng faculty: '.($faculty ? "{$faculty['item']} ({$faculty['faculty_qty']})" : 'wala'),
                        'Labing gipangayo sa faculty: '.($faculty ? "{$faculty['item']} ({$faculty['faculty_qty']})" : 'wala')
                    ),
                    $this->inLang(
                        'Student most purchased: '.($student ? "{$student['item']} ({$student['student_qty']})" : 'none'),
                        'Pinakamaraming bili ng student: '.($student ? "{$student['item']} ({$student['student_qty']})" : 'wala'),
                        'Labing gipalit sa student: '.($student ? "{$student['item']} ({$student['student_qty']})" : 'wala')
                    ),
                ]
            ),
        ];

        if ($restock === []) {
            $sections[] = $this->inLang(
                'No demanded items need restock this month based on current available stock.',
                'Walang demanded item na kailangan i-restock ngayong buwan base sa available stock.',
                'Walay demanded item nga kinahanglan i-restock karong bulan base sa available stock.'
            );
        } else {
            $sections[] = $this->linedAnswer(
                $this->inLang('Need restock this month:', 'Kailangan i-restock ngayong buwan:', 'Kinahanglan i-restock karong bulan:'),
                collect($restock)->map(
                    fn (array $row) => "{$row['item']} — demand {$row['demand']}, available {$row['available']}, reorder {$row['recommended_reorder']} {$row['unit']}"
                )
            );
        }

        if ($forecasts !== []) {
            $sections[] = $this->linedAnswer(
                $this->inLang('Usage forecast:', 'Forecast ng gamit:', 'Forecast sa gamit:'),
                collect($forecasts)->map(
                    fn (array $f) => "{$f['message']} Suggested reorder: {$f['recommended_reorder']} {$f['unit']}"
                )
            );
        }

        return implode("\n\n", $sections);
    }

    protected function answerRestockThisMonth(): string
    {
        $memory = $this->monthlyDemandMemory();
        $month = $memory['month_label'];
        $restock = $memory['restock'];

        if ($restock === []) {
            return $this->inLang(
                "I remember {$month}: no most-requested or most-purchased item needs restock right now. Available stock still covers this month's demand.",
                "Naaalala ko ang {$month}: walang pinaka-hinihingi o pinakamaraming bili na kailangan i-restock ngayon. Sapat pa ang available stock.",
                "Nahinumdom ko sa {$month}: walay labing gipangayo o gipalit nga kinahanglan i-restock karon. Igo pa ang available stock."
            );
        }

        return $this->linedAnswer(
            $this->inLang(
                "I remember {$month} demand. Predicted restocks:",
                "Naaalala ko ang demand ngayong {$month}. Predicted restock:",
                "Nahinumdom ko sa demand karong {$month}. Predicted restock:"
            ),
            collect($restock)->map(
                fn (array $row) => "{$row['item']} — demand {$row['demand']} (faculty {$row['faculty_qty']}, student {$row['student_qty']}), available {$row['available']}, suggest restock {$row['recommended_reorder']} {$row['unit']}"
            )
        );
    }

    /**
     * @param  'all'|'faculty'|'student'  $channel
     */
    protected function answerMostRequested(string $channel = 'all'): string
    {
        $this->monthlyDemandMemory();
        $month = Carbon::now()->format('F Y');
        $rows = $this->mostRequestedItemsThisMonth(8, null, $channel);

        if ($rows === []) {
            return match ($channel) {
                'faculty' => $this->inLang(
                    "No faculty requests this month ({$month}). Cancelled and rejected are not counted.",
                    "Walang faculty request ngayong buwan ({$month}). Hindi kasama ang cancelled at rejected.",
                    "Walay faculty request karong bulan ({$month}). Wala gilakip ang cancelled ug rejected."
                ),
                'student' => $this->inLang(
                    "No student purchases this month ({$month}). Cancelled and rejected are not counted.",
                    "Walang student purchase ngayong buwan ({$month}). Hindi kasama ang cancelled at rejected.",
                    "Walay student purchase karong bulan ({$month}). Wala gilakip ang cancelled ug rejected."
                ),
                default => $this->inLang(
                    "No requested items this month ({$month}). Cancelled and rejected orders are not counted.",
                    "Walang na-request na item ngayong buwan ({$month}). Hindi kasama ang cancelled at rejected.",
                    "Walay na-request nga item karong bulan ({$month}). Wala gilakip ang cancelled ug rejected."
                ),
            };
        }

        if ($channel === 'faculty') {
            $top = $rows[0]['item'];

            return $this->linedAnswer(
                $this->inLang(
                    "I remember faculty most requested this month ({$month}): {$top}. Ranking:",
                    "Naaalala ko ang pinaka-hinihingi ng faculty ngayong buwan ({$month}): {$top}. Ranking:",
                    "Nahinumdom ko sa labing gipangayo sa faculty karong bulan ({$month}): {$top}. Ranking:"
                ),
                collect($rows)->map(fn (array $row) => "{$row['item']} — {$row['faculty_qty']}")
            );
        }

        if ($channel === 'student') {
            $top = $rows[0]['item'];

            return $this->linedAnswer(
                $this->inLang(
                    "I remember student most purchased this month ({$month}): {$top}. Ranking:",
                    "Naaalala ko ang pinakamaraming bili ng student ngayong buwan ({$month}): {$top}. Ranking:",
                    "Nahinumdom ko sa labing gipalit sa student karong bulan ({$month}): {$top}. Ranking:"
                ),
                collect($rows)->map(fn (array $row) => "{$row['item']} — {$row['student_qty']}")
            );
        }

        $top = $rows[0]['item'];

        return $this->linedAnswer(
            $this->inLang(
                "Most requested this month ({$month}): {$top}. Ranking:",
                "Pinaka-hinihingi ngayong buwan ({$month}): {$top}. Ranking:",
                "Labing gipangayo karong bulan ({$month}): {$top}. Ranking:"
            ),
            collect($rows)->map(
                fn (array $row) => "{$row['item']} — {$row['total']} (faculty {$row['faculty_qty']}, student {$row['student_qty']})"
            ),
            $this->inLang('Open Reports for the graph.', 'Tingnan ang Reports para sa graph.', 'Tan-awa ang Reports para sa graph.')
        );
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

        return 'Your recent requests:'."\n".$this->bulletList(
            $requests->map(
                fn (SupplyRequest $r) => "{$r->request_number} (".str_replace('_', ' ', $r->status).')'
            )
        )."\nOpen My Requests for full details.";
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

        return $this->linedAnswer(
            'Your recent purchases:',
            $purchases->map(
                fn (PurchaseRequest $p) => "{$p->purchase_number} — ₱".number_format((float) $p->total_amount, 2)
                    .' ('.str_replace('_', ' ', $p->status).')'
            ),
            'Open My Purchases to view slips and upload receipts.'
        );
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

        $attention = $low->isEmpty()
            ? 'None are low/out of stock.'
            : $this->linedAnswer(
                'Attention:',
                $low->pluck('item_name')
            );

        return $this->linedAnswer(
            "{$categoryName}:",
            $items->take(10)->map(
                fn (Inventory $i) => "{$i->item_name} (avail {$i->availableQuantity()})"
            ),
            $attention
        );
    }

    /**
     * @param  array<int, array{role?: string, text?: string}>  $history
     */
    protected function resolveFollowUpQuestion(User $user, string $question, array $history): string
    {
        if (! $this->isFollowUpQuestion($question)) {
            return $question;
        }

        $item = $this->itemFromHistory($user, $history);
        if (! $item) {
            return $question;
        }

        return trim($question.' '.$item->item_name.' '.$item->item_code);
    }

    protected function isFollowUpQuestion(string $question): bool
    {
        $q = $this->normalizeQuestion($question);

        return $this->matches($q, [
            'that item', 'this item', 'that one', 'the first one', 'same item',
            'ilan yun', 'iyon', 'yung item', 'kana', 'ana nga', 'ang item na to',
            'why is it', 'ngano na', 'bakit yun',
        ]);
    }

    /**
     * @param  array<int, array{role?: string, text?: string}>  $history
     */
    protected function itemFromHistory(User $user, array $history): ?Inventory
    {
        $blob = collect($history)
            ->reverse()
            ->map(fn (array $row) => (string) ($row['text'] ?? ''))
            ->implode(' ');

        if (trim($blob) === '') {
            return null;
        }

        return $this->findInventoryByQuestion($user, $blob, skipCountOrList: false);
    }

    protected function extractDocumentNumber(string $question): ?string
    {
        if (preg_match('/\b((?:REQ|PUR)[- ][A-Z0-9\-]{2,})\b/i', $question, $match)) {
            return strtoupper(str_replace(' ', '', $match[1]));
        }

        return null;
    }

    protected function findInventoryByQuestion(User $user, string $question, bool $skipCountOrList = true): ?Inventory
    {
        return $this->findInventoryMatches($user, $question, $skipCountOrList)->first();
    }

    /**
     * @return Collection<int, Inventory>
     */
    protected function findInventoryMatches(User $user, string $question, bool $skipCountOrList = true): Collection
    {
        $q = $this->normalizeQuestion($question);
        if ($q === '') {
            return collect();
        }

        if ($skipCountOrList && ($this->isInventoryCountQuestion($q) || $this->isInventoryListQuestion($q) || $this->matches($q, ['list all', 'how many item', 'how many items', 'ilan ang item', 'pila ka item']))) {
            return collect();
        }

        $catalog = $this->inventoryCatalog($user);
        $scored = collect();

        foreach ($catalog as $item) {
            $score = $this->itemLookupScore($q, $item);
            if ($score >= 10) {
                $scored->push(['item' => $item, 'score' => $score]);
            }
        }

        if ($scored->isEmpty()) {
            return collect();
        }

        $codeHits = $scored->filter(fn (array $row) => $row['score'] >= 20);
        if ($codeHits->isNotEmpty()) {
            return $codeHits
                ->map(fn (array $row) => $row['item'])
                ->unique('id')
                ->values();
        }

        $named = $scored->filter(fn (array $row) => $row['score'] >= 15);
        if ($named->isNotEmpty()) {
            $names = $named->map(fn (array $row) => $this->normalizeQuestion((string) $row['item']->item_name))->unique()->values();
        } else {
            $bestName = $this->normalizeQuestion((string) $scored->sortByDesc('score')->first()['item']->item_name);
            $firstToken = explode(' ', $bestName)[0] ?? $bestName;
            $names = collect(
                strlen($firstToken) >= 4
                && ! $this->isItemLookupStopword($firstToken)
                && $this->questionHasPhrase($q, $firstToken)
                    ? [$firstToken]
                    : [$bestName]
            );
        }

        return $catalog
            ->filter(function (Inventory $item) use ($names) {
                $name = $this->normalizeQuestion((string) $item->item_name);
                foreach ($names as $needle) {
                    if ($needle === '' || strlen($needle) < 4) {
                        continue;
                    }
                    if ($name === $needle || str_starts_with($name, $needle.' ') || str_starts_with($name, $needle.'-')) {
                        return true;
                    }
                }

                return false;
            })
            ->sortBy(fn (Inventory $item) => [$item->item_name, $item->item_code])
            ->values();
    }

    protected function itemLookupScore(string $q, Inventory $item): int
    {
        $code = $this->normalizeQuestion((string) $item->item_code);
        $name = $this->normalizeQuestion((string) $item->item_name);
        $score = 0;

        if ($code !== '' && $this->questionHasPhrase($q, $code)) {
            $score += 20;
        }

        if (strlen($name) >= 3 && $this->questionHasPhrase($q, $name)) {
            $score += 15;

            return $score;
        }

        $tokens = array_values(array_filter(
            explode(' ', $name),
            fn (string $token) => strlen($token) >= 4 && ! $this->isItemLookupStopword($token)
        ));

        if ($tokens === []) {
            return $score;
        }

        $hits = 0;
        foreach ($tokens as $token) {
            if ($this->questionHasPhrase($q, $token)) {
                $hits++;
            }
        }

        if ($hits === count($tokens)) {
            $score += 12;
        } elseif ($hits >= 2) {
            $score += 7;
        } elseif ($hits === 1 && $this->questionHasPhrase($q, $tokens[0])) {
            $score += 11;
        }

        return $score;
    }

    protected function questionHasPhrase(string $q, string $phrase): bool
    {
        $phrase = trim($phrase);
        if ($phrase === '') {
            return false;
        }

        return (bool) preg_match('/(?:^|[^a-z0-9])'.preg_quote($phrase, '/').'(?:$|[^a-z0-9])/i', $q);
    }

    protected function isItemLookupStopword(string $token): bool
    {
        return in_array($token, [
            'item', 'items', 'stock', 'supply', 'supplies', 'office', 'unit', 'units',
            'available', 'type', 'types', 'size', 'pack', 'box', 'set', 'case', 'ream',
            'paper', 'tape', 'wire', 'ink', 'bag', 'black', 'white', 'blue', 'red',
            'green', 'yellow', 'small', 'large', 'plastic', 'steel', 'wood', 'refill',
        ], true);
    }

    /**
     * Category names in a sentence are not enough — require a stock/supplies cue.
     */
    protected function matchCategoryStockKeyword(string $q): ?string
    {
        if (! preg_match('/\b(office|opisina|classroom|laboratory|laboratoryo|computer|kompyuter|cleaning|panlinis|pantry|maintenance|furniture|muwebles|uniform|uniporme)\b/', $q, $m)) {
            return null;
        }

        $asksAboutStock = $this->matches($q, [
            'supplies', 'supply', 'items', 'item', 'stock', 'inventory', 'available',
            'gamit', 'klase', 'kategory', 'category', 'mga item',
        ]);

        if (! $asksAboutStock) {
            return null;
        }

        return match ($m[1]) {
            'opisina' => 'office',
            'laboratoryo' => 'laboratory',
            'kompyuter' => 'computer',
            'panlinis' => 'cleaning',
            'muwebles' => 'furniture',
            'uniporme', 'uniform' => 'uniform',
            default => $m[1],
        };
    }

    /**
     * @param  Collection<int, Inventory>  $items
     */
    protected function answerMatchedItems(User $user, Collection $items, bool $why): string
    {
        if ($items->count() === 1) {
            $item = $items->first();

            return $why ? $this->answerWhyItem($user, $item) : $this->answerItemLookup($user, $item);
        }

        return $this->answerItemLookupMany($user, $items, $why);
    }

    protected function lastSupplierName(Inventory $item): ?string
    {
        $txn = Transaction::query()
            ->with('supplier')
            ->where('inventory_id', $item->id)
            ->whereIn('type', ['stock_in', 'purchase_delivery'])
            ->whereNotNull('supplier_id')
            ->latest('id')
            ->first();

        return $txn?->supplier?->name;
    }

    protected function demandRowForItem(Inventory $item, ?Carbon $month = null): ?array
    {
        foreach ($this->mostRequestedItemsThisMonth(20, $month) as $row) {
            if ((int) $row['inventory_id'] === (int) $item->id) {
                return $row;
            }
        }

        return null;
    }

    protected function answerItemLookup(User $user, Inventory $item): string
    {
        $available = $item->availableQuantity();
        $onHand = (int) $item->quantity;
        $reserved = (int) $item->reserved_quantity;
        $min = (int) $item->minimum_stock;
        $unit = trim($item->unitLabel()) ?: 'unit';
        $demand = $this->demandRowForItem($item);
        $faculty = (int) ($demand['faculty_qty'] ?? 0);
        $student = (int) ($demand['student_qty'] ?? 0);
        $total = (int) ($demand['total'] ?? 0);
        $month = Carbon::now()->format('F Y');
        $supplier = $user->hasRole('Student') ? null : $this->lastSupplierName($item);

        $stock = $this->inLang(
            "{$item->item_name} ({$item->item_code}): on hand {$onHand}, reserved {$reserved}, available {$available} {$unit}, minimum {$min}.",
            "{$item->item_name} ({$item->item_code}): on hand {$onHand}, reserved {$reserved}, available {$available} {$unit}, minimum {$min}.",
            "{$item->item_name} ({$item->item_code}): on hand {$onHand}, reserved {$reserved}, available {$available} {$unit}, minimum {$min}."
        );

        $demandLine = $total > 0
            ? $this->inLang(
                " This month ({$month}): faculty {$faculty}, student {$student}, total {$total}.",
                " Ngayong buwan ({$month}): faculty {$faculty}, student {$student}, kabuuan {$total}.",
                " Karong bulan ({$month}): faculty {$faculty}, student {$student}, total {$total}."
            )
            : $this->inLang(
                " No faculty or student demand this month ({$month}).",
                " Walang faculty o student demand ngayong buwan ({$month}).",
                " Walay faculty o student demand karong bulan ({$month})."
            );

        $restock = '';
        if ($available <= 0 || $item->isLowStock() || $total > $available) {
            $need = max($total - $available, max(0, ($min * 2) - $available), 5);
            $restock = $this->inLang(
                " Suggest restock {$need} {$unit}.",
                " I-restock ng {$need} {$unit}.",
                " I-restock og {$need} {$unit}."
            );
        }

        $supplierLine = $supplier
            ? $this->inLang(
                " Last stock-in supplier: {$supplier}.",
                " Huling supplier sa stock-in: {$supplier}.",
                " Last supplier sa stock-in: {$supplier}."
            )
            : '';

        return $stock.$demandLine.$restock.$supplierLine;
    }

    /**
     * @param  Collection<int, Inventory>  $items
     */
    protected function answerItemLookupMany(User $user, Collection $items, bool $why = false): string
    {
        $limit = 20;
        $count = $items->count();
        $shown = $items->take($limit);
        $more = $count > $limit
            ? $this->inLang(
                "Showing {$limit} of {$count}. Type an item code for one row.",
                "Pinapakita ang {$limit} sa {$count}. I-type ang item code para sa isang row.",
                "Gipakita ang {$limit} sa {$count}. I-type ang item code para sa usa ka row."
            )
            : $this->inLang(
                'Type an item code for full details on one row.',
                'I-type ang item code para sa detalye ng isang row.',
                'I-type ang item code para sa detalye sa usa ka row.'
            );

        return $this->linedAnswer(
            $this->inLang(
                "Found {$count} inventory items matching that name:",
                "May {$count} item sa inventory na tumugma sa pangalan:",
                "Naa'y {$count} item sa inventory nga nahiuyon sa ngalan:"
            ),
            $shown->map(function (Inventory $item) use ($why) {
                $unit = trim($item->unitLabel()) ?: 'unit';
                $available = $item->availableQuantity();
                $line = "{$item->item_name} ({$item->item_code}): on hand {$item->quantity}, reserved {$item->reserved_quantity}, available {$available} {$unit}";
                if ($why) {
                    if ($available <= 0) {
                        $line .= $this->inLang(' — out of stock', ' — ubos na', ' — ubos na');
                    } elseif ($item->isLowStock()) {
                        $line .= $this->inLang(" — low (min {$item->minimum_stock})", " — low (min {$item->minimum_stock})", " — low (min {$item->minimum_stock})");
                    }
                }

                return $line;
            }),
            $more
        );
    }

    protected function answerWhyItem(User $user, Inventory $item): string
    {
        $lookup = $this->answerItemLookup($user, $item);
        $available = $item->availableQuantity();
        $demand = $this->demandRowForItem($item);
        $total = (int) ($demand['total'] ?? 0);

        $reason = match (true) {
            $available <= 0 => $this->inLang(
                ' It is out of stock.',
                ' Ubos na ang stock.',
                ' Ubos na ang stock.'
            ),
            $total > $available => $this->inLang(
                " Demand this month ({$total}) is higher than available ({$available}).",
                " Mas mataas ang demand ngayong buwan ({$total}) kaysa available ({$available}).",
                " Mas taas ang demand karong bulan ({$total}) kaysa available ({$available})."
            ),
            $item->isLowStock() => $this->inLang(
                " Available {$available} is at or below the minimum of {$item->minimum_stock}.",
                " Ang available {$available} ay nasa o mas mababa sa minimum na {$item->minimum_stock}.",
                " Ang available {$available} naa sa o ubos sa minimum nga {$item->minimum_stock}."
            ),
            default => $this->inLang(
                ' Current stock still covers this month’s recorded demand.',
                ' Sapat pa ang stock para sa demand ngayong buwan.',
                ' Igo pa ang stock para sa demand karong bulan.'
            ),
        };

        return $lookup.$reason;
    }

    protected function answerWhyLowStock(User $user): string
    {
        $low = $user->hasRole('Student')
            ? Inventory::forStudentShop($user)->get()->filter(fn (Inventory $i) => $i->isLowStock() || $i->isOutOfStock())->first()
            : $this->lowStockItems()->first();

        if (! $low) {
            return $this->inLang(
                'No items are currently low or out of stock to explain.',
                'Walang low o out-of-stock item na maipaliwanag.',
                'Walay low o out-of-stock item nga ipasabot.'
            );
        }

        return $this->answerWhyItem($user, $low);
    }

    protected function answerDocumentLookup(User $user, string $number): string
    {
        if (str_starts_with($number, 'REQ')) {
            $request = SupplyRequest::query()->with('user')->where('request_number', $number)->first();
            if (! $request) {
                return $this->inLang(
                    "I do not have request {$number}.",
                    "Wala akong request {$number}.",
                    "Wala koy request {$number}."
                );
            }

            if ($user->hasRole('Faculty') && (int) $request->user_id !== (int) $user->id) {
                return $this->inLang(
                    'You can only check your own supply requests.',
                    'Sarili mong request lang ang pwede mong tingnan.',
                    'Imong kaugalingong request ra ang imong tan-awon.'
                );
            }

            if ($user->hasRole('Student')) {
                return $this->inLang(
                    'Faculty request numbers are not for student accounts. Ask about your PUR- purchase instead.',
                    'Ang REQ ay para sa faculty. Tanongin ang iyong PUR- purchase.',
                    'Ang REQ para sa faculty. Pangutana ang imong PUR- purchase.'
                );
            }

            $status = str_replace('_', ' ', $request->status);
            $next = $this->facultyRequestNextStep($request->status);

            return $this->inLang(
                "{$request->request_number} is {$status}. {$next}",
                "{$request->request_number} ay {$status}. {$next}",
                "{$request->request_number} kay {$status}. {$next}"
            );
        }

        $purchase = PurchaseRequest::query()->with('user')->where('purchase_number', $number)->first();
        if (! $purchase) {
            return $this->inLang(
                "I do not have purchase {$number}.",
                "Wala akong purchase {$number}.",
                "Wala koy purchase {$number}."
            );
        }

        if ($user->hasRole('Student') && (int) $purchase->user_id !== (int) $user->id) {
            return $this->inLang(
                'You can only check your own purchases.',
                'Sarili mong purchase lang ang pwede mong tingnan.',
                'Imong kaugalingong purchase ra ang imong tan-awon.'
            );
        }

        if ($user->hasRole('Faculty')) {
            return $this->inLang(
                'Student purchase numbers are not for faculty accounts. Ask about your REQ- request instead.',
                'Ang PUR ay para sa student. Tanongin ang iyong REQ- request.',
                'Ang PUR para sa student. Pangutana ang imong REQ- request.'
            );
        }

        $status = str_replace('_', ' ', $purchase->status);
        $next = $this->studentPurchaseNextStep($purchase->status);

        return $this->inLang(
            "{$purchase->purchase_number} is {$status}. {$next}",
            "{$purchase->purchase_number} ay {$status}. {$next}",
            "{$purchase->purchase_number} kay {$status}. {$next}"
        );
    }

    protected function facultyRequestNextStep(string $status): string
    {
        return match ($status) {
            'pending', 'accounting_review' => 'Next: Accounting review.',
            'admin_review' => 'Next: Admission or Admin approval.',
            'approved', 'reserved' => 'Next: claim at Supply when released.',
            'released' => 'Already released.',
            'cancelled' => 'This request was cancelled.',
            'rejected' => 'This request was rejected.',
            default => 'Open My Requests for details.',
        };
    }

    protected function studentPurchaseNextStep(string $status): string
    {
        return match ($status) {
            'pending', 'payment_submitted' => 'Next: pay at Accounting and upload the receipt if needed.',
            'payment_verified' => 'Next: claim at Supply.',
            'released' => 'Already released.',
            'cancelled' => 'This purchase was cancelled.',
            'rejected' => 'This purchase was rejected.',
            default => 'Open My Purchases for details.',
        };
    }

    protected function answerNextAction(User $user): string
    {
        if ($user->hasRole('Faculty')) {
            $open = SupplyRequest::query()
                ->where('user_id', $user->id)
                ->whereNotIn('status', ['released', 'cancelled', 'rejected'])
                ->latest('id')
                ->first();
            $dept = $user->department;
            $budget = $dept ? $this->facultyBudget->snapshot($dept) : null;
            $budgetLine = $budget
                ? ' Budget remaining ₱'.number_format($budget['remaining'], 2).' of ₱'.number_format($budget['limit'], 2).' for '.$budget['period_label'].'.'
                : ' Ask Admin or Supply to assign your department for the faculty budget.';

            if (! $open) {
                return $this->inLang(
                    'You have no open request. Open New Request if you need supplies.'.$budgetLine,
                    'Wala kang open request. Pumunta sa New Request kung kailangan mo ng supplies.'.$budgetLine,
                    'Wala kay open request. Adto sa New Request kung kinahanglan nimo og supplies.'.$budgetLine
                );
            }

            return $this->inLang(
                "Next for you: {$open->request_number} is ".str_replace('_', ' ', $open->status).'. '.$this->facultyRequestNextStep($open->status).$budgetLine,
                "Susunod: {$open->request_number} ay ".str_replace('_', ' ', $open->status).'. '.$this->facultyRequestNextStep($open->status).$budgetLine,
                "Sunod: {$open->request_number} kay ".str_replace('_', ' ', $open->status).'. '.$this->facultyRequestNextStep($open->status).$budgetLine
            );
        }

        if ($user->hasRole('Student')) {
            $open = PurchaseRequest::query()
                ->where('user_id', $user->id)
                ->whereNotIn('status', ['released', 'cancelled', 'rejected'])
                ->latest('id')
                ->first();

            if (! $open) {
                return $this->inLang(
                    'You have no open purchase. Open Uniform Shop to buy department or shared items.',
                    'Wala kang open purchase. Pumunta sa Uniform Shop.',
                    'Wala kay open purchase. Adto sa Uniform Shop.'
                );
            }

            return $this->inLang(
                "Next for you: {$open->purchase_number} is ".str_replace('_', ' ', $open->status).'. '.$this->studentPurchaseNextStep($open->status),
                "Susunod: {$open->purchase_number} ay ".str_replace('_', ' ', $open->status).'. '.$this->studentPurchaseNextStep($open->status),
                "Sunod: {$open->purchase_number} kay ".str_replace('_', ' ', $open->status).'. '.$this->studentPurchaseNextStep($open->status)
            );
        }

        if ($user->hasRole('Accounting')) {
            $pay = PurchaseRequest::query()->where('status', 'payment_submitted')->orderBy('id')->first();
            $payCount = PurchaseRequest::query()->where('status', 'payment_submitted')->count();
            $reqCount = SupplyRequest::query()->whereIn('status', ['pending', 'accounting_review'])->count();
            $oldest = $pay ? " Oldest payment: {$pay->purchase_number} ₱".number_format((float) $pay->total_amount, 2).'.' : '';

            return "Next for Accounting: {$payCount} student payment(s) to verify, {$reqCount} faculty request(s) to review.{$oldest} Open Verify Payments or Review Requests.";
        }

        if ($user->hasAnyRole(['Admission', 'Administrator'])) {
            $review = SupplyRequest::query()->where('status', 'admin_review')->orderBy('id')->first();
            $count = SupplyRequest::query()->where('status', 'admin_review')->count();
            $oldest = $review ? " Oldest: {$review->request_number}." : '';

            return "Next for approval: {$count} faculty request(s) in admin review.{$oldest} Open Approve Requests.";
        }

        if ($user->hasRole('Supply Personnel')) {
            $faculty = SupplyRequest::query()->whereIn('status', ['approved', 'reserved'])->count();
            $students = PurchaseRequest::query()->where('status', 'payment_verified')->count();
            $oldestFaculty = SupplyRequest::query()->whereIn('status', ['approved', 'reserved'])->orderBy('id')->first();
            $oldestStudent = PurchaseRequest::query()->where('status', 'payment_verified')->orderBy('id')->first();
            $hint = $oldestFaculty?->request_number ?: $oldestStudent?->purchase_number;
            $hintLine = $hint ? " Start with {$hint}." : '';

            return "Next for Supply: release {$faculty} faculty request(s) and {$students} student purchase(s).{$hintLine} Open Release Items or Student Purchases.";
        }

        return $this->helpForRole($user);
    }

    protected function answerCompareMonths(): string
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = $thisMonth->copy()->subMonth();
        $now = $this->monthlySummaryReport($thisMonth);
        $prev = $this->monthlySummaryReport($lastMonth);
        $nowTop = $now['top_overall']['item'] ?? 'none';
        $nowQty = $now['top_overall']['total'] ?? 0;
        $prevTop = $prev['top_overall']['item'] ?? 'none';
        $prevQty = $prev['top_overall']['total'] ?? 0;
        $reqDelta = $now['counts']['faculty_requests'] - $prev['counts']['faculty_requests'];
        $buyDelta = $now['counts']['student_purchases'] - $prev['counts']['student_purchases'];

        return $this->inLang(
            "{$now['month_label']}: {$now['counts']['faculty_requests']} faculty request(s), {$now['counts']['student_purchases']} student purchase(s), most requested {$nowTop} ({$nowQty}). "
            ."{$prev['month_label']}: {$prev['counts']['faculty_requests']} faculty request(s), {$prev['counts']['student_purchases']} student purchase(s), most requested {$prevTop} ({$prevQty}). "
            .'Change vs last month: faculty requests '.($reqDelta >= 0 ? '+'.$reqDelta : $reqDelta).', student purchases '.($buyDelta >= 0 ? '+'.$buyDelta : $buyDelta).'.',
            "{$now['month_label']}: {$now['counts']['faculty_requests']} faculty request(s), {$now['counts']['student_purchases']} student purchase(s), pinaka-hinihingi {$nowTop} ({$nowQty}). "
            ."{$prev['month_label']}: {$prev['counts']['faculty_requests']} / {$prev['counts']['student_purchases']}, pinaka-hinihingi {$prevTop} ({$prevQty}).",
            "{$now['month_label']}: {$now['counts']['faculty_requests']} faculty request(s), {$now['counts']['student_purchases']} student purchase(s), labing gipangayo {$nowTop} ({$nowQty}). "
            ."{$prev['month_label']}: {$prev['counts']['faculty_requests']} / {$prev['counts']['student_purchases']}, labing gipangayo {$prevTop} ({$prevQty})."
        );
    }

    protected function answerSemesterRestock(): string
    {
        $forecast = $this->currentSemesterTrendForecast();
        $upcoming = implode(', ', $forecast['remaining_labels']);

        if ($forecast['complete'] || $forecast['items'] === []) {
            return $this->inLang(
                $forecast['summary'],
                $forecast['summary'],
                $forecast['summary']
            );
        }

        $top = $forecast['items'][0]['item'];

        return $this->linedAnswer(
            $this->inLang(
                "{$forecast['label']} still has {$upcoming}. I forecast {$top} as a likely trend item from last year the same months and this semester's pace.",
                "Ang {$forecast['label']} ay may {$upcoming} pa. Forecast: {$top} ang posibleng mag-trend.",
                "Ang {$forecast['label']} naay {$upcoming} pa. Forecast: {$top} ang posible nga mag-trend."
            ),
            collect($forecast['items'])->take(6)->map(function (array $row) {
                $restock = ((int) $row['recommended_reorder'] > 0)
                    ? "suggest restock {$row['recommended_reorder']} {$row['unit']}"
                    : 'available stock covers the predicted remaining months';

                return "{$row['item']} — predicted {$row['predicted_remaining']} for remaining months ({$row['basis']}), so far {$row['so_far']}, available {$row['available']}, {$restock}";
            })
        );
    }

    protected function answerBudgetCheck(User $user, string $question): string
    {
        if (! $user->hasAnyRole(['Faculty', 'Administrator'])) {
            return $this->inLang(
                'Faculty budget checks are for Faculty accounts.',
                'Ang faculty budget ay para sa Faculty.',
                'Ang faculty budget para sa Faculty.'
            );
        }

        $dept = $user->department;
        if (! $dept && $user->hasRole('Faculty')) {
            return 'Your account has no department. Ask Admin or Supply to assign one before submitting a request.';
        }

        if (! $dept) {
            return 'Pick a faculty account with a department to check that budget.';
        }

        $snapshot = $this->facultyBudget->snapshot($dept);
        $remaining = number_format($snapshot['remaining'], 2);
        $limit = number_format($snapshot['limit'], 2);
        $base = $this->inLang(
            "{$dept->name} faculty budget for {$snapshot['period_label']}: ₱{$remaining} remaining of ₱{$limit}.",
            "Faculty budget ng {$dept->name} para sa {$snapshot['period_label']}: ₱{$remaining} natitira sa ₱{$limit}.",
            "Faculty budget sa {$dept->name} para sa {$snapshot['period_label']}: ₱{$remaining} nabilin sa ₱{$limit}."
        );

        if (! preg_match('/(\d+)\s+(.+)/u', $this->normalizeQuestion($question), $match)) {
            return $base.' Open New Request to submit items within that remaining amount.';
        }

        $qty = (int) $match[1];
        $item = $this->findInventoryByQuestion($user, $match[2]);
        if (! $item || $qty < 1) {
            return $base;
        }

        $cost = round($qty * (float) $item->unit_price, 2);
        $ok = $cost <= $snapshot['remaining'] + 0.009;
        $costLabel = number_format($cost, 2);

        return $ok
            ? $base." {$qty} × {$item->item_name} is ₱{$costLabel} and fits the remaining budget."
            : $base." {$qty} × {$item->item_name} is ₱{$costLabel} and exceeds the remaining budget.";
    }

    /**
     * @return array<int, array<string, int>>
     */
    protected function demandByItemAndMonth(Carbon $start, Carbon $end): array
    {
        $skip = ['cancelled', 'rejected'];
        $map = [];

        $faculty = RequestItem::query()
            ->whereHas('supplyRequest', fn ($q) => $q
                ->whereBetween('created_at', [$start, $end])
                ->whereNotIn('status', $skip))
            ->with(['supplyRequest:id,created_at'])
            ->get(['id', 'request_id', 'inventory_id', 'quantity_requested']);

        foreach ($faculty as $line) {
            $ym = $line->supplyRequest?->created_at?->format('Y-m');
            if (! $ym) {
                continue;
            }
            $id = (int) $line->inventory_id;
            $map[$id][$ym] = ($map[$id][$ym] ?? 0) + (int) $line->quantity_requested;
        }

        $student = PurchaseRequestItem::query()
            ->whereHas('purchaseRequest', fn ($q) => $q
                ->whereBetween('created_at', [$start, $end])
                ->whereNotIn('status', $skip))
            ->with(['purchaseRequest:id,created_at'])
            ->get(['id', 'purchase_request_id', 'inventory_id', 'quantity']);

        foreach ($student as $line) {
            $ym = $line->purchaseRequest?->created_at?->format('Y-m');
            if (! $ym) {
                continue;
            }
            $id = (int) $line->inventory_id;
            $map[$id][$ym] = ($map[$id][$ym] ?? 0) + (int) $line->quantity;
        }

        return $map;
    }

    /**
     * @return list<string>
     */
    protected function trendMonthColors(int $count): array
    {
        $palette = [
            '#0B3C91', '#2563EB', '#38BDF8', '#F4B400', '#D97706', '#92400E',
            '#0F766E', '#14B8A6', '#7C3AED', '#C084FC', '#BE185D', '#FB7185',
        ];
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $palette[$i % count($palette)];
        }

        return $colors;
    }

    /**
     * @return array{summary: string, direction: 'up'|'down'|'steady'|'none'}
     */
    protected function trendPredictionCopy(
        string $periodLabel,
        int $currentTotal,
        int $priorTotal,
        int $compareTotal,
        int $elapsedMonths,
        int $remainingMonths,
        int $totalMonths,
        bool $complete,
        string $priorYears,
    ): array {
        if ($currentTotal <= 0 && $compareTotal <= 0) {
            return [
                'summary' => "No request or purchase data in {$periodLabel} yet, so there is no trend to predict.",
                'direction' => 'none',
            ];
        }

        $vs = null;
        $direction = 'none';
        if ($priorTotal >= 50) {
            $pct = (int) round((($compareTotal - $priorTotal) / $priorTotal) * 100);
            if ($pct >= 8) {
                $direction = 'up';
                $vs = 'up '.$pct.'% vs last year';
            } elseif ($pct <= -8) {
                $direction = 'down';
                $vs = 'down '.abs($pct).'% vs last year';
            } else {
                $direction = 'steady';
                $vs = 'about the same as last year';
            }
        }

        if ($complete) {
            $summary = "{$periodLabel} totaled ".number_format($currentTotal)
                .' (faculty requests + student purchases).';
            if ($vs) {
                $summary .= ' Same period last year ('.$priorYears.') was '.number_format($priorTotal).' ('.$vs.').';
            }

            return ['summary' => $summary, 'direction' => $direction];
        }

        $summary = "{$periodLabel}: {$elapsedMonths} of {$totalMonths} months so far ("
            .number_format($currentTotal).' issued).';
        if ($remainingMonths > 0 && $priorTotal >= 50) {
            $summary .= ' Using last year’s remaining months, this period is likely to finish near '
                .number_format($compareTotal).($vs ? ' ('.$vs.')' : '').'.';
        } elseif ($elapsedMonths > 0) {
            $summary .= ' At the current monthly pace, this period is likely to finish near '
                .number_format($compareTotal).'.';
        }

        return ['summary' => $summary, 'direction' => $direction];
    }
}
