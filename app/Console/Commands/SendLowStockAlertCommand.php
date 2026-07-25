<?php

namespace App\Console\Commands;

use App\Services\AiInsightService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendLowStockAlertCommand extends Command
{
    protected $signature = 'psis:low-stock-alert';

    protected $description = 'Email/notify Supply Personnel and Administrators about urgent low-stock and depletion risks';

    public function handle(AiInsightService $ai, NotificationService $notifications): int
    {
        $alerts = $ai->urgentStockAlerts(7);

        if (empty($alerts)) {
            $this->info('No urgent stock alerts today.');

            return self::SUCCESS;
        }

        $lines = collect($alerts)->take(12)->map(function (array $row) {
            $days = $row['days_until_depletion'] !== null
                ? "{$row['days_until_depletion']} day(s) left"
                : 'below minimum';

            return "{$row['item']}: avail {$row['available']}, {$days}, reorder {$row['recommended_reorder']} {$row['unit']}";
        })->implode("\n");

        $title = 'Urgent stock alert ('.count($alerts).' item(s))';
        $message = "The following items need attention within 7 days:\n{$lines}";
        $link = route('ai.restock');

        $notifications->notifyRole('Supply Personnel', 'low_stock_alert', $title, $message, $link);
        $notifications->notifyRole('Administrator', 'low_stock_alert', $title, $message, $link);

        $this->info('Low-stock alert sent for '.count($alerts).' item(s).');

        return self::SUCCESS;
    }
}
