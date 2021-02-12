@extends('index')

@section('head')
<link rel="stylesheet" href="/css/admin.css">
@endsection

@section('body')
<div class="main-container">
    <div class="main-left-panel">
        <div style="height: 100px;"></div>
        <div class="list-group">
            <a class="list-group-item list-group-item-action @activeMenu('')" href="{{ route('variables') }}">
                @lang('admin/variables.menu')
            </a>
            <a class="list-group-item list-group-item-action @activeMenu('scripts')" href="{{ route('scripts') }}">
                @lang('admin/scripts.menu')
            </a>
            <a class="list-group-item list-group-item-action @activeMenu('statistics')" href="{{ route('statistics') }}">
                @lang('admin/statistics.menu')
            </a>
            <a class="list-group-item list-group-item-action @activeMenu('users')" href="{{ route('users') }}">
                @lang('admin/users.menu')
            </a>
            <a class="list-group-item list-group-item-action @activeMenu('ow-manager')" href="{{ route('ow-manager') }}">
                @lang('admin/ow-manager.menu')
            </a>
        </div>
    </div>
    <div class="main-content">
        @yield('content')
    </div>
</div>
@endsection