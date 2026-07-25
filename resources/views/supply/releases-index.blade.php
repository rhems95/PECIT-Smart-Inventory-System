@extends('layouts.psis')
@section('page-title', 'Release Items')
@section('content')
@include('partials.requests-table', [
    'requests' => $requests,
    'showRoute' => 'supply.releases.show',
    'actionLabel' => 'Release',
])
@endsection
