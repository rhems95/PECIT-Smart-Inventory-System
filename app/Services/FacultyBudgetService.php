<?php

namespace App\Services;

use App\Models\Department;
use App\Models\SupplyRequest;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use RuntimeException;

class FacultyBudgetService
{
    public const DEFAULT_LIMIT = 10000.00;

    /**
     * Faculty request statuses that consume department budget.
     *
     * @var list<string>
     */
    public const COUNTING_STATUSES = [
        'pending',
        'accounting_review',
        'admin_review',
        'approved',
        'reserved',
        'released',
    ];

    /**
     * @return array{
     *     semester: int,
     *     label: string,
     *     academic_year: string,
     *     period_label: string,
     *     starts_at: CarbonInterface,
     *     ends_at: CarbonInterface
     * }
     */
    public function period(?\DateTimeInterface $at = null): array
    {
        $at = Carbon::parse($at ?? now());
        $year = (int) $at->year;
        $month = (int) $at->month;

        // Two semesters per academic year (starts June 1):
        // 1st: June 1 – November 30
        // 2nd: December 1 – May 31 (next calendar year)
        if ($month >= 6 && $month <= 11) {
            $starts = Carbon::create($year, 6, 1)->startOfDay();
            $ends = Carbon::create($year, 11, 30)->endOfDay();
            $ay = $year.'-'.($year + 1);
            $semester = 1;
            $label = '1st semester';
        } elseif ($month === 12) {
            $starts = Carbon::create($year, 12, 1)->startOfDay();
            $ends = Carbon::create($year + 1, 5, 31)->endOfDay();
            $ay = $year.'-'.($year + 1);
            $semester = 2;
            $label = '2nd semester';
        } else {
            $starts = Carbon::create($year - 1, 12, 1)->startOfDay();
            $ends = Carbon::create($year, 5, 31)->endOfDay();
            $ay = ($year - 1).'-'.$year;
            $semester = 2;
            $label = '2nd semester';
        }

        return [
            'semester' => $semester,
            'label' => $label,
            'academic_year' => $ay,
            'period_label' => "{$label} AY {$ay}",
            'starts_at' => $starts,
            'ends_at' => $ends,
        ];
    }

    public function limitFor(?Department $department): float
    {
        if ($department && $department->faculty_budget_limit !== null) {
            return round((float) $department->faculty_budget_limit, 2);
        }

        return round((float) config('psis.faculty_department_budget', self::DEFAULT_LIMIT), 2);
    }

    public function usedThisPeriod(?Department $department, ?int $exceptRequestId = null, ?array $period = null): float
    {
        if (! $department) {
            return 0.0;
        }

        $period ??= $this->period();

        $query = SupplyRequest::query()
            ->where('department_id', $department->id)
            ->where('type', 'faculty')
            ->whereIn('status', self::COUNTING_STATUSES)
            ->whereBetween('created_at', [$period['starts_at'], $period['ends_at']]);

        if ($exceptRequestId) {
            $query->whereKeyNot($exceptRequestId);
        }

        return round((float) $query->sum('total_amount'), 2);
    }

    /**
     * @return array{
     *     semester: int,
     *     label: string,
     *     academic_year: string,
     *     period_label: string,
     *     starts_at: CarbonInterface,
     *     ends_at: CarbonInterface,
     *     limit: float,
     *     used: float,
     *     remaining: float,
     *     department: ?Department
     * }
     */
    public function snapshot(?Department $department, ?int $exceptRequestId = null): array
    {
        $period = $this->period();
        $limit = $this->limitFor($department);
        $used = $this->usedThisPeriod($department, $exceptRequestId, $period);

        return $period + [
            'limit' => $limit,
            'used' => $used,
            'remaining' => round(max(0, $limit - $used), 2),
            'department' => $department,
        ];
    }

    public function assertWithinBudget(?Department $department, float $amount, ?int $exceptRequestId = null): void
    {
        if (! $department) {
            throw new RuntimeException('Your account has no department. Ask Admin or Supply to assign one before submitting a request.');
        }

        $snapshot = $this->snapshot($department, $exceptRequestId);
        $projected = round($snapshot['used'] + $amount, 2);

        if ($projected > $snapshot['limit'] + 0.009) {
            $remaining = number_format($snapshot['remaining'], 2);
            $limit = number_format($snapshot['limit'], 2);
            $asked = number_format($amount, 2);

            throw new RuntimeException(
                "This request (₱{$asked}) exceeds the {$department->name} faculty supply budget for {$snapshot['period_label']}. Remaining: ₱{$remaining} of ₱{$limit}."
            );
        }
    }
}
