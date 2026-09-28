<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Department;
use App\Models\Inventory;
use App\Models\InventoryPriceAdjustment;
use App\Models\InventorySizeStock;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PsisNotification;
use App\Models\RequestItem;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Models\UnitOfMeasurement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use RuntimeException;

class SuppliesSpreadsheetImportService
{
    public function defaultPath(): string
    {
        return base_path('docs/.supply data/SUPPLIES DATA.xlsx');
    }

    /**
     * @return array{departments: int, items: int, requests: int, lines: int, kept_shop_items: int}
     */
    public function import(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Spreadsheet not found: {$path}");
        }

        $rows = $this->parseSpreadsheet($path);
        if ($rows === []) {
            throw new RuntimeException('No usable rows found in the spreadsheet.');
        }

        return DB::transaction(function () use ($rows) {
            $keptInventoryIds = Inventory::query()
                ->where(function ($query) {
                    $query->where('student_shop', true)
                        ->orWhereIn('id', PurchaseRequestItem::query()->select('inventory_id'));
                })
                ->pluck('id')
                ->all();
            $keptPurchaseIds = PurchaseRequest::query()->pluck('id')->all();

            $this->purgeOperationalData($keptInventoryIds, $keptPurchaseIds);

            $office = Category::query()->firstOrCreate(
                ['slug' => 'office-supplies'],
                ['name' => 'Office Supplies', 'description' => 'Office supplies for PECIT campuses.', 'is_active' => true],
            );

            $archive = User::query()->firstOrCreate(
                ['email' => 'issuance-archive@pecit.edu.ph'],
                [
                    'name' => 'Supply Issuance Archive',
                    'last_name' => 'Archive',
                    'password' => 'password',
                    'employee_id' => 'ISSUE-ARC',
                    'department_id' => Department::query()->where('code', 'SUPPLY')->value('id'),
                    'is_active' => false,
                    'email_verified_at' => now(),
                ],
            );
            if (! $archive->hasRole('Faculty')) {
                $archive->assignRole('Faculty');
            }
            $faculty = $archive;
            $supply = User::query()->role('Supply Personnel')->orderBy('id')->first();
            if (! $faculty || ! $supply) {
                throw new RuntimeException('Need at least one Faculty user and one Supply Personnel user.');
            }

            $departments = [];
            $items = [];
            foreach ($rows as $row) {
                $departments[$row['department_key']] ??= $this->departmentFor($row['department_raw']);
                $itemKey = $row['item_key'];
                if (! isset($items[$itemKey])) {
                    $items[$itemKey] = [
                        'name' => $row['item_name'],
                        'unit' => $row['unit'],
                        'price' => $row['unit_price'],
                        'uses' => 0,
                    ];
                }
                $items[$itemKey]['uses']++;
                if ($row['unit_price'] > 0) {
                    $items[$itemKey]['price'] = $row['unit_price'];
                    $items[$itemKey]['unit'] = $row['unit'] !== '' ? $row['unit'] : $items[$itemKey]['unit'];
                }
            }

            $inventoryByKey = [];
            $seq = 1;
            foreach ($items as $key => $meta) {
                $code = 'XLS-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                $seq++;
                $unit = $meta['unit'] !== '' ? $meta['unit'] : 'pc';
                $uom = $this->unitOfMeasurement($unit);
                $qty = $this->sampleQuantity($meta['uses']);
                $min = max(5, (int) floor($qty / 5));
                $inventoryByKey[$key] = Inventory::query()->create([
                    'item_code' => $code,
                    'item_name' => $meta['name'],
                    'description' => 'Imported from Supply office issuance log.',
                    'category_id' => $office->id,
                    'unit' => $unit,
                    'unit_of_measurement_id' => $uom?->id,
                    'unit_price' => $meta['price'] > 0 ? $meta['price'] : 0,
                    'quantity' => $qty,
                    'reserved_quantity' => 0,
                    'minimum_stock' => $min,
                    'location' => 'Supply Room',
                    'student_shop' => false,
                    'department_id' => null,
                    'status' => $qty <= $min ? 'low_stock' : 'available',
                ]);
            }

            $grouped = [];
            foreach ($rows as $row) {
                if (! isset($inventoryByKey[$row['item_key']])) {
                    continue;
                }
                $groupKey = $row['date']->toDateString().'|'.$row['department_key'];
                $grouped[$groupKey][] = $row;
            }

            ksort($grouped);
            $requestCount = 0;
            $lineCount = 0;
            $n = 1;

            foreach ($grouped as $groupKey => $groupRows) {
                [$dateString, $deptKey] = explode('|', $groupKey, 2);
                $department = $departments[$deptKey];
                $when = Carbon::parse($dateString)->setTime(10, 0);
                $chunks = array_chunk($groupRows, 40);

                foreach ($chunks as $chunk) {
                    $total = 0.0;
                    $payload = [];
                    foreach ($chunk as $row) {
                        $item = $inventoryByKey[$row['item_key']];
                        $qty = $row['qty'];
                        $price = $row['unit_price'] > 0 ? $row['unit_price'] : (float) $item->unit_price;
                        $sub = round($qty * $price, 2);
                        $total += $sub;
                        $payload[] = [
                            'inventory' => $item,
                            'qty' => $qty,
                            'price' => $price,
                            'sub' => $sub,
                        ];
                    }

                    $request = SupplyRequest::query()->create([
                        'request_number' => 'REQ-XLS-'.str_pad((string) $n, 5, '0', STR_PAD_LEFT),
                        'user_id' => $faculty->id,
                        'department_id' => $department->id,
                        'type' => 'faculty',
                        'status' => 'released',
                        'purpose' => 'Supply office issuance (imported)',
                        'total_amount' => round($total, 2),
                        'approved_by' => $supply->id,
                        'released_by' => $supply->id,
                        'reviewed_at' => $when,
                        'approved_at' => $when,
                        'released_at' => $when,
                    ]);
                    $request->forceFill([
                        'created_at' => $when,
                        'updated_at' => $when,
                    ])->save();
                    $n++;
                    $requestCount++;

                    foreach ($payload as $line) {
                        RequestItem::query()->create([
                            'request_id' => $request->id,
                            'inventory_id' => $line['inventory']->id,
                            'quantity_requested' => $line['qty'],
                            'quantity_approved' => $line['qty'],
                            'quantity_released' => $line['qty'],
                            'unit_price' => $line['price'],
                            'subtotal' => $line['sub'],
                            'created_at' => $when,
                            'updated_at' => $when,
                        ]);
                        $lineCount++;
                    }
                }
            }

            return [
                'departments' => count($departments),
                'items' => count($inventoryByKey),
                'requests' => $requestCount,
                'lines' => $lineCount,
                'kept_shop_items' => count($keptInventoryIds),
            ];
        });
    }

    /**
     * @return list<array{date: Carbon, item_name: string, item_key: string, qty: int, unit: string, unit_price: float, department_raw: string, department_key: string}>
     */
    public function parseSpreadsheet(string $path): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($path);
        $rows = [];

        foreach (['2024', '2025', '2026'] as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (! $sheet) {
                continue;
            }

            $highest = $sheet->getHighestDataRow();
            $lastDate = Carbon::createFromDate((int) $sheetName, 1, 1)->startOfDay();
            $lastWasComplete = false;

            for ($r = 2; $r <= $highest; $r++) {
                $itemName = trim((string) $sheet->getCell([2, $r])->getValue());
                if ($itemName === '') {
                    continue;
                }

                $dateCell = $sheet->getCell([1, $r]);
                $resolved = $this->resolveIssuanceDate(
                    $dateCell->getValue(),
                    (int) $sheetName,
                    $lastDate,
                    $lastWasComplete,
                    $dateCell->getFormattedValue(),
                );
                $lastDate = $resolved['date'];
                $lastWasComplete = $resolved['complete'];

                $qtyCell = $sheet->getCell([3, $r]);
                $qty = $this->resolveIssuanceQuantity(
                    $qtyCell->getValue(),
                    $qtyCell->getFormattedValue(),
                    $sheet->getCell([5, $r])->getValue(),
                    $sheet->getCell([6, $r])->getValue(),
                );
                $unit = $this->canonicalUnit((string) $sheet->getCell([4, $r])->getValue());
                $priceRaw = $sheet->getCell([5, $r])->getValue();
                $price = is_numeric($priceRaw) ? round((float) $priceRaw, 2) : 0.0;
                $deptRaw = trim((string) $sheet->getCell([8, $r])->getValue());
                if ($deptRaw === '') {
                    $deptRaw = 'Supply Office';
                }

                $rows[] = [
                    'date' => $lastDate->copy(),
                    'item_name' => preg_replace('/\s+/', ' ', $itemName) ?: $itemName,
                    'item_key' => $this->itemKey($itemName),
                    'qty' => $qty,
                    'unit' => $unit,
                    'unit_price' => $price,
                    'department_raw' => $deptRaw,
                    'department_key' => $this->departmentKey($deptRaw),
                ];
            }
        }

        $spreadsheet->disconnectWorksheets();

        return $rows;
    }

    /**
     * @param  list<int>  $keptInventoryIds
     * @param  list<int>  $keptPurchaseIds
     */
    protected function purgeOperationalData(array $keptInventoryIds, array $keptPurchaseIds): void
    {
        Schema::disableForeignKeyConstraints();

        RequestItem::query()->delete();
        SupplyRequest::query()->delete();

        $keepTxnIds = [];
        if ($keptPurchaseIds !== []) {
            $keepTxnIds = Transaction::query()
                ->where('reference_type', PurchaseRequest::class)
                ->whereIn('reference_id', $keptPurchaseIds)
                ->pluck('id')
                ->all();
        }

        if ($keepTxnIds !== []) {
            Transaction::query()->whereNotIn('id', $keepTxnIds)->delete();
        } else {
            Transaction::query()->delete();
        }

        $deleteInventory = Inventory::query()
            ->when($keptInventoryIds !== [], fn ($q) => $q->whereNotIn('id', $keptInventoryIds))
            ->when($keptInventoryIds === [], fn ($q) => $q->whereRaw('1=1'))
            ->pluck('id')
            ->all();

        if ($deleteInventory !== []) {
            Transaction::query()->whereIn('inventory_id', $deleteInventory)->delete();
            DB::table('stock_logs')->whereIn('inventory_id', $deleteInventory)->delete();
            InventoryPriceAdjustment::query()->whereIn('inventory_id', $deleteInventory)->delete();
            InventorySizeStock::query()->whereIn('inventory_id', $deleteInventory)->delete();
            Inventory::query()->whereIn('id', $deleteInventory)->delete();
        }

        PsisNotification::query()
            ->where(function ($q) {
                $q->where('type', 'like', 'request_%')
                    ->orWhere('type', 'like', 'supply_request%')
                    ->orWhere('link', 'like', '%/requests%')
                    ->orWhere('link', 'like', '%supply/releases%');
            })
            ->delete();

        Schema::enableForeignKeyConstraints();
    }

    protected function departmentFor(string $raw): Department
    {
        [$code, $name] = $this->mapDepartment($raw);

        return Department::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'is_active' => true,
                'faculty_budget_limit' => 10000,
            ],
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function mapDepartment(string $raw): array
    {
        $key = $this->departmentKey($raw);

        $aliases = [
            'CCS' => ['CCS', 'College of Computer Studies'],
            'CC' => ['CC', 'College of Criminology'],
            'CTHM' => ['CTHM', 'College of Tourism and Hospitality Management'],
            'CTE' => ['CTE', 'College of Teacher Education'],
            'CBA' => ['CBA', 'College of Business Administration'],
            'SHS' => ['SHS', 'Senior High School'],
            'ADMIN' => ['ADMIN', 'Administration'],
            'SUPPLY' => ['SUPPLY', 'Supply Office'],
            'OSAS' => ['OSAS', 'Office of Student Affairs'],
            'REGDMO' => ['REGDMO', 'Registrar / DMO'],
            'TESDA' => ['TESDA', 'TESDA'],
            'TVET' => ['TVET', 'TVET'],
            'ACCT' => ['ACCT', 'Accounting'],
            'FIN' => ['FIN', 'Finance / Accounting'],
            'GENSERV' => ['GENSERV', 'General Services'],
            'CLINIC' => ['CLINIC', 'Clinic'],
            'LIBRARY' => ['LIBRARY', 'Library'],
            'LRC' => ['LRC', 'Learning Resource Center'],
            'NSTP' => ['NSTP', 'NSTP'],
            'GUIDANCE' => ['GUIDANCE', 'Guidance'],
            'HR' => ['HR', 'Human Resources'],
            'RESEARCH' => ['RESEARCH', 'Research'],
            'CANTEEN' => ['CANTEEN', 'Canteen'],
            'AUTO' => ['AUTO', 'Automotive'],
            'SSG' => ['SSG', 'Supreme Student Government'],
            'SPORTS' => ['SPORTS', 'Sports'],
            'CULTURE' => ['CULTURE', 'Cultural'],
            'MARKET' => ['MARKET', 'Marketing'],
            'MEDIA' => ['MEDIA', 'Multimedia'],
            'WATCH' => ['WATCH', 'Watch Guild'],
            'KANTAW' => ['KANTAW', 'Kantaw'],
            'JDVP' => ['JDVP', 'JDVP'],
            'BASICED' => ['BASICED', 'Basic Education'],
            'GENED' => ['GENED', 'General Education'],
            'HOUSEKP' => ['HOUSEKP', 'Housekeeping'],
            'CULINARY' => ['CULINARY', 'Culinary'],
            'BARISTA' => ['BARISTA', 'Barista'],
            'EPAS' => ['EPAS', 'EPAS'],
            'EDTECH' => ['EDTECH', 'EdTech'],
        ];

        if (isset($aliases[$key])) {
            return $aliases[$key];
        }

        $code = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $key) ?: 'DEPT', 0, 10));
        $name = ucwords(strtolower(trim($raw)));

        return [$code, $name !== '' ? $name : $code];
    }

    public function departmentKey(string $raw): string
    {
        $n = strtoupper(trim($raw));
        $n = str_replace(['.', '/', ',', '-'], ' ', $n);
        $n = preg_replace('/\s+/', ' ', $n) ?? $n;

        return match (true) {
            str_contains($n, 'BSIS') || str_contains($n, 'CSS') || str_contains($n, 'COMPUTER') => 'CCS',
            str_contains($n, 'BSCRIM') || str_contains($n, 'CRIMIN') => 'CC',
            str_contains($n, 'CTHM') || str_contains($n, 'BSHM') || str_contains($n, 'TOURISM') => 'CTHM',
            str_contains($n, 'CTE') || str_contains($n, 'BEED') || str_contains($n, 'TEACHER') => 'CTE',
            str_contains($n, 'BSBA') || $n === 'CBA' => 'CBA',
            str_contains($n, 'BASIC ED') || $n === 'SHS' => 'SHS',
            str_contains($n, 'SUPPLY') => 'SUPPLY',
            str_contains($n, 'ADMIN') || str_contains($n, 'BOD') || $n === 'ACAD' || str_contains($n, 'ACADEMIC') || str_contains($n, 'COLLEGE FACULTY') => 'ADMIN',
            str_contains($n, 'OSAS') => 'OSAS',
            str_contains($n, 'REGISTRAR') || str_contains($n, 'DMO') => 'REGDMO',
            str_contains($n, 'TESDA') => 'TESDA',
            str_contains($n, 'TVET') && ! str_contains($n, 'JDVP') => 'TVET',
            str_contains($n, 'ACCOUNTING') && str_contains($n, 'FINANCE') => 'FIN',
            str_contains($n, 'ACCOUNTING') => 'ACCT',
            str_contains($n, 'FINANCE') => 'FIN',
            str_contains($n, 'GENERAL SERV') || str_contains($n, 'GEN SERV') => 'GENSERV',
            str_contains($n, 'CLINIC') => 'CLINIC',
            str_contains($n, 'LIBRARY') && str_contains($n, 'LRC') => 'LRC',
            str_contains($n, 'LIBRARY') => 'LIBRARY',
            $n === 'LRC' || str_starts_with($n, 'LRC ') => 'LRC',
            str_contains($n, 'NSTP') => 'NSTP',
            str_contains($n, 'GUIDANCE') => 'GUIDANCE',
            $n === 'HR' || str_starts_with($n, 'HR ') => 'HR',
            str_contains($n, 'RESEARCH') => 'RESEARCH',
            str_contains($n, 'CANTEEN') => 'CANTEEN',
            str_contains($n, 'AUTOMOTIVE') => 'AUTO',
            str_contains($n, 'SSG') => 'SSG',
            str_contains($n, 'SPORT') => 'SPORTS',
            str_contains($n, 'CULTURAL') => 'CULTURE',
            str_contains($n, 'MARKETING') => 'MARKET',
            str_contains($n, 'MEDIA') => 'MEDIA',
            str_contains($n, 'WATCH') => 'WATCH',
            str_contains($n, 'KANTAW') => 'KANTAW',
            str_contains($n, 'JDVP') => 'JDVP',
            str_contains($n, 'GEN ED') || str_contains($n, 'GENERAL ED') => 'GENED',
            str_contains($n, 'HOUSEKEEP') => 'HOUSEKP',
            str_contains($n, 'CULINARY') || str_contains($n, 'COOKERY') => 'CULINARY',
            str_contains($n, 'BARISTA') => 'BARISTA',
            str_contains($n, 'EPAS') => 'EPAS',
            str_contains($n, 'EDTECH') => 'EDTECH',
            default => preg_replace('/[^A-Z0-9]/', '', $n) ?: 'SUPPLY',
        };
    }

    public function itemKey(string $name): string
    {
        $n = strtolower($name);
        $n = str_replace(['(', ')', '[', ']', '-', '_', '/', '\\'], ' ', $n);
        $n = preg_replace('/\s+/', ' ', $n) ?? $n;

        return trim($n);
    }

    public function canonicalUnit(string $raw): string
    {
        $n = strtolower(trim($raw));
        $n = rtrim($n, '.');
        $n = preg_replace('/\s+/', '', $n) ?? $n;

        return match (true) {
            in_array($n, ['pc', 'pcs', 'piece', 'pieces', 'pcd'], true) => 'pc',
            in_array($n, ['rm', 'rms', 'rim', 'rims', 'ream'], true) => 'ream',
            in_array($n, ['gal', 'gals', 'gallon'], true) => 'gal',
            in_array($n, ['cps', 'cpies', 'copies'], true) => 'copy',
            in_array($n, ['bot', 'bots', 'bottle', 'bottles'], true) => 'bottle',
            in_array($n, ['pck', 'pcks', 'pack', 'packs', 'pac'], true) => 'pack',
            in_array($n, ['litr', 'lit', 'ltr', 'ltrs', 'l', 'liter', 'liters'], true) => 'L',
            in_array($n, ['ml', 'nl'], true) => 'ml',
            in_array($n, ['box'], true) => 'box',
            in_array($n, ['set'], true) => 'set',
            in_array($n, ['roll', 'rolls'], true) => 'roll',
            in_array($n, ['tube'], true) => 'tube',
            in_array($n, ['unit', 'units'], true) => 'unit',
            in_array($n, ['kl', 'kls', 'kis', 'kg'], true) => 'kg',
            in_array($n, ['m', 'mtr', 'mtrs'], true) => 'm',
            $n === '' => 'pc',
            default => $n,
        };
    }

    protected function unitOfMeasurement(string $symbol): ?UnitOfMeasurement
    {
        $names = [
            'pc' => 'Piece',
            'ream' => 'Ream',
            'gal' => 'Gallon',
            'copy' => 'Copy',
            'bottle' => 'Bottle',
            'pack' => 'Pack',
            'L' => 'Liter',
            'ml' => 'Milliliter',
            'box' => 'Box',
            'set' => 'Set',
            'roll' => 'Roll',
            'tube' => 'Tube',
            'unit' => 'Unit',
            'kg' => 'Kilogram',
            'm' => 'Meter',
        ];

        $name = $names[$symbol] ?? ucfirst($symbol);

        return UnitOfMeasurement::query()->firstOrCreate(
            ['symbol' => $symbol],
            ['name' => $name, 'description' => $name],
        );
    }

    protected function sampleQuantity(int $uses): int
    {
        if ($uses >= 200) {
            return 200;
        }
        if ($uses >= 50) {
            return 100;
        }

        return 50;
    }

    /**
     * Excel often turns a typed fraction such as 1/2 into a date serial (46054)
     * while TOTAL AMOUNT still has unit price × 0.5. Prefer the amount, then a
     * visible n/d fraction, never the date serial as a quantity.
     */
    public function resolveIssuanceQuantity(mixed $qtyRaw, mixed $qtyFormatted, mixed $unitPrice, mixed $totalAmount): int
    {
        $qty = $this->quantityFromCell($qtyRaw, $qtyFormatted);
        $price = is_numeric($unitPrice) ? (float) $unitPrice : 0.0;
        $total = is_numeric($totalAmount) ? (float) $totalAmount : null;

        if ($price > 0.0 && $total !== null && $total > 0.0) {
            $implied = $total / $price;
            $matchesLine = abs(($qty * $price) - $total) <= max(0.05 * abs($total), 1.0);
            if ($implied > 0 && ! $matchesLine) {
                $qty = $implied;
            }
        }

        return max(1, (int) round($qty));
    }

    protected function quantityFromCell(mixed $raw, mixed $formatted): float
    {
        $fmt = trim((string) $formatted);
        if (preg_match('/^(\d{1,2})\s*\/\s*(\d{1,2})$/', $fmt, $match)) {
            $denominator = (int) $match[2];
            if ($denominator > 0) {
                $fraction = ((int) $match[1]) / $denominator;
                if (! is_numeric($raw) || abs((float) $raw - $fraction) > 1) {
                    return $fraction;
                }
            }
        }

        if (is_numeric($raw)) {
            return max(0.0, (float) $raw);
        }

        if (is_numeric($fmt)) {
            return max(0.0, (float) $fmt);
        }

        return 1.0;
    }

    /**
     * Blank cells (and day-less month/year marks like "01/ /25") follow the last
     * item above that has a real date, matching the office issuance log.
     *
     * @return array{date: Carbon, complete: bool}
     */
    public function resolveIssuanceDate(mixed $value, int $sheetYear, Carbon $lastDate, bool $lastWasComplete, mixed $formatted = null): array
    {
        $complete = $this->parseCompleteDate($value, $sheetYear, $formatted);
        if ($complete) {
            return ['date' => $complete, 'complete' => true];
        }

        $monthHint = $this->parseMonthYearHint($formatted, $sheetYear)
            ?? $this->parseMonthYearHint($value, $sheetYear);
        if ($monthHint) {
            if ($lastWasComplete && $lastDate->year === $monthHint->year && $lastDate->month === $monthHint->month) {
                return ['date' => $lastDate->copy(), 'complete' => true];
            }

            return ['date' => $monthHint, 'complete' => false];
        }

        return ['date' => $lastDate->copy(), 'complete' => $lastWasComplete];
    }

    protected function parseCompleteDate(mixed $value, int $sheetYear, mixed $formatted = null): ?Carbon
    {
        foreach ([$formatted, $value] as $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            if ($this->parseMonthYearHint($candidate, $sheetYear)) {
                continue;
            }

            if (is_numeric($candidate) && (float) $candidate > 20000) {
                continue;
            }

            $parsed = $this->parseMonthDayYear(trim((string) $candidate), $sheetYear);
            if ($parsed) {
                return $parsed;
            }
        }

        if (is_numeric($value) && (float) $value > 20000) {
            try {
                return $this->clampYear(
                    Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value)),
                    $sheetYear
                );
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Office log dates are month/day (1/14/2025 is January 14). Excel serials on
     * this workbook often used day/month, so 1/8/2025 became August 1.
     */
    protected function parseMonthDayYear(string $text, int $sheetYear): ?Carbon
    {
        $text = preg_replace('/\s+.*$/', '', $text) ?? $text;
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        foreach (['n/j/Y', 'm/d/Y', 'n/j/y', 'm/d/y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat('!'.$format, $text);
                if ($parsed !== false) {
                    return $this->clampYear($parsed, $sheetYear);
                }
            } catch (\Throwable) {
            }
        }

        return null;
    }

    protected function parseMonthYearHint(mixed $value, int $sheetYear): ?Carbon
    {
        $text = trim((string) $value);
        if ($text === '' || ! preg_match('/^(\d{1,2})\/\s*\/(\d{2,4})$/', $text, $m)) {
            return null;
        }

        $year = strlen($m[2]) === 2 ? 2000 + (int) $m[2] : (int) $m[2];

        return $this->clampYear(Carbon::create($year, (int) $m[1], 1), $sheetYear);
    }

    protected function clampYear(Carbon $date, int $sheetYear): Carbon
    {
        $year = (int) $date->year;
        if ($year < 100) {
            $date->year(2000 + $year);
            $year = (int) $date->year;
        }
        if ($year < 2020 || $year > 2026) {
            $date->year($sheetYear);
        }

        return $date->startOfDay();
    }
}
