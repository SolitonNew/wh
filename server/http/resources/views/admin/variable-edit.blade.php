@extends('dialog')

@section('title')
@if ($item->ID == -1)
    @lang('admin\variables.variable_add_title')
@else
    @lang('admin\variables.variable_edit_title')
@endif
@endsection

@section('content')
<form id="variable_edit_form" class="container" method="POST" action="{{ route('user-edit', $item->ID) }}">
    {{ csrf_field() }}
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\variables.table_id')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_controller')</div>
        </div>
        <div class="col-sm-6">
            <select class="form-control" name="controller">
            @foreach(\App\Http\Models\ControllersModel::orderBy('name', 'asc')->get() as $row)
            <option value="{{ $row->ID }}" {{ $row->ID == $item->ID ? 'selected' : '' }}>{{ $row->NAME }}</option>
            @endforeach
            </select>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_typ')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="TYP" value="" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_readonly')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="DIRECTION" value="{{ $item->DIRECTION }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_name')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="NAME" value="{{ $item->NAME }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_comm')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="COMM" value="{{ $item->COMM }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_app_control')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="APP_CONTROL" value="{{ $item->APP_CONTROL }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_value')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="VALUE" value="{{ $item->VALUE }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\variables.table_channel')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="CHANNEL" value="{{ $item->CHANNEL }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->ID > 0)
    <button type="button" class="btn btn-danger" onclick="variableDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="variableEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#variable_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });
    
    function variableEditOK() {
        $('#variable_edit_form').submit();
    }
    
    function variableDelete() {
        if (confirm('@lang("admin\variables.variable-delete-confirm")')) {
            $.ajax('{{ route("variable-delete", $item->ID) }}').done((data) => {
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