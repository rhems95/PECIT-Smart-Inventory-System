@extends('layouts.psis')

@section('title', 'Edit '.$inventory->item_name)
@section('page-title', 'Edit Inventory Item')

@section('content')
<form method="POST" action="{{ route('inventory.update', $inventory) }}" class="psis-card p-6 max-w-3xl space-y-4">
    @csrf
    @method('PUT')
    @include('inventory._form', ['inventory' => $inventory])
    <button type="submit" class="psis-btn-primary">Update Item</button>
</form>
@endsection
