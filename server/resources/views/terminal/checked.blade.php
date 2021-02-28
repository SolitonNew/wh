@extends('terminal.terminal')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('terminal.menu_home')</a></li>
        <li class="breadcrumb-item" style="flex-grow: 1;">@lang('terminal.menu_checked')</li>
        <li><a href="{{ route('terminal.checked-edit-add') }}">@lang('terminal.menu_checked_edit')</a></li>
    </ol>
</nav>

@include('terminal.room_list')

@endsection