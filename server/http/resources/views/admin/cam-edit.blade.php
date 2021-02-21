@extends('dialog')

@section('title')
@if($item->ID == -1)
    @lang('admin\cams.cam_add_title')
@else
    @lang('admin\cams.cam_edit_title')
@endif
@endsection

@section('content')
<form id="cam_edit_form" class="container" method="POST" action="{{ route('cam-edit', $item->ID) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\cams.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\cams.table_NAME')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="NAME" value="{{ $item->NAME }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="form-group">
        <div class="strong">@lang('admin\cams.table_URL'):</div>
        <textarea class="form-control" name="URL" rows="3">{!! $item->URL !!}</textarea>
        <div class="invalid-feedback"></div>
    </div>
    <div class="form-group">
        <div class="strong">@lang('admin\cams.table_URL_LOW'):</div>
        <textarea class="form-control" name="URL_LOW" rows="3">{!! $item->URL_LOW !!}</textarea>
        <div class="invalid-feedback"></div>
    </div>
    <div class="form-group">
        <div class="strong">@lang('admin\cams.table_URL_HIGH'):</div>
        <textarea class="form-control" name="URL_HIGH" rows="3">{!! $item->URL_HIGH !!}</textarea>
        <div class="invalid-feedback"></div>
    </div>
    <div class="form-group">
        <div class="strong">@lang('admin\cams.table_ALERT_VAR_ID'):</div>
        <select class="custom-select" name="ALERT_VAR_ID">
        <option value="-1">-//-</option>
        @foreach(\App\Http\Models\VAriablesModel::orderBy('NAME')->get() as $row)
        <option value="{{ $row->ID }}" {{ $item->ALERT_VAR_ID == $row->ID ? 'selected' : '' }}>{{ $row->NAME }}</option>
        @endforeach
        </select>
        <div class="invalid-feedback"></div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->ID > 0)
    <button type="button" class="btn btn-danger" onclick="camDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="camEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#cam_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });
    
    function camEditOK() {
        $('#cam_edit_form').submit();
    }
    
    function camDelete() {
        if (confirm('@lang("admin\cams.cam_delete_confirm")')) {
            $.ajax('{{ route("cam-delete", $item->ID) }}').done((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {
                    
                }
            });
        }
    }
    
</script>
@endsection