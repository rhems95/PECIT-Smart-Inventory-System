@extends('layouts.psis')
@section('page-title', 'Accounting — Requests')
@section('content')
@include('partials.requests-table', [
    'requests' => $requests,
    'showRoute' => 'accounting.requests.show',
    'actionLabel' => 'Review',
])
@endsection
