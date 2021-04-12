@extends('admin.admin')

@section('down-menu')
<style>
    .navbar-down-menu {
        min-width: 250px;
        margin-right: 0.5rem;
    }
</style>
@yield('page-down-menu')
@endsection

@section('top-menu')
<div class="nav nav-tabs navbar-top-menu-tab">
    <a class="nav-link @activeSegment(4, 'devices')" 
        href="{{ route('admin.hub-devices', $hubID) }}">@lang('admin/hubs.devices')</a>
    <a class="nav-link @activeSegment(4, 'hosts')" 
        href="{{ route('admin.hub-hosts', $hubID) }}">@lang('admin/hubs.hosts')</a>
</div>
@yield('page-top-menu')
@endsection

@section('content')
<div style="display: flex; flex-direction: row; flex-grow: 1;height: 100%;">
    <div class="tree" style="width: 250px;min-width:250px; border-right: 1px solid rgba(0,0,0,0.125);" scroll-store="hubsList">
        @foreach(\App\Http\Models\ControllersModel::whereIsServer(0)->orderBy('id', 'asc')->get() as $row)
        <a href="{{ route('admin.hubs', $row->id).'/'.$page }}"
            class="tree-item {{ $row->id == $hubID ? 'active' : '' }}">
            <div>
                <div class="" >{{ $row->name }}</div>
                <small class="text-muted">{{ $row->comm }}</small>
            </div>
        </a>
        @endforeach
    </div>
    <div class="content-body">
        @yield('page-content')
    </div>
</div>
@endsection