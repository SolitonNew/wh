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
            <div class="form-label">@lang('admin\ow-manager.table_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\ow-manager.table_CONTROLLER')</div>
        </div>
        <div class="col-sm-6">
            <div class="form-control">{{ $item->CONTROLLER_NAME }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\ow-manager.table_ROM')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->ROM }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\ow-manager.table_COMM')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->COMM }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin\ow-manager.table_CHANNELS')</div>
        </div>
        <div class="col-sm-9">
            <div class="form-control">{{ $item->CHANNELS }}</div>
        </div>
    </div>
    <div class="form-group">
        <div class="">@lang('admin\ow-manager.table_VARIABLES') ({{ count($item->VARIABLES) }}):</div>
        <div class="form-control" style="height: auto;">
        @foreach($item->VARIABLES as $v)
        <div>[{{ $v->CHANNEL }}] {{ $v->NAME }}</div>
        @endforeach    
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