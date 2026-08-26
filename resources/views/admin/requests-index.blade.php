@extends('layouts.psis')
@section('page-title', 'Approve Requests')
@section('content')
@include('partials.requests-table', [
    'requests' => $requests,
    'showRoute' => 'admin.requests.show',
    'actionLabel' => 'Review',
])
@endsection
