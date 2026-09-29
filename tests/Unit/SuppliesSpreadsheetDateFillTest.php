<?php

namespace Tests\Unit;

use App\Services\SuppliesSpreadsheetImportService;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class SuppliesSpreadsheetDateFillTest extends TestCase
{
    public function test_blank_dates_follow_the_last_dated_item_above(): void
    {
        $path = storage_path('framework/supplies-date-fill-test.xlsx');
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('2025');
        $sheet->fromArray([
            ['Date', 'Item', 'Qty', 'Unit', 'Price', '', '', 'Dept'],
            ['1/10/2025', 'Pen', 1, 'pc', 10, '', '', 'CCS'],
            ['', 'Paper', 2, 'ream', 20, '', '', 'CCS'],
            ['', 'Stapler', 1, 'pc', 50, '', '', 'CCS'],
            ['1/15/2025', 'Water', 1, 'gal', 30, '', '', 'CC'],
            ['', 'Folder', 3, 'pc', 5, '', '', 'CC'],
            ['01/ /25', 'Battery', 1, 'pc', 15, '', '', 'CBA'],
            ['2/ /25', 'Tape', 1, 'pc', 12, '', '', 'CTE'],
            ['', 'Glue', 1, 'pc', 8, '', '', 'CTE'],
        ], null, 'A1');

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $rows = app(SuppliesSpreadsheetImportService::class)->parseSpreadsheet($path);
        } finally {
            @unlink($path);
        }

        $this->assertCount(8, $rows);
        $this->assertSame('2025-01-10', $rows[0]['date']->toDateString());
        $this->assertSame('2025-01-10', $rows[1]['date']->toDateString());
        $this->assertSame('2025-01-10', $rows[2]['date']->toDateString());
        $this->assertSame('2025-01-15', $rows[3]['date']->toDateString());
        $this->assertSame('2025-01-15', $rows[4]['date']->toDateString());
        $this->assertSame('2025-01-15', $rows[5]['date']->toDateString());
        $this->assertSame('2025-02-01', $rows[6]['date']->toDateString());
        $this->assertSame('2025-02-01', $rows[7]['date']->toDateString());
    }

    public function test_resolve_issuance_date_inherits_blank_and_same_month_hint(): void
    {
        $service = app(SuppliesSpreadsheetImportService::class);
        $jan10 = Carbon::create(2025, 1, 10)->startOfDay();

        $blank = $service->resolveIssuanceDate('', 2025, $jan10, true);
        $this->assertSame('2025-01-10', $blank['date']->toDateString());
        $this->assertTrue($blank['complete']);

        $hint = $service->resolveIssuanceDate('01/ /25', 2025, $jan10, true);
        $this->assertSame('2025-01-10', $hint['date']->toDateString());

        $newMonth = $service->resolveIssuanceDate('2/ /25', 2025, $jan10, true);
        $this->assertSame('2025-02-01', $newMonth['date']->toDateString());
        $this->assertFalse($newMonth['complete']);
    }

    public function test_displayed_month_day_wins_over_excel_day_month_serial(): void
    {
        $service = app(SuppliesSpreadsheetImportService::class);
        $fallback = Carbon::create(2025, 1, 1)->startOfDay();

        $rj45 = $service->resolveIssuanceDate(45870, 2025, $fallback, true, '1/8/2025');
        $this->assertSame('2025-01-08', $rj45['date']->toDateString());

        $dec = $service->resolveIssuanceDate(45728, 2025, $fallback, true, '12/03/25');
        $this->assertSame('2025-12-03', $dec['date']->toDateString());

        $text = $service->resolveIssuanceDate('2/13/2025', 2025, $fallback, true, '2/13/2025');
        $this->assertSame('2025-02-13', $text['date']->toDateString());
    }

    public function test_half_quantity_typed_as_excel_date_uses_total_amount(): void
    {
        $service = app(SuppliesSpreadsheetImportService::class);

        $this->assertSame(0.5, $service->resolveIssuanceQuantity(46054, '1/2', 816, 408));
        $this->assertSame(0.5, $service->resolveIssuanceQuantity(46054, '1/2', 150, 75));
        $this->assertSame(12.0, $service->resolveIssuanceQuantity(12, '12', 40, 480));
        $this->assertSame(20000.0, $service->resolveIssuanceQuantity(20000, '20000', 1, 20000));
        $this->assertSame(10.0, $service->resolveIssuanceQuantity(10, '10', 0, null));
        $this->assertSame(3.74, $service->resolveIssuanceQuantity(3.74, '3.74', 68.5, 256.19));
    }

    public function test_parse_spreadsheet_does_not_keep_date_serial_as_qty(): void
    {
        $path = storage_path('framework/supplies-qty-fraction-test.xlsx');
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('2025');
        $sheet->fromArray([
            ['Date', 'Item', 'Qty', 'Unit', 'Price', 'Total', '', 'Dept'],
            ['2/4/2025', 'Cellophane', 46054, 'rm', 816, 408, '', 'Accounting'],
            ['2/4/2025', 'Pen', 12, 'pc', 40, 480, '', 'CCS'],
            ['4/14/2025', 'concreate nail yellow', 46054, 'kl', 150, 75, '', 'General Services'],
            ['6/1/2025', 'Gasoline', 3.74, 'L', 68.5, 256.19, '', 'Supply Office'],
        ], null, 'A1');

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $rows = app(SuppliesSpreadsheetImportService::class)->parseSpreadsheet($path);
        } finally {
            @unlink($path);
        }

        $byName = collect($rows)->keyBy('item_name');
        $this->assertSame(0.5, $byName['Cellophane']['qty']);
        $this->assertSame(12.0, $byName['Pen']['qty']);
        $this->assertSame(0.5, $byName['concreate nail yellow']['qty']);
        $this->assertSame(3.74, $byName['Gasoline']['qty']);
        $this->assertSame('L', $byName['Gasoline']['unit']);
    }

    public function test_canonical_unit_maps_liter_aliases_and_quart(): void
    {
        $service = app(SuppliesSpreadsheetImportService::class);

        $this->assertSame('L', $service->canonicalUnit('litr.'));
        $this->assertSame('L', $service->canonicalUnit('Ltrs'));
        $this->assertSame('L', $service->canonicalUnit('lrts'));
        $this->assertSame('qt', $service->canonicalUnit('qrt.'));
        $this->assertSame('gal', $service->canonicalUnit('gal.'));
        $this->assertSame('ream', $service->canonicalUnit('rm.'));
    }

    public function test_default_path_prefers_updated_workbook_name(): void
    {
        $path = str_replace('\\', '/', app(SuppliesSpreadsheetImportService::class)->defaultPath());

        $this->assertTrue(
            str_ends_with($path, 'SUPPLIES DATA updated.xlsx')
            || str_ends_with($path, 'SUPPLIES DATA.xlsx')
        );
    }
}
