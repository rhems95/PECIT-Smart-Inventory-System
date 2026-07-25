@extends('layouts.psis')
@section('page-title', 'Audit Logs')
@section('content')
<div class="psis-card overflow-hidden"><table class="min-w-full text-sm"><thead><tr><th class="px-4 py-3 text-left">When</th><th>User</th><th>Action</th></tr></thead><tbody>
@foreach($logs as $log)<tr class="border-t border-[var(--psis-border)]"><td class="px-4 py-3">{{ $log->created_at }}</td><td class="px-4 py-3">{{ $log->user?->name }}</td><td class="px-4 py-3">{{ $log->action }}</td></tr>@endforeach
</tbody></table></div>{{ $logs->links() }}
@endsection
