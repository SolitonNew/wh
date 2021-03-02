@extends('admin.admin')

@section('down-menu')
@yield('page-down-menu')
@endsection

@section('top-menu')
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link @activeSegment(3, 'table')" href="{{ route('statistics-table', '') }}">@lang('admin/statistics.page_table')</a>
    <a class="nav-link @activeSegment(3, 'chart')" href="{{ route('statistics-chart') }}">@lang('admin/statistics.page_chart')</a>
    <a class="nav-link @activeSegment(3, 'power')" href="{{ route('statistics-power') }}">@lang('admin/statistics.page_power')</a>
</div>
@yield('page-top-menu')
@endsection

@section('content')
@yield('page-content')
@endsection