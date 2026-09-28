<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Services\AiInsightService;
use App\Services\FacultyBudgetService;
use App\Services\SuppliesIssuanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request, AiInsightService $ai, FacultyBudgetService $budget): View
    {
        $month = $this->selectedReportMonth($request->query('month'));
        $ranking = $this->resolvedMostRequestedRange($request, $budget);
        $trendSemester = $budget->periodFromKey((string) $request->query('trend_semester'))
            ?? $budget->period();

        $summaryReport = $ai->monthlySummaryReport($month);

        return view('reports.index', [
            'summaryReport' => $summaryReport,
            'lowStock' => $ai->lowStockItems(),
            'mostRequested' => $ai->mostRequestedItemsInRange($ranking['from'], $ranking['to'], 8),
            'mostRequestedPeriod' => $ranking['label'],
            'selectedPeriod' => $ranking['period'],
            'selectedMonth' => $month->format('Y-m'),
            'selectedSemester' => $ranking['semester'],
            'selectedYear' => $ranking['year'],
            'selectedTrendSemester' => (string) ($trendSemester['key'] ?? ''),
            'monthOptions' => $this->reportMonthOptions(),
            'semesterOptions' => $budget->recentSemesters(8),
            'yearOptions' => $this->reportYearOptions(),
            'demandTrend' => $ai->itemDemandTrend(
                $trendSemester['starts_at']->copy(),
                $trendSemester['ends_at']->copy(),
                $trendSemester['period_label'],
                8
            ),
            'semesterForecast' => $ai->semesterTrendForecast(
                $trendSemester['starts_at']->copy(),
                $trendSemester['ends_at']->copy(),
                $trendSemester['period_label'],
                8
            ),
        ]);
    }

    public function monthlySummaryPdf(Request $request, AiInsightService $ai)
    {
        $month = $this->selectedReportMonth($request->query('month'));
        $report = $ai->monthlySummaryReport($month);
        $pdf = Pdf::loadView('reports.pdf.monthly-summary', compact('report'));

        return $pdf->download('monthly-summary-'.$report['month'].'.pdf');
    }

    public function monthlySummaryExcel(Request $request, AiInsightService $ai)
    {
        $month = $this->selectedReportMonth($request->query('month'));
        $report = $ai->monthlySummaryReport($month);

        return Excel::download(new class($report) implements \Maatwebsite\Excel\Concerns\FromCollection {
            public function __construct(private array $report) {}

            public function collection()
            {
                $rows = collect([
                    ['Monthly Summary — PECIT PSIS', $this->report['month_label']],
                    ['Generated', now()->toDateTimeString()],
                    [],
                    ['Section', 'Item', 'Faculty', 'Student', 'Demand / Total', 'Available', 'Suggest restock'],
                    ['Overview', 'Faculty requests', $this->report['counts']['faculty_requests'], '', '', '', ''],
                    ['Overview', 'Student purchases', '', $this->report['counts']['student_purchases'], '', '', ''],
                    ['Overview', 'Low stock', '', '', $this->report['counts']['low_stock'], '', ''],
                    ['Overview', 'Out of stock', '', '', $this->report['counts']['out_of_stock'], '', ''],
                    ['Overview', 'Most requested overall', $this->report['top_overall']['item'] ?? 'None', $this->report['top_overall']['faculty_qty'] ?? '', $this->report['top_overall']['student_qty'] ?? '', $this->report['top_overall']['total'] ?? '', ''],
                ]);

                foreach ($this->report['faculty'] as $row) {
                    $rows->push(['Most requested (faculty)', $row['item'], $row['faculty_qty'], '', $row['faculty_qty'], '', '']);
                }
                if ($this->report['faculty'] === []) {
                    $rows->push(['Most requested (faculty)', 'None', '', '', '', '', '']);
                }

                foreach ($this->report['student'] as $row) {
                    $rows->push(['Most purchased (students)', $row['item'], '', $row['student_qty'], $row['student_qty'], '', '']);
                }
                if ($this->report['student'] === []) {
                    $rows->push(['Most purchased (students)', 'None', '', '', '', '', '']);
                }

                foreach ($this->report['restock'] as $row) {
                    $rows->push([
                        'Need restock this month',
                        $row['item'],
                        $row['faculty_qty'],
                        $row['student_qty'],
                        $row['demand'],
                        $row['available'],
                        $row['recommended_reorder'].' '.$row['unit'],
                    ]);
                }
                if ($this->report['restock'] === []) {
                    $rows->push(['Need restock this month', 'None', '', '', '', '', '']);
                }

                foreach ($this->report['low_stock_items'] as $row) {
                    $rows->push(['Current low stock', $row['item'], '', '', '', $row['available'], 'min '.$row['minimum']]);
                }
                if ($this->report['low_stock_items'] === []) {
                    $rows->push(['Current low stock', 'None', '', '', '', '', '']);
                }

                return $rows;
            }
        }, 'monthly-summary-'.$report['month'].'.xlsx');
    }

    protected function selectedReportMonth(mixed $value): Carbon
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                $parsed = Carbon::createFromFormat('Y-m', $value)->startOfMonth();
                $newest = now()->endOfMonth();
                $oldest = now()->subYears(5)->startOfMonth();

                if ($parsed->gte($oldest) && $parsed->lte($newest)) {
                    return $parsed;
                }
            } catch (\Throwable) {
            }
        }

        return now()->startOfMonth();
    }

    /**
     * @return array{period: string, from: Carbon, to: Carbon, label: string, semester: string, year: string}
     */
    protected function resolvedMostRequestedRange(Request $request, FacultyBudgetService $budget): array
    {
        $period = (string) $request->query('period', 'month');
        if (! in_array($period, ['month', 'semester', 'year'], true)) {
            $period = 'month';
        }

        $month = $this->selectedReportMonth($request->query('month'));
        $year = is_string($request->query('year')) && preg_match('/^\d{4}$/', (string) $request->query('year'))
            ? (int) $request->query('year')
            : (int) now()->year;
        $newestYear = (int) now()->year;
        $oldestYear = $newestYear - 5;
        if ($year < $oldestYear || $year > $newestYear) {
            $year = $newestYear;
        }

        $currentSemester = $budget->period();
        $semesterPeriod = $budget->periodFromKey((string) $request->query('semester')) ?? $currentSemester;

        if ($period === 'semester') {
            return [
                'period' => 'semester',
                'from' => $semesterPeriod['starts_at']->copy(),
                'to' => $semesterPeriod['ends_at']->copy(),
                'label' => $semesterPeriod['period_label'],
                'semester' => (string) ($semesterPeriod['key'] ?? ''),
                'year' => (string) $year,
            ];
        }

        if ($period === 'year') {
            return [
                'period' => 'year',
                'from' => Carbon::create($year, 1, 1)->startOfDay(),
                'to' => Carbon::create($year, 12, 31)->endOfDay(),
                'label' => (string) $year,
                'semester' => (string) ($semesterPeriod['key'] ?? ''),
                'year' => (string) $year,
            ];
        }

        return [
            'period' => 'month',
            'from' => $month->copy()->startOfMonth(),
            'to' => $month->copy()->endOfMonth(),
            'label' => $month->format('F Y'),
            'semester' => (string) ($semesterPeriod['key'] ?? ''),
            'year' => (string) $year,
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    protected function reportMonthOptions(): array
    {
        $options = [];
        $cursor = now()->startOfMonth();
        $oldest = now()->subMonths(17)->startOfMonth();

        while ($cursor->gte($oldest)) {
            $options[] = [
                'value' => $cursor->format('Y-m'),
                'label' => $cursor->format('F Y'),
            ];
            $cursor = $cursor->copy()->subMonth();
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    protected function reportYearOptions(): array
    {
        $years = [];
        for ($year = (int) now()->year; $year >= (int) now()->year - 8; $year--) {
            $years[] = (string) $year;
        }

        return $years;
    }

    public function lowStockPdf(AiInsightService $ai)
    {
        $items = $ai->lowStockItems();
        $pdf = Pdf::loadView('reports.pdf.low-stock', compact('items'));

        return $pdf->download('low-stock-report.pdf');
    }

    public function outOfStockPdf()
    {
        $items = Inventory::with('category')->get()->filter(fn (Inventory $i) => $i->isOutOfStock());
        $pdf = Pdf::loadView('reports.pdf.out-of-stock', compact('items'));

        return $pdf->download('out-of-stock-report.pdf');
    }

    public function inventoryValuationPdf()
    {
        $items = Inventory::with('category')->get();
        $total = $items->sum(fn (Inventory $i) => $i->quantity * $i->unit_price);
        $pdf = Pdf::loadView('reports.pdf.inventory-valuation', compact('items', 'total'));

        return $pdf->download('inventory-valuation.pdf');
    }

    public function facultyRequests(Request $request)
    {
        $requests = SupplyRequest::with(['user', 'items'])
            ->where('type', 'faculty')
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->get();

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.faculty-requests', compact('requests'));

            return $pdf->download('faculty-request-report.pdf');
        }

        return view('reports.faculty-requests', compact('requests'));
    }

    public function studentPurchases(Request $request)
    {
        $purchases = PurchaseRequest::with(['user', 'items'])
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->get();

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.student-purchases', compact('purchases'));

            return $pdf->download('student-purchase-report.pdf');
        }

        return view('reports.student-purchases', compact('purchases'));
    }

    public function dailyInventory()
    {
        $items = Inventory::with('category')->orderBy('item_name')->get();
        $pdf = Pdf::loadView('reports.pdf.daily-inventory', [
            'items' => $items,
            'date' => now()->toFormattedDateString(),
        ]);

        return $pdf->download('daily-inventory-'.now()->format('Y-m-d').'.pdf');
    }

    public function monthlyInventory()
    {
        $start = now()->startOfMonth();
        $items = Inventory::with('category')->orderBy('item_name')->get();
        $monthlyTransactions = Transaction::where('created_at', '>=', $start)->count();
        $pdf = Pdf::loadView('reports.pdf.monthly-inventory', [
            'items' => $items,
            'month' => now()->format('F Y'),
            'monthlyTransactions' => $monthlyTransactions,
        ]);

        return $pdf->download('monthly-inventory-'.now()->format('Y-m').'.pdf');
    }

    public function auditTrailPdf(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->limit(500)
            ->get();

        $pdf = Pdf::loadView('reports.pdf.audit-trail', compact('logs'));

        return $pdf->download('audit-trail-'.now()->format('Y-m-d').'.pdf');
    }

    public function transactionsExcel()
    {
        $rows = Transaction::with(['inventory', 'performer'])->latest()->limit(500)->get();

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection {
            public function __construct(private $rows) {}

            public function collection()
            {
                return $this->rows->map(fn ($t) => [
                    'Transaction' => $t->transaction_number,
                    'Item' => $t->inventory?->item_name,
                    'Type' => $t->typeLabel(),
                    'In' => $t->quantity_in,
                    'Out' => $t->quantity_out,
                    'Balance' => $t->runningBalance(),
                    'Quantity' => $t->quantity,
                    'Before' => $t->quantity_before,
                    'After' => $t->quantity_after,
                    'By' => $t->performer?->name,
                    'Date' => $t->created_at?->toDateTimeString(),
                ]);
            }
        }, 'transactions.xlsx');
    }

    public function suppliesIssuance(Request $request, SuppliesIssuanceService $issuance, FacultyBudgetService $budget): View
    {
        return view('reports.supplies-issuance', $this->suppliesIssuanceViewData($request, $issuance, $budget));
    }

    public function suppliesIssuancePdf(Request $request, SuppliesIssuanceService $issuance)
    {
        set_time_limit(120);
        $report = $issuance->report($request);
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', (string) $report['range_label']) ?: now()->format('Y-m-d');

        return response($issuance->pdf($report), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="supplies-issuance-'.$slug.'.pdf"',
        ]);
    }

    public function suppliesIssuanceExcel(Request $request, SuppliesIssuanceService $issuance)
    {
        $report = $issuance->report($request);
        $rows = collect([
            ['Date', 'Articles/Items', 'QTY', 'UNIT', 'UNIT PRICE', 'TOTAL AMOUNT', 'SEMESTER', 'DEPARTMENT', 'Source', 'Reference', 'Recipient'],
        ]);

        foreach (['faculty', 'student'] as $group) {
            foreach ($report[$group] as $row) {
                $rows->push([
                    optional($row['date'])->format('m/d/Y'),
                    $row['item'],
                    $row['qty'],
                    $row['unit'],
                    $row['unit_price'],
                    $row['total_amount'],
                    $row['semester'],
                    $row['department'],
                    $row['source'],
                    $row['reference'],
                    $row['recipient'],
                ]);
            }
        }

        if ($report['faculty']->isEmpty() && $report['student']->isEmpty()) {
            $rows->push(['', 'No released items in this period.', '', '', '', '', '', '', '', '', '']);
        }

        return Excel::download(new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection {
            public function __construct(private $rows) {}

            public function collection()
            {
                return $this->rows;
            }
        }, 'supplies-issuance-'.now()->format('Y-m-d').'.xlsx');
    }

    /**
     * @return array<string, mixed>
     */
    protected function suppliesIssuanceViewData(Request $request, SuppliesIssuanceService $issuance, FacultyBudgetService $budget): array
    {
        $report = $issuance->report($request);

        return [
            'report' => $report,
            'facultyPage' => $this->paginateIssuanceRows($report['faculty'], $request, 'faculty_page'),
            'studentPage' => $this->paginateIssuanceRows($report['student'], $request, 'student_page'),
            'departments' => Department::query()->orderBy('name')->get(),
            'semesterOptions' => $budget->recentSemesters(8),
            'monthOptions' => $this->reportMonthOptions(),
            'yearOptions' => $this->reportYearOptions(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    protected function paginateIssuanceRows(Collection $rows, Request $request, string $pageName): LengthAwarePaginator
    {
        $perPage = 100;
        $page = max(1, (int) $request->query($pageName, 1));

        return (new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        ))->appends($request->except($pageName));
    }
}
