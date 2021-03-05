@extends('dialog')

@section('title')
@if($item->ID == -1)
    @lang('admin/configuration.controller_add_title')
@else
    @lang('admin/configuration.controller_edit_title')
@endif
@endsection

@section('content')
<form id="controller_edit_form" class="container" method="POST" action="{{ route('configuration-edit', $item->ID) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/configuration.controller_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/configuration.controller_NAME')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="NAME" value="{{ $item->NAME }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/configuration.controller_NAME')</div>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="COMM" required="">{{ $item->COMM }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    
</form>
@endsection

@section('buttons')
    @if($item->ID > 0)
    <button type="button" class="btn btn-danger" onclick="controllerDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="controllerEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#controller_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });

    function controllerEditOK() {
        $('#controller_edit_form').submit();
    }

    function controllerDelete() {
        confirmYesNo("@lang('admin/configuration.controller-delete-confirm')", () => {
            $.ajax('{{ route("configuration-delete", $item->ID) }}').done((data) => {
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
