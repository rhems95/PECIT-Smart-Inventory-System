<?php

namespace App\Console\Commands;

use App\Services\PurchaseRequestService;
use Illuminate\Console\Command;

class PurgeStudentPurchasesCommand extends Command
{
    protected $signature = 'psis:purge-student-purchases
        {--force : Run without confirmation}';

    protected $description = 'Delete all student purchases, payments, and purchase history. Keeps student users and Uniform Shop items.';

    public function handle(PurchaseRequestService $purchases): int
    {
        $this->warn('This deletes every student purchase, payment, receipt, and related stock/audit history.');
        $this->line('Kept: student users, staff users, Uniform Shop catalog, faculty requests.');

        if (! $this->option('force') && ! $this->confirm('Continue?', false)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $stats = $purchases->purgeAllHistory();

        $this->info('Deleted purchases: '.$stats['purchases']);
        $this->info('Student accounts kept: '.$stats['students']);

        return self::SUCCESS;
    }
}
