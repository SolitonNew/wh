@extends('terminal.terminal')

@section('content')

<nav aria-label="breadcrumb">
    <ol class="row breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">@lang('terminal.menu_home')</a></li>
        <li class="breadcrumb-item"><a href="{{ route('terminal.room', $roomID) }}">{{ $roomTitle }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $variableTitle }}</li>
    </ol>
</nav>

@yield('variable')

@endsection