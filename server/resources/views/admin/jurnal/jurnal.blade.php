@extends('admin.admin')

@section('down-menu')
@yield('page-down-menu')
@endsection

@section('top-menu')
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link @activeSegment(3, 'history')" 
        href="{{ route('admin.jurnal-history', '') }}">@lang('admin/jurnal.history')</a>
    <a class="nav-link @activeSegment(3, 'daemons')" 
        href="{{ route('admin.jurnal-daemons', '') }}">@lang('admin/jurnal.daemons')</a>
    {{-- <a class="nav-link @activeSegment(3, 'power')" 
        href="{{ route('admin.jurnal-power', '') }}">@lang('admin/jurnal.power')</a> --}}
</div>
@endsection

@section('content')
@yield('page-content')
@endsection