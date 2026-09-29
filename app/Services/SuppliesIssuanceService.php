<?php

namespace App\Services;

use App\Models\PurchaseRequestItem;
use App\Models\RequestItem;
use App\Support\Qty;
use App\Support\SimplePdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SuppliesIssuanceService
{
    public function __construct(protected FacultyBudgetService $facultyBudget) {}

    /**
     * @return array{
     *     period: string,
     *     source: string,
     *     department_id: ?int,
     *     date: string,
     *     month: string,
     *     year: string,
     *     semester: string,
     *     range_label: string,
     *     from: ?Carbon,
     *     to: ?Carbon,
     *     faculty: Collection<int, array<string, mixed>>,
     *     student: Collection<int, array<string, mixed>>,
     *     departments: Collection<int, array{department: string, faculty_qty: float, student_qty: float, faculty_amount: float, student_amount: float}>,
     *     totals: array{faculty_lines: int, student_lines: int, faculty_amount: float, student_amount: float}
     * }
     */
    public function report(Request $request): array
    {
        $filters = $this->resolvedFilters($request);
        [$from, $to] = [$filters['from'], $filters['to']];
        $departmentId = $filters['department_id'];
        $source = $filters['source'];

        $faculty = $source === 'student'
            ? collect()
            : $this->facultyRows($from, $to, $departmentId);
        $student = $source === 'faculty'
            ? collect()
            : $this->studentRows($from, $to, $departmentId);

        $departments = $this->departmentSummary($faculty, $student);

        return $filters + [
            'faculty' => $faculty,
            'student' => $student,
            'departments' => $departments,
            'totals' => [
                'faculty_lines' => $faculty->count(),
                'student_lines' => $student->count(),
                'faculty_amount' => round((float) $faculty->sum('total_amount'), 2),
                'student_amount' => round((float) $student->sum('total_amount'), 2),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $report
     */
    public function pdf(array $report): string
    {
        $pdf = new SimplePdf;
        $pdf->title(
            'Supplies Issuance Log — PECIT PSIS',
            'Period: '.$report['range_label'].'  |  Generated: '.now()->format('M d, Y g:i A')
        );
        $pdf->paragraph('Released faculty requests and student purchases only. Cancelled and rejected are not included.');
        $pdf->paragraph(
            'Faculty lines: '.number_format((int) $report['totals']['faculty_lines'])
            .' (P'.number_format((float) $report['totals']['faculty_amount'], 2).')'
            .'  ·  Student lines: '.number_format((int) $report['totals']['student_lines'])
            .' (P'.number_format((float) $report['totals']['student_amount'], 2).')'
        );

        $pdf->paragraph('Per department');
        $deptRows = $report['departments']->map(fn (array $row) => [
            $row['department'],
            Qty::format($row['faculty_qty']),
            Qty::format($row['student_qty']),
            number_format((float) $row['faculty_amount'], 2),
            number_format((float) $row['student_amount'], 2),
        ])->all();
        if ($deptRows === []) {
            $deptRows[] = ['No released items in this period.', '', '', '', ''];
        }
        $pdf->table(
            ['Department', 'Faculty qty', 'Student qty', 'Faculty amount', 'Student amount'],
            $deptRows,
            [220, 90, 90, 140, 140]
        );

        $lineHeaders = ['Date', 'Articles/Items', 'QTY', 'UNIT', 'UNIT PRICE', 'TOTAL AMOUNT', 'SEMESTER', 'DEPARTMENT'];
        $lineWidths = [58, 210, 36, 40, 70, 80, 120, 120];

        if (($report['source'] ?? 'all') !== 'student') {
            $pdf->paragraph('Faculty');
            $pdf->table($lineHeaders, $this->pdfLineRows($report['faculty']), $lineWidths);
        }

        if (($report['source'] ?? 'all') !== 'faculty') {
            $pdf->paragraph('Students');
            $pdf->table($lineHeaders, $this->pdfLineRows($report['student']), $lineWidths);
        }

        return $pdf->output();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return list<list<string>>
     */
    protected function pdfLineRows(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [['No released items in this period.', '', '', '', '', '', '', '']];
        }

        return $rows->map(fn (array $row) => [
            optional($row['date'])->format('m/d/Y') ?: '—',
            (string) $row['item'],
            Qty::format($row['qty']),
            (string) $row['unit'],
            number_format((float) $row['unit_price'], 2),
            number_format((float) $row['total_amount'], 2),
            (string) $row['semester'],
            (string) $row['department'],
        ])->all();
    }

    /**
     * @return array{
     *     period: string,
     *     source: string,
     *     department_id: ?int,
     *     date: string,
     *     month: string,
     *     year: string,
     *     semester: string,
     *     range_label: string,
     *     from: ?Carbon,
     *     to: ?Carbon
     * }
     */
    public function resolvedFilters(Request $request): array
    {
        $period = (string) $request->query('period', 'month');
        if (! in_array($period, ['day', 'week', 'month', 'semester', 'year', 'all'], true)) {
            $period = 'month';
        }

        $source = (string) $request->query('source', 'all');
        if (! in_array($source, ['all', 'faculty', 'student'], true)) {
            $source = 'all';
        }

        $departmentId = $request->integer('department_id') ?: null;
        $date = $this->parseDate($request->query('date')) ?? now()->toDateString();
        $month = is_string($request->query('month')) && preg_match('/^\d{4}-\d{2}$/', $request->query('month'))
            ? $request->query('month')
            : now()->format('Y-m');
        $year = is_string($request->query('year')) && preg_match('/^\d{4}$/', $request->query('year'))
            ? $request->query('year')
            : now()->format('Y');

        $currentSemester = $this->facultyBudget->period();
        $semester = (string) $request->query('semester', $currentSemester['key'] ?? '');
        $semesterPeriod = $this->facultyBudget->periodFromKey($semester) ?? $currentSemester;
        $semester = (string) ($semesterPeriod['key'] ?? $semester);

        [$from, $to, $rangeLabel] = match ($period) {
            'day' => $this->boundsForDay($date),
            'week' => $this->boundsForWeek($date),
            'month' => $this->boundsForMonth($month),
            'semester' => [
                $semesterPeriod['starts_at']->copy(),
                $semesterPeriod['ends_at']->copy(),
                $semesterPeriod['period_label'],
            ],
            'year' => $this->boundsForYear($year),
            default => [null, null, 'All released items'],
        };

        return [
            'period' => $period,
            'source' => $source,
            'department_id' => $departmentId,
            'date' => $date,
            'month' => $month,
            'year' => $year,
            'semester' => $semester,
            'range_label' => $rangeLabel,
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function facultyRows(?Carbon $from, ?Carbon $to, ?int $departmentId): Collection
    {
        $items = RequestItem::query()
            ->with(['inventory', 'supplyRequest.department', 'supplyRequest.user.department'])
            ->whereHas('supplyRequest', function ($query) use ($from, $to, $departmentId) {
                $query->where('status', 'released')
                    ->whereNotNull('released_at')
                    ->when($from && $to, fn ($q) => $q->whereBetween('released_at', [$from, $to]))
                    ->when($departmentId, function ($q) use ($departmentId) {
                        $q->where(function ($inner) use ($departmentId) {
                            $inner->where('department_id', $departmentId)
                                ->orWhereHas('user', fn ($user) => $user->where('department_id', $departmentId));
                        });
                    });
            })
            ->get();

        return $items
            ->map(function (RequestItem $line) {
                $request = $line->supplyRequest;
                $date = $request?->released_at;
                $qty = Qty::of($line->quantity_released ?: $line->quantity_approved ?: $line->quantity_requested);
                $price = (float) $line->unit_price;
                $department = $request?->department?->name
                    ?? $request?->user?->department?->name
                    ?? '—';

                return [
                    'date' => $date,
                    'item' => $line->inventory?->item_name ?? 'Item',
                    'qty' => $qty,
                    'unit' => $line->inventory?->unit ?? '',
                    'unit_price' => $price,
                    'total_amount' => round((float) ($line->subtotal ?: $qty * $price), 2),
                    'semester' => $date ? $this->facultyBudget->period($date)['period_label'] : '—',
                    'department' => $department,
                    'source' => 'Faculty',
                    'reference' => $request?->request_number ?? '',
                    'recipient' => $request?->user?->name ?? '—',
                ];
            })
            ->sortBy('date')
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function studentRows(?Carbon $from, ?Carbon $to, ?int $departmentId): Collection
    {
        $items = PurchaseRequestItem::query()
            ->with(['inventory', 'purchaseRequest.user.department'])
            ->whereHas('purchaseRequest', function ($query) use ($from, $to, $departmentId) {
                $query->where('status', 'released')
                    ->whereNotNull('released_at')
                    ->when($from && $to, fn ($q) => $q->whereBetween('released_at', [$from, $to]))
                    ->when($departmentId, fn ($q) => $q->whereHas('user', fn ($user) => $user->where('department_id', $departmentId)));
            })
            ->get();

        return $items
            ->map(function (PurchaseRequestItem $line) {
                $purchase = $line->purchaseRequest;
                $date = $purchase?->released_at;
                $qty = Qty::of($line->quantity);
                $price = (float) $line->unit_price;
                $name = $line->inventory?->item_name ?? 'Item';
                if ($line->size) {
                    $name .= ' ('.$line->size.')';
                }

                return [
                    'date' => $date,
                    'item' => $name,
                    'qty' => $qty,
                    'unit' => $line->inventory?->unit ?? '',
                    'unit_price' => $price,
                    'total_amount' => round((float) ($line->subtotal ?: $qty * $price), 2),
                    'semester' => $date ? $this->facultyBudget->period($date)['period_label'] : '—',
                    'department' => $purchase?->user?->department?->name ?? '—',
                    'source' => 'Student',
                    'reference' => $purchase?->purchase_number ?? '',
                    'recipient' => $purchase?->user?->name ?? '—',
                ];
            })
            ->sortBy('date')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $faculty
     * @param  Collection<int, array<string, mixed>>  $student
     * @return Collection<int, array{department: string, faculty_qty: float, student_qty: float, faculty_amount: float, student_amount: float}>
     */
    protected function departmentSummary(Collection $faculty, Collection $student): Collection
    {
        $map = [];

        foreach ($faculty as $row) {
            $key = $row['department'];
            $map[$key] ??= [
                'department' => $key,
                'faculty_qty' => 0,
                'student_qty' => 0,
                'faculty_amount' => 0.0,
                'student_amount' => 0.0,
            ];
            $map[$key]['faculty_qty'] = Qty::add($map[$key]['faculty_qty'], $row['qty']);
            $map[$key]['faculty_amount'] += (float) $row['total_amount'];
        }

        foreach ($student as $row) {
            $key = $row['department'];
            $map[$key] ??= [
                'department' => $key,
                'faculty_qty' => 0,
                'student_qty' => 0,
                'faculty_amount' => 0.0,
                'student_amount' => 0.0,
            ];
            $map[$key]['student_qty'] = Qty::add($map[$key]['student_qty'], $row['qty']);
            $map[$key]['student_amount'] += (float) $row['total_amount'];
        }

        return collect($map)
            ->map(function (array $row) {
                $row['faculty_amount'] = round($row['faculty_amount'], 2);
                $row['student_amount'] = round($row['student_amount'], 2);

                return $row;
            })
            ->sortBy('department')
            ->values();
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function boundsForDay(string $date): array
    {
        $day = Carbon::parse($date)->startOfDay();

        return [$day, $day->copy()->endOfDay(), $day->toFormattedDateString()];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function boundsForWeek(string $date): array
    {
        $start = Carbon::parse($date)->startOfWeek();
        $end = Carbon::parse($date)->endOfWeek();

        return [$start, $end, $start->format('M d, Y').' – '.$end->format('M d, Y')];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function boundsForMonth(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return [$start, $start->copy()->endOfMonth(), $start->format('F Y')];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function boundsForYear(string $year): array
    {
        $start = Carbon::create((int) $year, 1, 1)->startOfDay();

        return [$start, $start->copy()->endOfYear(), $year];
    }

    protected function parseDate(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
