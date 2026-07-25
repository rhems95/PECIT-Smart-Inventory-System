@extends('layouts.psis')
@section('page-title', 'Notifications')
@section('content')
<form method="POST" action="{{ route('notifications.read-all') }}" class="mb-3">@csrf<button class="psis-btn-outline text-sm">Mark all read</button></form>
<div class="space-y-2">@foreach($notifications as $note)
<div class="psis-card p-4 {{ $note->is_read ? 'opacity-70' : '' }}"><p class="font-medium">{{ $note->title }}</p><p class="text-sm mt-1">{{ $note->message }}</p>
<form method="POST" action="{{ route('notifications.read', $note) }}" class="mt-2">@csrf<button class="text-pecit-blue text-sm">Open</button></form></div>
@endforeach</div>{{ $notifications->links() }}
@endsection
