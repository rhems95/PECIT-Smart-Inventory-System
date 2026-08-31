@extends('layouts.psis')

@section('title', 'My Purchases')
@section('page-title', 'Purchase History')

@section('content')
<div class="psis-card overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                <th class="px-4 py-3 font-medium">Purchase #</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium">Total</th>
                <th class="px-4 py-3 font-medium">Date</th>
                <th class="px-4 py-3 font-medium text-right">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchases as $purchase)
                <tr class="border-t border-[var(--psis-border)] hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                    <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $purchase->purchase_number }}</td>
                    <td class="px-4 py-3 min-w-[11rem]">
                        <x-status-tracker compact :tracker="\App\Support\OrderStatusTracker::forPurchase($purchase)" />
                        <span class="sr-only">{{ str_replace('_', ' ', $purchase->status) }}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">₱{{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $purchase->created_at?->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('purchases.show', $purchase) }}" class="text-pecit-blue dark:text-pecit-gold hover:underline font-medium">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">No purchases yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $purchases->links() }}</div>
@endsection
