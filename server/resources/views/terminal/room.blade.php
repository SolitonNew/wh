@extends('terminal.terminal')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('terminal.menu_home')</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $roomTitle }}</li>
    </ol>
</nav>

@include('terminal.room_list')

@endsection