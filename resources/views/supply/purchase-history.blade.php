@extends('layouts.psis')
@section('title', 'Purchase History')
@section('page-title', 'Purchase History')

@section('content')
<div class="space-y-6">
    <p class="text-sm text-slate-500">
        Three lists: <strong>Uniform Shop</strong> (students), <strong>department releases</strong> (faculty requests), and <strong>supplier deliveries</strong> (Stock In).
        Mark whether the <strong>correct</strong> or <strong>wrong</strong> item was given.
    </p>

    <form method="GET" action="{{ route('supply.purchase-history') }}" class="psis-card p-4 flex flex-wrap gap-2 items-end">
        <div class="flex-1 min-w-[12rem]">
            <label class="psis-label">Search</label>
            <input name="q" value="{{ request('q') }}" class="psis-input" placeholder="Department, faculty, student, item, request #, PO, or DR">
        </div>
        <div>
            <label class="psis-label">Kind</label>
            <select name="kind" class="psis-input">
                <option value="all" @selected(($kind ?? 'all') === 'all')>All</option>
                <option value="shop" @selected(($kind ?? '') === 'shop')>Student shop</option>
                <option value="department" @selected(($kind ?? '') === 'department')>Department releases</option>
                <option value="supplier" @selected(($kind ?? '') === 'supplier')>Supplier deliveries</option>
            </select>
        </div>
        <div>
            <label class="psis-label">Check</label>
            <select name="inspection" class="psis-input">
                <option value="">All</option>
                <option value="pending" @selected(request('inspection') === 'pending')>Not checked</option>
                <option value="correct" @selected(request('inspection') === 'correct')>Correct item</option>
                <option value="incorrect" @selected(request('inspection') === 'incorrect')>Wrong item</option>
            </select>
        </div>
        <button class="psis-btn-primary">Filter</button>
        <a href="{{ route('supply.purchase-history') }}" class="psis-btn-outline">Reset</a>
    </form>

    @if ($shopRows)
    <section class="space-y-2">
        <h2 class="font-semibold">Student shop purchases</h2>
        <div class="psis-card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Purchase #</th>
                        <th class="px-4 py-3 font-medium">Item</th>
                        <th class="px-4 py-3 font-medium">Qty</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Bought by</th>
                        <th class="px-4 py-3 font-medium">Check</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shopRows as $line)
                        @php
                            $purchase = $line->purchaseRequest;
                            $student = $purchase?->user;
                            $status = $line->inspection_status?->value ?? 'pending';
                        @endphp
                        <tr class="border-t border-[var(--psis-border)] align-top">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $purchase?->created_at?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $purchase?->purchase_number ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $line->inventory?->item_name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $line->inventory?->item_code }}@if($line->size) · Size {{ $line->size }}@endif</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $line->quantity }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $purchase?->status ?? '—') }}</td>
                            <td class="px-4 py-3">
                                {{ $student?->name ?? '—' }}
                                @if ($student?->employee_id)
                                    <div class="text-xs text-slate-500">{{ $student->employee_id }}</div>
                                @endif
                                @if ($student?->department)
                                    <div class="text-xs text-slate-500">{{ $student->department->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 min-w-[16rem]">
                                <form method="POST" action="{{ route('supply.purchase-history.inspect-shop', $line) }}" class="space-y-2">
                                    @csrf
                                    <select name="inspection_status" class="psis-input text-sm">
                                        <option value="pending" @selected($status === 'pending')>Not checked</option>
                                        <option value="correct" @selected($status === 'correct')>Correct item</option>
                                        <option value="incorrect" @selected($status === 'incorrect')>Wrong item</option>
                                    </select>
                                    <input name="inspection_notes" class="psis-input text-sm" value="{{ old('inspection_notes', $line->inspection_notes) }}" placeholder="If wrong: what was actually given?">
                                    @error('inspection_notes')
                                        <p class="text-red-500 text-xs">{{ $message }}</p>
                                    @enderror
                                    <button class="psis-btn-outline text-sm">Save check</button>
                                    @if ($line->inspector)
                                        <p class="text-xs text-slate-400">{{ $line->inspectionLabel() }} · {{ $line->inspector->name }} · {{ $line->inspected_at?->format('M d, Y') }}</p>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">No student shop purchases yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $shopRows->links() }}</div>
    </section>
    @endif

    @if ($departmentRows)
    <section class="space-y-2">
        <h2 class="font-semibold">Department released items</h2>
        <div class="psis-card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3 font-medium">Released</th>
                        <th class="px-4 py-3 font-medium">Request #</th>
                        <th class="px-4 py-3 font-medium">Item</th>
                        <th class="px-4 py-3 font-medium">Qty</th>
                        <th class="px-4 py-3 font-medium">Department</th>
                        <th class="px-4 py-3 font-medium">Faculty</th>
                        <th class="px-4 py-3 font-medium">Released by</th>
                        <th class="px-4 py-3 font-medium">Check</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departmentRows as $line)
                        @php
                            $req = $line->supplyRequest;
                            $status = $line->inspection_status?->value ?? 'pending';
                        @endphp
                        <tr class="border-t border-[var(--psis-border)] align-top">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $req?->released_at?->format('M d, Y') ?? $req?->updated_at?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $req?->request_number ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $line->inventory?->item_name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $line->inventory?->item_code }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $line->quantity_released }}</td>
                            <td class="px-4 py-3">
                                {{ $req?->department?->name ?? $req?->user?->department?->name ?? '—' }}
                                @if ($req?->department?->code)
                                    <div class="text-xs text-slate-500">{{ $req->department->code }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $req?->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $req?->releaser?->name ?? '—' }}</td>
                            <td class="px-4 py-3 min-w-[16rem]">
                                <form method="POST" action="{{ route('supply.purchase-history.inspect-department', $line) }}" class="space-y-2">
                                    @csrf
                                    <select name="inspection_status" class="psis-input text-sm">
                                        <option value="pending" @selected($status === 'pending')>Not checked</option>
                                        <option value="correct" @selected($status === 'correct')>Correct item</option>
                                        <option value="incorrect" @selected($status === 'incorrect')>Wrong item</option>
                                    </select>
                                    <input name="inspection_notes" class="psis-input text-sm" value="{{ old('inspection_notes', $line->inspection_notes) }}" placeholder="If wrong: what was actually given?">
                                    @error('inspection_notes')
                                        <p class="text-red-500 text-xs">{{ $message }}</p>
                                    @enderror
                                    <button class="psis-btn-outline text-sm">Save check</button>
                                    @if ($line->inspector)
                                        <p class="text-xs text-slate-400">{{ $line->inspectionLabel() }} · {{ $line->inspector->name }} · {{ $line->inspected_at?->format('M d, Y') }}</p>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">No department releases yet. Release faculty requests first.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $departmentRows->links() }}</div>
    </section>
    @endif

    @if ($supplierRows)
    <section class="space-y-2">
        <h2 class="font-semibold">Supplier deliveries (Stock In)</h2>
        <div class="psis-card overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 text-left text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Item recorded</th>
                        <th class="px-4 py-3 font-medium">Qty</th>
                        <th class="px-4 py-3 font-medium">Supplier</th>
                        <th class="px-4 py-3 font-medium">PO / DR</th>
                        <th class="px-4 py-3 font-medium">Bought by</th>
                        <th class="px-4 py-3 font-medium">Recorded by</th>
                        <th class="px-4 py-3 font-medium">Check</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplierRows as $row)
                        @php
                            $status = $row->inspection_status?->value ?? 'pending';
                        @endphp
                        <tr class="border-t border-[var(--psis-border)] align-top">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                {{ ($row->transaction_date ?? $row->created_at)?->format('M d, Y') }}
                                <div class="text-xs">{{ $row->transaction_number }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $row->inventory?->item_name ?? '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $row->inventory?->item_code }}@if($row->size) · Size {{ $row->size }}@endif</div>
                                @if ($row->notes)
                                    <div class="text-xs text-slate-500 mt-1">{{ $row->notes }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $row->quantity_in ?: $row->quantity }}</td>
                            <td class="px-4 py-3">{{ $row->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($row->reference_number)
                                    <div>PO {{ $row->reference_number }}</div>
                                @endif
                                @if ($row->delivery_receipt_number)
                                    <div class="text-slate-500">DR {{ $row->delivery_receipt_number }}</div>
                                @endif
                                @if (! $row->reference_number && ! $row->delivery_receipt_number)
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php $buyerUser = $row->buyer ?? $row->performer; @endphp
                                {{ $row->buyerName() }}
                                @if ($buyerUser?->department)
                                    <div class="text-xs text-slate-500">{{ $buyerUser->department->name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $row->performer?->name ?? '—' }}</td>
                            <td class="px-4 py-3 min-w-[16rem]">
                                <form method="POST" action="{{ route('supply.purchase-history.inspect', $row) }}" class="space-y-2">
                                    @csrf
                                    <select name="inspection_status" class="psis-input text-sm">
                                        <option value="pending" @selected($status === 'pending')>Not checked</option>
                                        <option value="correct" @selected($status === 'correct')>Correct item</option>
                                        <option value="incorrect" @selected($status === 'incorrect')>Wrong item</option>
                                    </select>
                                    <input name="inspection_notes" class="psis-input text-sm" value="{{ old('inspection_notes', $row->inspection_notes) }}" placeholder="If wrong: what was actually given?">
                                    @error('inspection_notes')
                                        <p class="text-red-500 text-xs">{{ $message }}</p>
                                    @enderror
                                    <button class="psis-btn-outline text-sm">Save check</button>
                                    @if ($row->inspector)
                                        <p class="text-xs text-slate-400">{{ $row->inspectionLabel() }} · {{ $row->inspector->name }} · {{ $row->inspected_at?->format('M d, Y') }}</p>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">No supplier deliveries yet. Record them on Stock In first.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $supplierRows->links() }}</div>
    </section>
    @endif
</div>
@endsection
