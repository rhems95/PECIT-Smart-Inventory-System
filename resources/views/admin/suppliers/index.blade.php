@extends('layouts.psis')
@section('page-title', 'Suppliers')
@section('content')
<form method="POST" action="{{ route('admin.suppliers.store') }}" class="psis-card p-4 mb-4 grid md:grid-cols-2 gap-2">@csrf<input name="name" class="psis-input" placeholder="Supplier name" required><input name="email" class="psis-input" placeholder="Email"><button class="psis-btn-primary md:col-span-2">Add Supplier</button></form>
<div class="psis-card">@foreach($suppliers as $supplier)<div class="px-4 py-3 border-t border-[var(--psis-border)]">{{ $supplier->name }}</div>@endforeach</div>{{ $suppliers->links() }}
@endsection
