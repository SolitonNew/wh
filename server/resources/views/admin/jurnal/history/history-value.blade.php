@extends('dialog')

@section('title')
@lang('admin/jurnal.history_view_title')
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/jurnal.history_ID')</div>
        </div>
        <div class="col-sm-4">
            <div class="form-control">{{ $item->id }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/jurnal.history_CHANGE_DATE')</div>
        </div>
        <div class="col-sm-8">
            <div class="form-control">{{ parse_datetime($item->change_date)->format('d-m-Y H:i:s') }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/jurnal.history_VALUE')</div>
        </div>
        <div class="col-sm-4">
            <div class="form-control">{{ $item->value }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</div>
@endsection

@section('buttons')
    <button type="button" class="btn btn-danger" onclick="jurnalHistoryValueDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
<script>
    function jurnalHistoryValueDelete() {
        confirmYesNo("@lang('admin/jurnal.history_value_delete_confirm')", () => {
            $.ajax({
                type: 'delete',
                url: '{{ route("admin.jurnal-history-value-delete", $item->id) }}',
                data: {_token: '{{ csrf_token() }}'},
                success: function (data) {
                    if (data === 'OK') {
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
