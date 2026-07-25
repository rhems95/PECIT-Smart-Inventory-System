@extends('layouts.psis')
@section('page-title', 'Categories')
@section('content')
<form method="POST" action="{{ route('admin.categories.store') }}" class="psis-card p-4 mb-4 flex flex-wrap gap-2">@csrf<input name="name" class="psis-input max-w-xs" placeholder="Category name" required><button class="psis-btn-primary">Add</button></form>
<div class="psis-card overflow-hidden"><table class="min-w-full text-sm">@foreach($categories as $cat)<tr class="border-t border-[var(--psis-border)]"><td class="px-4 py-3">{{ $cat->name }}</td><td class="px-4 py-3 text-slate-500">{{ $cat->slug }}</td></tr>@endforeach</table></div>{{ $categories->links() }}
@endsection
