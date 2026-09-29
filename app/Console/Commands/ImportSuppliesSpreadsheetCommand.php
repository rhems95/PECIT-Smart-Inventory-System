<?php

namespace App\Console\Commands;

use App\Services\SuppliesSpreadsheetImportService;
use Illuminate\Console\Command;

class ImportSuppliesSpreadsheetCommand extends Command
{
    protected $signature = 'psis:import-supplies-xlsx
        {--path= : Path to the Supply workbook (default: SUPPLIES DATA updated.xlsx)}
        {--force : Run without confirmation}';

    protected $description = 'Replace office inventory and faculty request history from the Supply spreadsheet. Keeps users, students, and student purchases.';

    public function handle(SuppliesSpreadsheetImportService $import): int
    {
        $path = $this->option('path') ?: $import->defaultPath();
        $this->warn('This deletes faculty requests and non-shop inventory, then imports items/departments/history from:');
        $this->line($path);
        $this->line('Kept: users, students, student purchases, Uniform Shop items.');

        if (! $this->option('force') && ! $this->confirm('Continue?', false)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $stats = $import->import($path);

        $this->info('Imported departments used: '.$stats['departments']);
        $this->info('New inventory items: '.$stats['items']);
        $this->info('Released faculty requests: '.$stats['requests']);
        $this->info('Issuance lines: '.$stats['lines']);
        $this->info('Kept shop items (student purchases): '.$stats['kept_shop_items']);

        return self::SUCCESS;
    }
}
