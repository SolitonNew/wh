@extends('dialog')

@section('title')
@lang('admin/hubs.hubs_scan_title')
@endsection

@section('content')
<div class="form-control" style="height: auto;">
    <div style="white-space: pre;padding: 0.5rem;">{!! $data !!}</div>
</div>
@endsection

@section('buttons')
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')

@endsection
