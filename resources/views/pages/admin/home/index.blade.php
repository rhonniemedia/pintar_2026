@extends('layouts.main.admin')

@section('title', 'Dashboard Akademik')
@section('page_title', 'Akademik')
@section('page_subtitle', 'Dashboard Kesiswaan')

@section('content')

<div class="flex-1 overflow-y-auto p-5 md:p-8 bg-white" x-data="akademikDashboardApp()">
    @include('pages.admin.home.partials._header')
    @include('pages.admin.home.partials._stat-cards')
    @include('pages.admin.home.partials._charts')
    @include('pages.admin.home.partials._capacity-activity')
    @include('pages.admin.home.partials._latest-students')
</div>

@include('pages.admin.home.partials._scripts')

@endsection