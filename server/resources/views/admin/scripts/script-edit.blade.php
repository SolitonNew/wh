@extends('dialog')

@section('title')
@lang('admin/scripts.script_edit_title')
@endsection

@section('content')
<form id="script_edit_form" class="container" method="POST" action="{{ route('admin.script-edit', ['id' => $item->id]) }}">
    <button type="submit" style="display: none;"></button>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label">@lang('admin/scripts.table_ID')</label>
        </div>
        <div class="col-sm-3">
            <div class="form-control">{{ $item->id }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3">
            <label class="form-label strong">@lang('admin/scripts.table_COMM')</label>
        </div>
        <div class="col-sm-9">
            <input class="form-control" type="text" name="comm" value="{{ $item->comm }}" required="">
            <div class="invalid-feedback"></div>
        </div>
    </div>
</form>
@endsection

@section('buttons')
    <button type="button" class="btn btn-danger" onclick="scriptDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-primary" onclick="scriptEditOK()">@lang('dialogs.btn_save')</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_cancel')</button>
@endsection

@section('script')
<script>
    $(document).ready(() => {
        $('#script_edit_form').ajaxForm((data) => {
            if (data == 'OK') {
                dialogHide(() => {
                    reloadWithWaiter();
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
        confirmYesNo("@lang('admin/scripts.script_delete_confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.script-delete", ["id" => $item->id]) }}',
                data: {

                },
                success: function (data) {
                    if (data == 'OK') {
                        dialogHide(() => {
                            reloadWithWaiter();
                        });
                    } else {

                    }
                },
            });
        });
    }
</script>
@endsection
