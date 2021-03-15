@extends('dialog')

@section('title')
@lang('admin/statistics.table_view_title')
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/statistics.table_ID')</div>
        </div>
        <div class="col-sm-4">
            <div class="form-control">{{ $item->id }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/statistics.table_CHANGE_DATE')</div>
        </div>
        <div class="col-sm-8">
            <div class="form-control">{{ $item->change_date }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-label">@lang('admin/statistics.table_VALUE')</div>
        </div>
        <div class="col-sm-4">
            <div class="form-control">{{ $item->value }}</div>
            <div class="invalid-feedback"></div>
        </div>
    </div>
</div>
@endsection

@section('buttons')
    <button type="button" class="btn btn-danger" onclick="statisticsValueDelete()">@lang('dialogs.btn_delete')</button>
    <div style="flex-grow: 1"></div>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('dialogs.btn_close')</button>
@endsection

@section('script')
<script>
    function statisticsValueDelete() {
        confirmYesNo("@lang('admin/statistics.table-value-delete-confirm')", () => {
            $.ajax('{{ route("statistics-table-value-delete", $item->id) }}').done((data) => {
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
