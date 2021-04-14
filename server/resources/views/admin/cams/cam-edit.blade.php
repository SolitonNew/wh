@extends('dialog')

@section('title')
@if($item->id == -1)
    @lang('admin/cams.cam_add_title')
@else
    @lang('admin/cams.cam_edit_title')
@endif
@endsection

@section('content')
<form id="cam_edit_form" class="container" method="POST" action="{{ route('admin.cam-edit', $item->id) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/cams.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/cams.table_NAME')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="name" value="{{ $item->name }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="form-group">
        <div class="strong">@lang('admin/cams.table_URL'):</div>
        <textarea class="form-control" name="url" rows="3">{!! $item->url !!}</textarea>
        <div class="invalid-feedback"></div>
    </div>
    <div class="form-group">
        <div class="strong">@lang('admin/cams.table_URL_LOW'):</div>
        <textarea class="form-control" name="url_low" rows="3">{!! $item->url_low !!}</textarea>
        <div class="invalid-feedback"></div>
    </div>
    <div class="form-group">
        <div class="strong">@lang('admin/cams.table_URL_HIGH'):</div>
        <textarea class="form-control" name="url_high" rows="3">{!! $item->url_high !!}</textarea>
        <div class="invalid-feedback"></div>
    </div>
    <div class="form-group">
        <div>@lang('admin/cams.table_ALERT_VAR_ID'):</div>
        <select class="custom-select" name="alert_var_id">
        <option value="-1">-//-</option>
        @foreach(\App\Http\Models\VariablesModel::orderBy('name')->get() as $row)
        <option value="{{ $row->id }}" {{ $item->alert_var_id == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
        @endforeach
        </select>
        <div class="invalid-feedback"></div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
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
        confirmYesNo("@lang('admin/cams.cam_delete_confirm')", () => {
            $.ajax('{{ route("admin.cam-delete", $item->id) }}').done((data) => {
                if (data == 'OK') {
                    dialogHide(() => {
                        window.location.reload();
                    });
                } else {

                }
            });
        });
    }

</script>
@endsection
