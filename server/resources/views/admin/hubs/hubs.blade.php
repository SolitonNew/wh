@extends('admin.admin')

@section('down-menu')
@yield('page-down-menu')
@endsection

@section('top-menu')
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link @activeSegment(3, 'devices')" 
        href="{{ route('admin.hubs.devices') }}">@lang('admin/statistics.page-table')</a>
    <a class="nav-link @activeSegment(3, 'hosts')" 
        href="{{ route('admin.hubs.hosts') }}">@lang('admin/statistics.page-chart')</a>
</div>
@yield('page-top-menu')
@endsection

@section('content')
@yield('page-content')
@endsection