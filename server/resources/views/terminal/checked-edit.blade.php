@extends('terminal.terminal')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('terminal.menu_home')</a></li>
        <li class="breadcrumb-item"><a href="{{ route('terminal.checked') }}">@lang('terminal.menu_checked')</a></li>
        <li class="breadcrumb-item">@lang('terminal.menu_checked_edit')</li>
    </ol>
</nav>

<div class="nav nav-tabs justify-content-center" style="margin: 0 -1rem; margin-bottom: 1rem;">
    <div class="nav-item">
        <a href="{{ route('terminal.checked-edit-add') }}" class="nav-link {{ $page == 'add' ? 'active' : '' }}">@lang('terminal.page_append')</a>
    </div>
    <div class="nav-item">
        <a href="{{ route('terminal.checked-edit-order') }}" class="nav-link {{ $page == 'order' ? 'active' : '' }}">@lang('terminal.page_order')</a>
    </div>
    <div class="nav-item">
        <a href="{{ route('terminal.checked-edit-color') }}" class="nav-link {{ $page == 'color' ? 'active' : '' }}">@lang('terminal.page_color')</a>
    </div>
</div>

<div class="justify-content-center" style="display: flex; margin: 0 -1rem;">
    @yield('page')
</div>

@endsection