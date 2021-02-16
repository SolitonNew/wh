@extends('dialog')

@section('title')
@lang('admin\ow-manager.info_title')
@endsection

@section('content')
<form id="ow_manager_form" class="container" method="POST">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin\users.table_login')</div>
        </div>
        <div class="col-sm-6">
            <input class="form-control" type="text" name="login" value="{{ $item->login }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-danger" onclick="owManagerDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        //
    });
        
    function owManagerDelete() {
        if (confirm('@lang("admin\ow-manager.ow-manager-delete-confirm")')) {
            $.ajax('{{ route("ow-manager-delete", $item->ID) }}').done((data) => {
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