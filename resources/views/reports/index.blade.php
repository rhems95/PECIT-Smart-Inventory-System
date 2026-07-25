@extends('layouts.psis')
@section('page-title', 'Reports')
@section('content')
<div class="grid md:grid-cols-2 gap-4">
<div class="psis-card p-5"><h3 class="font-semibold mb-2">AI Summary</h3><p class="text-sm">{{ $aiSummary }}</p></div>
<div class="psis-card p-5 space-y-2"><h3 class="font-semibold">Export</h3>
<a class="psis-btn-outline block text-center" href="{{ route('reports.low-stock.pdf') }}">Low Stock (PDF)</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.out-of-stock.pdf') }}">Out of Stock (PDF)</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.valuation.pdf') }}">Inventory Valuation (PDF)</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.daily-inventory.pdf') }}">Daily Inventory (PDF)</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.monthly-inventory.pdf') }}">Monthly Inventory (PDF)</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.faculty-requests') }}">Faculty Requests</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.student-purchases') }}">Student Purchases</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.audit-trail.pdf') }}">Audit Trail (PDF)</a>
<a class="psis-btn-outline block text-center" href="{{ route('reports.transactions.excel') }}">Transactions (Excel)</a>
</div></div>
<div class="psis-card p-5 mt-4"><h3 class="font-semibold mb-3">Low Stock Items</h3>
<ul class="text-sm list-disc list-inside">@forelse($lowStock as $item)<li>{{ $item->item_name }} — {{ $item->availableQuantity() }} left</li>@empty<li>None</li>@endforelse</ul>
</div>
@endsection
