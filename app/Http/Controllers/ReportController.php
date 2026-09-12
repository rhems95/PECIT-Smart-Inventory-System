<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Inventory;
use App\Models\PurchaseRequest;
use App\Models\SupplyRequest;
use App\Models\Transaction;
use App\Services\AiInsightService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(AiInsightService $ai): View
    {
        return view('reports.index', [
            'aiSummary' => $ai->monthlySummary(),
            'lowStock' => $ai->lowStockItems(),
        ]);
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
}
