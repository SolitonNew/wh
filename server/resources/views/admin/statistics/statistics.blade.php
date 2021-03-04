@extends('admin.admin')

@section('down-menu')
@yield('page-down-menu')
@endsection

@section('top-menu')
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link @activeSegment(3, 'table')" 
        href="{{ route('statistics-table', Session::get('STATISTICS-TABLE-ID')) }}">@lang('admin/statistics.page-table')</a>
    <a class="nav-link @activeSegment(3, 'chart')" 
        href="{{ route('statistics-chart') }}">@lang('admin/statistics.page-chart')</a>
    <a class="nav-link @activeSegment(3, 'power')" 
        href="{{ route('statistics-power') }}">@lang('admin/statistics.page-power')</a>
</div>
@yield('page-top-menu')
@endsection

@section('content')
@yield('page-content')
@endsection