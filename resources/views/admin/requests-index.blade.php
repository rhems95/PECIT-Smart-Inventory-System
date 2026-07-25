@extends('layouts.psis')
@section('page-title', 'Admin Approvals')
@section('content')
@include('partials.requests-table', [
    'requests' => $requests,
    'showRoute' => 'admin.requests.show',
    'actionLabel' => 'Review',
])
@endsection
