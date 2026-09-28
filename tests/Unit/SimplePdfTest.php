<?php

namespace Tests\Unit;

use App\Support\SimplePdf;
use Tests\TestCase;

class SimplePdfTest extends TestCase
{
    public function test_large_table_stays_a_valid_pdf(): void
    {
        $pdf = new SimplePdf;
        $pdf->title('Issuance', 'Year 2025');
        $rows = [];
        for ($i = 1; $i <= 2500; $i++) {
            $rows[] = ['01/15/2025', 'Bond Paper '.$i, '2', 'ream', '210.00', '420.00', '1st semester AY 2024-2025', 'CCS'];
        }
        $pdf->table(
            ['Date', 'Item', 'QTY', 'UNIT', 'PRICE', 'TOTAL', 'SEMESTER', 'DEPT'],
            $rows,
            [58, 210, 36, 40, 70, 80, 120, 120]
        );
        $out = $pdf->output();

        $this->assertStringStartsWith('%PDF-1.4', $out);
        $this->assertStringContainsString('%%EOF', $out);
        $this->assertGreaterThan(50_000, strlen($out));
    }
}
