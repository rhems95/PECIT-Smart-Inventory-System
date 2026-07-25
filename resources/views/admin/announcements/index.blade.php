@extends('layouts.psis')
@section('page-title', 'Announcements')
@section('content')
<form method="POST" action="{{ route('admin.announcements.store') }}" class="psis-card p-4 mb-4 space-y-2">
@csrf
<input name="title" class="psis-input w-full" placeholder="Title" required>
<textarea name="content" class="psis-input w-full" rows="3" placeholder="Content" required></textarea>
<select name="priority" class="psis-input w-full">
<option value="normal">Normal priority</option>
<option value="high">High priority</option>
<option value="low">Low priority</option>
</select>
<button class="psis-btn-primary">Publish</button>
</form>
@foreach($announcements as $a)
<div class="psis-card p-4 mb-2">
<div class="flex justify-between gap-2">
<h3 class="font-semibold">{{ $a->title }}</h3>
<span class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700">{{ $a->priority }}</span>
</div>
<p class="text-sm mt-1">{{ $a->content }}</p>
<p class="text-xs text-slate-400 mt-2">{{ $a->published_at?->format('M d, Y H:i') }} · {{ $a->creator?->name }}</p>
</div>
@endforeach
{{ $announcements->links() }}
@endsection
