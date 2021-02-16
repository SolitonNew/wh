@extends('admin.admin')

@section('top-menu')
@endsection

@section('down-menu')
<a href="#" class="dropdown-item" onclick="runOwScan(); return false;">@lang('admin\ow-manager.run_ow_scan')</a>
@endsection

@section('content')
SCHEDULE
@endsection