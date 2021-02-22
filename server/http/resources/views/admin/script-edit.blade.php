@extends('dialog')

@section('title')
@if($item->ID == -1)
    @lang('admin\scripts.script_add_title')
@else
    @lang('admin\scripts.script_edit_title')
@endif
@endsection

@section('content')
<form id="script_edit_form" class="container" method="POST" action="{{ route('script-edit', $item->ID) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\scripts.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\scripts.table_COMM')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="COMM" value="{{ $item->COMM }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->ID > 0 && Auth::user()->ID != $item->ID)
    <button type="button" class="btn btn-danger" onclick="scriptDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="scriptEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#script_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });
    
    function scriptEditOK() {
        $('#script_edit_form').submit();
    }
    
    function scriptDelete() {
        confirm("@lang('dialogs.confirm_title')", 
                "@lang('admin\scripts.script_delete_confirm')", 
                "@lang('dialogs.btn_yes')", 
                "@lang('dialogs.btn_no')", () => {
            $.ajax('{{ route("script-delete", $item->ID) }}').done((data) => {
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