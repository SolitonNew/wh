@extends('dialog')

@section('title')
@lang('admin/configuration.ow_scan_title')
@endsection

@section('content')
<div class="form-control" style="height: auto; white-space: pre;">
{!! $data !!}
</div>
@endsection

@section('buttons')
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')

@endsection
