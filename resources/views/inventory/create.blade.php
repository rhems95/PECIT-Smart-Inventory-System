@extends('layouts.psis')

@section('title', 'Add Inventory Item')
@section('page-title', 'Add Inventory Item')

@section('content')
<form method="POST" action="{{ route('inventory.store') }}" class="psis-card p-6 max-w-3xl space-y-4">
    @csrf
    @include('inventory._form')
    <button type="submit" class="psis-btn-primary">Save Item</button>
</form>
@endsection
