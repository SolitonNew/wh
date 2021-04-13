@extends('dialog')

@section('title')
@if($item->id == -1)
    @lang('admin/hubs.hub_add_title')
@else
    @lang('admin/hubs.hub_edit_title')
@endif
@endsection

@section('content')
<form id="hub_edit_form" class="container" method="POST" action="{{ route('admin.hub-edit', $item->id) }}">
    {{ csrf_field() }}
    <button type="submit" style="display: none;"></button>
    @if($item->id > 0)
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/hubs.hub_ID')</div>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id > 0 ? $item->id : '' }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/hubs.hub_NAME')</div>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="name" value="{{ $item->name }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label strong">@lang('admin/hubs.hub_ROM')</div>
        </div>
        <div class="col-sm-3">
            <input type="text" class="form-control" name="rom" value="{{ $item->rom }}">
            <div class="invalid-feedback"></div>
        </div>
    </div>    
    <div class="row">
        <div class="col-sm-3">
            <div class="form-label">@lang('admin/hubs.hub_COMM')</div>
        </div>
        <div class="col-sm-9">
            <textarea class="form-control" name="comm">{{ $item->comm }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    
    
</form>
@endsection

@section('buttons')
    @if($item->id > 0)
    <button type="button" class="btn btn-danger" onclick="hubDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    @endif
    <button type="button" class="btn btn-primary" onclick="hubEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#hub_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    window.location.reload();
                });
            } else {
                dialogShowErrors(data);
            }
        });
    });

    function hubEditOK() {
        $('#hub_edit_form').submit();
    }

    function hubDelete() {
        confirmYesNo("@lang('admin/hubs.hub_delete_confirm')", () => {
            $.ajax({
                url: '{{ route("admin.hub-delete", $item->id) }}',
                data: {_token: '{{ csrf_token() }}'},
                type: 'delete',
                success: function (data) {
                    if (data == 'OK') {
                        dialogHide(() => {
                            window.location.reload();
                        });
                    } else {

                    }
                }
            });
        });
    }

</script>
@endsection
