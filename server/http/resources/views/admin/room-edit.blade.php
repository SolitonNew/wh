@extends('dialog')

@section('title')
@if ($item->ID == -1)
    @lang('admin\rooms.room_add_title')
@else
    @lang('admin\rooms.room_edit_title')
@endif
@endsection

@section('content')
<form id="room_edit_form" class="container" method="POST" action="{{ route('room-edit', $item->ID) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->ID > 0)
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin\rooms.table_id')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->ID > 0 ? $item->ID : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin\rooms.table_controller')</div>
        </div>
        <div class="col-sm-6">
            
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    @if($item->ID > 0)
    <button type="button" class="btn btn-danger" onclick="roomDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="roomEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#room_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });
    
    function roomEditOK() {
        $('#room_edit_form').submit();
    }
    
    function roomDelete() {
        if (confirm("@lang('admin\rooms.room_delete_confirm')")) {
            $.ajax('{{ route("room-delete", $item->ID) }}').done((data) => {
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